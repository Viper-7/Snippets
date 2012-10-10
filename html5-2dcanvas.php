<!doctype html>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<script type="text/javascript">
function polygon(n){var a=[];for(var i=0;i<n;i++)
a.push({x:Math.cos(2*Math.PI*(i/n)),y:Math.sin(2*Math.PI*(i/n))});return a;}
function lineTo(ctx){return function(p){ctx.lineTo(p.x,p.y)}}
function linePath(ctx,path){path.map(lineTo(ctx))}
function drawPath(ctx,path,close){ctx.beginPath();linePath(ctx,path);if(close)ctx.closePath()}
function fillPath(ctx,path){drawPath(ctx,path);ctx.fill()}
function scale(x,y,p){return{x:p.x*x,y:p.y*y}}
function scalePoint(x,y){return function(p){return scale(x,y,p)}}
function translate(x,y,p){return{x:p.x+x,y:p.y+y}}
function translatePoint(x,y){return function(p){return translate(x,y,p)}}
function wavePoint(p){var np={x:(p.x+1.5*Math.cos((p.x+p.y)/15)),y:(p.y+1.5*Math.sin((p.x+p.y)/15))}
var d=Math.sqrt(np.x*np.x+np.y*np.y);var s=Math.pow(1/(d+1),0.7);return translate(5,24,scale(s*30,s*30,np));}
function wavePath(path){return path.map(wavePoint)}
function drawbg(){var canvas=document.createElement('canvas');canvas.width=1400;canvas.height=700;var ctx=canvas.getContext('2d');ctx.fillStyle='#000';
ctx.fillRect(0,0,canvas.width,canvas.height);var g=ctx.createRadialGradient(50,50,0,50,50,1400);g.addColorStop(0,'rgba(0,192,255,0.2)');
g.addColorStop(1.0,'rgba(255,0,255,0.05)');ctx.fillStyle=g;var hex=polygon(6);var s=Math.sin(Math.PI/3);var c=Math.cos(Math.PI/3);
for(var i=-2;i<(canvas.width/1400)*120;i++){for(var j=0;j<8;j++){if(Math.random()+Math.random()<Math.pow((Math.sqrt(i*i+j*j)/55),2))continue;
var x=i*(1.2+c);var y=j*(2.4*s)+(i%2?1.2*s:0);var path=hex.map(translatePoint(x,y));path=path.map(wavePoint);path=path.map(scalePoint(10,7));fillPath(ctx,path);}}
document.body.style.background='#000 url('+canvas.toDataURL()+') no-repeat top left';}</script>
</head>
<body onload="drawbg()">
</body>
</html>