document.addEventListener("DOMContentLoaded", () => {
  const root = document.getElementById("product-variant-root");
  if (!root) return;

  let payload = {};
  try {
    payload = JSON.parse(root.dataset.payload || "{}");
  } catch (error) {
    return;
  }

  const priceEl = document.getElementById("pdp-price");
  const compareEl = document.getElementById("pdp-compare");
  const discountEl = document.getElementById("pdp-discount");
  const stockEl = document.getElementById("pdp-stock");
  const imageEl = document.getElementById("pdp-image") || document.querySelector(".msh-pdp-img img");
  const thumbsEl = document.getElementById("pdp-thumbs");
  const variantInput = document.getElementById("variant-id-input");
  const addBtn = document.querySelector(".msh-pdp-actions .msh-cart-add-form button[type=submit]");
  const defaultImage = imageEl?.getAttribute("src") || "";
  const selected = {};

  function formatCurrency(amount) {
    return "₹" + Number(amount || 0).toLocaleString("en-IN", { maximumFractionDigits: 0 });
  }

  function setGallery(images) {
    const list = (images || []).filter(Boolean);
    const primary = list[0] || defaultImage;

    if (imageEl) {
      imageEl.src = primary;
    }

    if (!thumbsEl) return;

    thumbsEl.innerHTML = "";

    if (list.length <= 1) {
      thumbsEl.hidden = true;
      return;
    }

    thumbsEl.hidden = false;

    list.forEach((src, index) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "msh-pdp-thumb" + (index === 0 ? " active" : "");
      btn.innerHTML = `<img src="${src}" alt="">`;
      btn.addEventListener("click", () => {
        if (imageEl) imageEl.src = src;
        thumbsEl.querySelectorAll(".msh-pdp-thumb").forEach((el) => el.classList.remove("active"));
        btn.classList.add("active");
      });
      thumbsEl.appendChild(btn);
    });
  }

  function findVariant() {
    if (payload.type !== "variable") return null;
    const selectedIds = Object.values(selected).map(Number).filter(Boolean).sort((a, b) => a - b);
    if (!selectedIds.length) return null;
    return payload.variants.find((variant) => {
      const ids = [...variant.value_ids].sort((a, b) => a - b);
      return ids.length === selectedIds.length && ids.every((id, i) => id === selectedIds[i]);
    }) || null;
  }

  function updateSimple() {
    const price = payload.price;
    const compare = payload.compare_at_price;
    if (priceEl) priceEl.textContent = formatCurrency(price);
    if (compareEl) {
      compareEl.style.display = compare && compare > price ? "" : "none";
      compareEl.textContent = formatCurrency(compare);
    }
    if (discountEl) {
      if (compare && compare > price) {
        discountEl.style.display = "";
        discountEl.textContent = Math.round((1 - price / compare) * 100) + "% OFF";
      } else {
        discountEl.style.display = "none";
      }
    }
    if (stockEl) {
      stockEl.textContent = payload.stock > 0 ? "In Stock" : "Out of Stock";
      stockEl.style.color = payload.stock > 0 ? "var(--success)" : "var(--error)";
    }
    if (addBtn) addBtn.disabled = payload.stock < 1;
    if (variantInput) variantInput.value = "";
    setGallery([defaultImage]);
  }

  function updateVariable() {
    const variant = findVariant();
    if (!variant) {
      const min = payload.price_min;
      const max = payload.price_max;
      if (priceEl && min != null && max != null) {
        priceEl.textContent = min === max
          ? formatCurrency(min)
          : formatCurrency(min) + " – " + formatCurrency(max);
      }
      if (compareEl) compareEl.style.display = "none";
      if (discountEl) discountEl.style.display = "none";
      if (stockEl) {
        const hasStock = Boolean(payload.in_stock) || (payload.variants || []).some((item) => Number(item.stock) > 0);
        stockEl.textContent = hasStock ? "Select options" : "Out of Stock";
        stockEl.style.color = hasStock ? "var(--text-secondary)" : "var(--error)";
      }
      setGallery([defaultImage]);
      if (addBtn) addBtn.disabled = true;
      if (variantInput) variantInput.value = "";
      return;
    }

    const price = variant.price;
    const compare = variant.compare_at_price;
    if (priceEl) priceEl.textContent = formatCurrency(price);
    if (compareEl) {
      compareEl.style.display = compare && compare > price ? "" : "none";
      compareEl.textContent = formatCurrency(compare);
    }
    if (discountEl) {
      if (compare && compare > price) {
        discountEl.style.display = "";
        discountEl.textContent = Math.round((1 - price / compare) * 100) + "% OFF";
      } else {
        discountEl.style.display = "none";
      }
    }
    if (stockEl) {
      stockEl.textContent = variant.stock > 0 ? "In Stock (" + variant.stock + ")" : "Out of Stock";
      stockEl.style.color = variant.stock > 0 ? "var(--success)" : "var(--error)";
    }

    const images = (variant.images && variant.images.length)
      ? variant.images
      : [variant.thumbnail || defaultImage];
    setGallery(images);

    if (variantInput) variantInput.value = variant.id;
    if (addBtn) addBtn.disabled = Number(variant.stock) < 1;
  }

  function refresh() {
    payload.type === "variable" ? updateVariable() : updateSimple();
  }

  root.querySelectorAll(".msh-variant-option").forEach((btn) => {
    btn.addEventListener("click", () => {
      const group = btn.closest(".msh-variant-group");
      const attributeId = group?.dataset.attributeId;
      if (!attributeId) return;
      group.querySelectorAll(".msh-variant-option").forEach((el) => el.classList.remove("active"));
      btn.classList.add("active");
      selected[attributeId] = Number(btn.dataset.valueId);
      refresh();
    });
  });

  if (payload.type === "variable") {
    refresh();
  }
});
