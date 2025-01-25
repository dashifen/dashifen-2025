document.addEventListener('DOMContentLoaded', () => {
  document.documentElement.classList.add('js');
  
  // because WordPress abandoned all sense and reason and decided to use HTML
  // files for block themes, the only way I've been able to figure out how to
  // automatically update the year on my site in a block theme is via
  // JavaScript.
  
  document.getElementById('year').innerHTML = (new Date()).getFullYear().toString();
})
