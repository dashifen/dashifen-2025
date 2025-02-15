document.addEventListener('DOMContentLoaded', () => {
  document.documentElement.classList.add('js');
  
  const canvas = document.querySelector('canvas.progress')
  if (canvas.getContext) {
    const ctx = canvas.getContext('2d');
    const progress = canvas.dataset.progress;
    ctx.fillStyle = 'rgba(255,255,255,0.1)';
    ctx.strokeStyle = 'white';
    
    ctx.beginPath();
    ctx.lineWidth = 5;
    ctx.arc(canvas.width / 2, canvas.height / 2, 70, 0, 2 * Math.PI * progress);
    ctx.stroke();
    ctx.fill();
    
    ctx.beginPath();
    ctx.lineWidth = 1;
    ctx.arc(canvas.width / 2, canvas.height / 2, 70, 2 * Math.PI * progress, 2 * Math.PI);
    ctx.stroke();
    ctx.fill();
  }
});
