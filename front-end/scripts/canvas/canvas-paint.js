let canvas = document.getElementById('canvas');
let context = canvas.getContext('2d');
context.lineWidth = 5;
let con = false;
let x = 0, y = 0;

function draw(e){
  const rect = canvas.getBoundingClientRect();
  x = e.clientX - rect.left;
  y = e.clientY - rect.top;

  if (con === true){
    context.lineTo(x, y);
    context.stroke();
  }
}

canvas.addEventListener('mousemove', draw);

canvas.addEventListener('mousedown', function(e){
  const rect = canvas.getBoundingClientRect();
  x = e.clientX - rect.left;
  y = e.clientY - rect.top;

  con = true;
  context.beginPath();
  context.moveTo(x, y);
});

canvas.addEventListener('mouseup', function(){
  con = false;
});

function Color(valor){
  context.strokeStyle = valor.value;
}
function Line(valor){
  context.lineWidth = valor.value;
}
function Clear(){
  context.clearRect(0, 0, canvas.width, canvas.height);
}
