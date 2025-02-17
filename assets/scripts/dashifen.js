document.addEventListener('DOMContentLoaded', () => {
  document.documentElement.classList.add('js');
  
  const canvas = document.querySelector('canvas.progress')
  if (canvas.getContext) {
    const ctx = canvas.getContext('2d');
    const progress = canvas.dataset.progress;
    ctx.fillStyle = 'rgba(255,255,255,0.1)';
    ctx.strokeStyle = 'white';
    
    // this part of the arc draws the progress itself with a thicker line.
    // then, the second path is the remainder of the full circle with a thin,
    // less obvious line to complete the arc.  note, 0 radians is a horizontal
    // line from the center going to the right (eastward, if you will), the CSS
    // for the <canvas> element rotates things -90 degrees so that zero radians
    // is in the north.
    
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
