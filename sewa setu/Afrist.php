<!DOCTYPE html>
<html>
<head>
<title>Sewa Setu</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI', sans-serif;
scroll-behavior:smooth;
}

body{
background:#0f172a;
color:#e2e8f0;
overflow-x:hidden;
}

/* NAVBAR */
.navbar{
display:flex;
justify-content:space-between;
align-items:center;
padding:18px 60px;
background:rgba(255,255,255,0.04);
backdrop-filter:blur(10px);
position:sticky;
top:0;
z-index:100;
}

.logo{
font-size:28px;
font-weight:700;
color:#38bdf8;
}

.login-btn{
padding:10px 22px;
background:#38bdf8;
border-radius:25px;
color:#000;
text-decoration:none;
}

/* HERO */
.hero{
height:100vh;
display:flex;
flex-direction:column;
justify-content:center;
align-items:center;
text-align:center;
position:relative;
overflow:hidden;
background:linear-gradient(-45deg,#0f172a,#1e293b,#1d4ed8,#0ea5e9);
background-size:400% 400%;
animation:gradientMove 10s ease infinite;
}

@keyframes gradientMove{
0%{background-position:0% 50%;}
50%{background-position:100% 50%;}
100%{background-position:0% 50%;}
}

/* 3D CANVAS */
#canvas{
position:absolute;
top:0;
left:0;
width:100%;
height:100%;
z-index:1;
}

/* HERO CONTENT */
.hero-content{
position:relative;
z-index:2;
}

.hero h1{
font-size:65px;
margin-bottom:15px;
}

.hero p{
font-size:20px;
margin-bottom:30px;
color:#cbd5f5;
}

.hero-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 16px;
}

.button{
padding:14px 35px;
background:#38bdf8;
border-radius:30px;
color:#000;
text-decoration:none;
display:inline-block;
border:2px solid transparent;
transition: background 0.2s ease, transform 0.2s ease;
}

.button:hover{
    background:#0ea5e9;
    transform: translateY(-2px);
}

/* SECTION */
.section{
padding:80px 60px;
text-align:center;
}

.section h2{
font-size:38px;
margin-bottom:50px;
}

.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:30px;
}

.card{
background:rgba(255,255,255,0.05);
padding:25px;
border-radius:14px;
transition:0.3s;
}

.card:hover{
transform:translateY(-10px);
background:#1d4ed8;
}

/* FEATURES */
.features{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
gap:30px;
padding:60px;
}

.feature-box{
background:#020617;
padding:25px;
border-radius:12px;
}

/* STATS */
.stats{
display:flex;
justify-content:space-around;
padding:60px;
background:#020617;
flex-wrap:wrap;
}

.stats span{
font-size:35px;
color:#38bdf8;
}

/* JOIN */
.join{
padding:80px;
text-align:center;
}

.join .button{
margin:10px;
}

/* FOOTER */
.footer{
text-align:center;
padding:20px;
color:#94a3b8;
}

</style>

</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
<div class="logo">Sewa Setu</div>
<a class="login-btn" href="login.php">Login</a>
</div>

<!-- HERO WITH 3D -->
<div class="hero">

<div id="canvas"></div>

<div class="hero-content">
<h1>Sewa Setu</h1>
<p>Find Skilled Workers Near You or Become a Worker Easily</p>
<div class="hero-actions">
    <a class="button" href="login.php">Find Worker</a>
    <a class="button" href="login2.php">Work in Sewa Setu</a>
</div>
</div>

</div>

<!-- WORKERS -->
<div class="section">
<h2>Worker Categories</h2>

<div class="grid">
<div class="card">Electrical</div>
<div class="card">Mechanical</div>
<div class="card">Plumber</div>
<div class="card">Painter</div>
<div class="card">Labor</div>
<div class="card">Carpenter</div>
</div>
</div>

<!-- FEATURES -->
<div class="features">
<div class="feature-box">⚡ Fast Worker Search</div>
<div class="feature-box">📍 Location Based Hiring</div>
<div class="feature-box">🔒 Secure Platform</div>
<div class="feature-box">💼 Job Opportunities</div>
</div>

<!-- STATS -->
<div class="stats">
<div><span>1.</span>24*7 availability</div>
<div><span>2.</span>Safe and Reliable</div>
<div><span>3.</span>Trusted Platform</div>
</div>

<!-- JOIN -->
<div class="join">

<h2>Join Us Today!</h2>
<a class="button">Hire Worker</a>
<a class="button">Become Worker</a>
</div>

<!-- FOOTER -->
<div class="footer">
© 2026 Sewa Setu
</div>

<!-- THREE JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<script>

// Scene
const scene = new THREE.Scene();

// Camera
const camera = new THREE.PerspectiveCamera(75, window.innerWidth/window.innerHeight, 0.1, 1000);

// Renderer
const renderer = new THREE.WebGLRenderer({ alpha:true, antialias:true });
renderer.setSize(window.innerWidth, window.innerHeight);
document.getElementById("canvas").appendChild(renderer.domElement);

// Lights
const light1 = new THREE.PointLight(0xffffff, 1.5);
light1.position.set(5,5,5);
scene.add(light1);

const light2 = new THREE.PointLight(0x38bdf8, 1);
light2.position.set(-5,-5,5);
scene.add(light2);

// Material
const material = new THREE.MeshStandardMaterial({
color:0x38bdf8,
metalness:0.8,
roughness:0.3
});

// 🔨 Hammer (Right)
const hammer = new THREE.Group();

const handle = new THREE.Mesh(new THREE.CylinderGeometry(0.1,0.1,2), material);
handle.rotation.z = Math.PI/4;

const head = new THREE.Mesh(new THREE.BoxGeometry(1,0.3,0.3), material);
head.position.y = 1;

hammer.add(handle);
hammer.add(head);
hammer.position.set(3,0,0);
scene.add(hammer);

// 🔌 Drill (Left)
const drill = new THREE.Group();

const body = new THREE.Mesh(new THREE.BoxGeometry(1.2,0.6,0.6), material);

const dHandle = new THREE.Mesh(new THREE.BoxGeometry(0.3,1,0.3), material);
dHandle.position.set(0,-0.8,0);

const bit = new THREE.Mesh(new THREE.ConeGeometry(0.2,1,32), material);
bit.rotation.z = Math.PI/2;
bit.position.x = 1;

drill.add(body);
drill.add(dHandle);
drill.add(bit);
drill.position.set(-3,0,0);
scene.add(drill);

// Camera
camera.position.z = 6;

// Animation
function animate(){
requestAnimationFrame(animate);
hammer.rotation.y += 0.015;
drill.rotation.y -= 0.015;
renderer.render(scene, camera);
}
animate();

// Resize
window.addEventListener("resize", ()=>{
camera.aspect = window.innerWidth/window.innerHeight;
camera.updateProjectionMatrix();
renderer.setSize(window.innerWidth, window.innerHeight);
});

</script>

</body>
</html>