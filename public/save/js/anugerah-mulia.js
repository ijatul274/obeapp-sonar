function toggleDropdown(e){
  e.preventDefault();
  const parent = e.currentTarget.parentElement;
  parent.classList.toggle('open');
}

// optional: close other dropdowns when one opens
document.addEventListener('click', function(ev){
  const isToggle = ev.target.closest('.dropdown > a');
  if(!isToggle){
    document.querySelectorAll('.dropdown.open').forEach(d=> d.classList.remove('open'));
  }
});
