document.addEventListener("DOMContentLoaded", () => {
  const typeSelect = document.getElementById("product-type");
  const simpleFields = document.getElementById("simple-fields");
  const variableFields = document.getElementById("variable-fields");
  const typeButtons = document.querySelectorAll(".adm-type-toggle__btn");
  const uploadZone = document.getElementById("main-upload-zone");
  const fileInput = document.getElementById("thumbnail_file");
  const previewWrap = document.getElementById("main-image-preview");
  const previewImg = document.getElementById("main-preview-img");
  const uploadDrop = document.getElementById("main-upload-drop");
  const clearPreview = document.getElementById("clear-main-preview");
  const nameInput = document.querySelector('input[name="name"]');
  const headerTitle = document.querySelector(".adm-product-header__title");

  function setProductType(type) {
    if (!typeSelect) return;
    typeSelect.value = type;
    typeButtons.forEach((btn) => {
      btn.classList.toggle("is-active", btn.dataset.type === type);
    });
    const isVariable = type === "variable";
    if (simpleFields) simpleFields.hidden = isVariable;
    if (variableFields) variableFields.hidden = !isVariable;
    document.querySelectorAll("#simple-fields input, #simple-fields select").forEach((el) => {
      el.disabled = isVariable;
    });
    typeSelect.dispatchEvent(new Event("change"));
  }

  typeButtons.forEach((btn) => {
    btn.addEventListener("click", () => setProductType(btn.dataset.type));
  });

  if (nameInput && headerTitle) {
    const defaultTitle = headerTitle.dataset.defaultTitle || "New Product";
    nameInput.addEventListener("input", () => {
      headerTitle.textContent = nameInput.value.trim() || defaultTitle;
    });
  }

  function showPreview(src) {
    if (!previewImg || !previewWrap || !uploadDrop) return;
    previewImg.src = src;
    previewWrap.hidden = false;
    uploadDrop.hidden = true;
  }

  function hidePreview() {
    if (!previewWrap || !uploadDrop) return;
    previewWrap.hidden = true;
    uploadDrop.hidden = false;
    if (previewImg) previewImg.src = "";
    if (fileInput) fileInput.value = "";
  }

  fileInput?.addEventListener("change", () => {
    const file = fileInput.files?.[0];
    if (!file || !file.type.startsWith("image/")) return;
    const reader = new FileReader();
    reader.onload = (e) => showPreview(e.target?.result);
    reader.readAsDataURL(file);
  });

  clearPreview?.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    hidePreview();
  });

  uploadZone?.addEventListener("dragover", (e) => {
    e.preventDefault();
    uploadZone.classList.add("is-dragover");
  });

  uploadZone?.addEventListener("dragleave", () => {
    uploadZone.classList.remove("is-dragover");
  });

  uploadZone?.addEventListener("drop", (e) => {
    e.preventDefault();
    uploadZone.classList.remove("is-dragover");
    const file = e.dataTransfer?.files?.[0];
    if (!file || !file.type.startsWith("image/") || !fileInput) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;
    fileInput.dispatchEvent(new Event("change"));
  });

  document.querySelectorAll(".adm-chip input[type=checkbox]").forEach((input) => {
    const sync = () => input.closest(".adm-chip")?.classList.toggle("is-selected", input.checked);
    input.addEventListener("change", sync);
    sync();
  });

  document.body.addEventListener("change", (e) => {
    if (!e.target.classList?.contains("js-var-file")) return;
    const file = e.target.files?.[0];
    if (!file || !file.type.startsWith("image/")) return;
    const slot = e.target.closest(".adm-var-img-slot");
    if (!slot) return;
    let preview = slot.querySelector(".adm-var-img-slot__preview");
    if (!preview) {
      preview = document.createElement("div");
      preview.className = "adm-var-img-slot__preview";
      slot.insertBefore(preview, e.target);
    }
    const reader = new FileReader();
    reader.onload = (ev) => {
      preview.innerHTML = `<img src="${ev.target?.result}" alt="Preview">`;
    };
    reader.readAsDataURL(file);
  });
});
