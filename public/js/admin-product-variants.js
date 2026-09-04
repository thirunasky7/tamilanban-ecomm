document.addEventListener("DOMContentLoaded", () => {
  const typeSelect = document.getElementById("product-type");
  const simpleFields = document.getElementById("simple-fields");
  const variableFields = document.getElementById("variable-fields");
  const variationsBody = document.getElementById("variations-body");
  const addRowBtn = document.getElementById("add-variation-row");
  const template = document.getElementById("variation-row-template");
  let rowIndex = variationsBody ? variationsBody.querySelectorAll(".variation-row").length : 0;

  function getSelectedAttributeIds() {
    return Array.from(document.querySelectorAll(".js-product-attribute:checked")).map((el) =>
      Number(el.value)
    );
  }

  function toggleType() {
    const isVariable = typeSelect?.value === "variable";
    if (simpleFields) simpleFields.hidden = isVariable;
    if (variableFields) variableFields.hidden = !isVariable;
    document.querySelectorAll("#simple-fields input, #simple-fields select").forEach((el) => {
      el.disabled = isVariable;
    });
  }

  function refreshAttributeVisibility() {
    const selected = getSelectedAttributeIds();
    document.querySelectorAll(".js-variation-attr").forEach((el) => {
      const attributeId = Number(el.dataset.attributeId);
      el.hidden = !selected.includes(attributeId);
    });
  }

  function bindRemoveButtons() {
    document.querySelectorAll(".js-remove-variation").forEach((btn) => {
      btn.onclick = () => {
        const rows = variationsBody.querySelectorAll(".variation-row");
        if (rows.length <= 1) return;
        btn.closest(".variation-row")?.remove();
      };
    });
  }

  document.querySelectorAll(".js-product-attribute").forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      checkbox.closest(".adm-chip")?.classList.toggle("is-selected", checkbox.checked);
      refreshAttributeVisibility();
    });
  });

  addRowBtn?.addEventListener("click", () => {
    if (!template || !variationsBody) return;
    const html = template.innerHTML.replaceAll("__INDEX__", String(rowIndex++));
    variationsBody.insertAdjacentHTML("beforeend", html);
    bindRemoveButtons();
    refreshAttributeVisibility();
  });

  typeSelect?.addEventListener("change", toggleType);
  toggleType();
  refreshAttributeVisibility();
  bindRemoveButtons();
});
