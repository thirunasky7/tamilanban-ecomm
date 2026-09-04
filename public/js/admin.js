document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.getElementById("adm-menu-toggle");
  const sidebar = document.querySelector(".adm-sidebar");
  const overlay = document.getElementById("adm-sidebar-overlay");
  if (toggle && sidebar) {
    toggle.addEventListener("click", () => {
      sidebar.classList.toggle("open");
      overlay?.classList.toggle("open");
    });
    overlay?.addEventListener("click", () => {
      sidebar.classList.remove("open");
      overlay.classList.remove("open");
    });
  }

  if (typeof $ === "undefined" || !$.fn.DataTable) return;

  $(".adm-datatable").each(function () {
    if ($.fn.DataTable.isDataTable(this)) return;
    $(this).DataTable({
      dom: '<"adm-dt-toolbar"lfB>rt<"adm-dt-footer"ip>',
      buttons: [
        { extend: "copy", text: "Copy" },
        { extend: "csv", text: "CSV" },
        { extend: "excel", text: "Excel" },
        { extend: "pdf", text: "PDF" },
        { extend: "print", text: "Print" },
      ],
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      order: [],
      responsive: true,
      language: {
        search: "",
        searchPlaceholder: "Search records...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: "No entries found",
        paginate: { first: "First", last: "Last", next: "Next", previous: "Prev" },
      },
    });
  });
});