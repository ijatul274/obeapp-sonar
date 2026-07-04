// Confirm delete (generic). 
// type: resource name (string), id: record id, name: readable name
function confirmDelete(type, id, name) {
  const title = name ? `${type} "${name}"` : `${type}`;
  if (confirm(`Apakah Anda yakin ingin menghapus ${title}?\n\nData yang dihapus tidak dapat dikembalikan!`)) {
    const formId = `delete-form-${type}-${id}`;
    const form = document.getElementById(formId);
    if (form) {
      form.submit();
    } else {
      console.warn('Form delete tidak ditemukan:', formId);
    }
  }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.alert').forEach(function (alert) {
    setTimeout(function () {
      alert.style.opacity = '0';
      setTimeout(function () { alert.remove(); }, 300);
    }, 5000);
  });

  // Dropdown toggle for sidebar (if you used onclick toggleDropdown in layout)
  document.querySelectorAll('.dropdown > a').forEach(function (el) {
    el.addEventListener('click', function (ev) {
      ev.preventDefault();
      const parent = el.parentElement;
      parent.classList.toggle('open');
    });
  });
});
