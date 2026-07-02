<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sewa Setu</title>

<style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family: 'Segoe UI', sans-serif;
    }

    body{
        height:100vh;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        display:flex;
        flex-direction:column;
    }

    /* Navbar */
    .navbar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:15px 50px;
        color:white;
    }

    .logo{
        font-size:24px;
        font-weight:bold;
        letter-spacing:1px;
    }

    .login-btn{
        padding:8px 18px;
        border:none;
        border-radius:20px;
        background:white;
        color:#2a5298;
        cursor:pointer;
        transition:0.3s;
    }

    .login-btn:hover{
        background:#ffd700;
        color:black;
    }

    /* Hero Section */
    .hero{
        flex:1;
        display:flex;
        justify-content:center;
        align-items:center;
        text-align:center;
        color:white;
    }

    .glass-box{
        padding:50px;
        border-radius:20px;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }

    h1{
        font-size:48px;
        margin-bottom:10px;
    }

    p{
        font-size:18px;
        margin-bottom:30px;
        color:#ddd;
    }

    /* Buttons */
    .btn-container{
        display:flex;
        gap:20px;
        justify-content:center;
        flex-wrap:wrap;
    }

    .btn{
        padding:12px 25px;
        border:none;
        border-radius:25px;
        font-size:16px;
        cursor:pointer;
        transition:0.3s;
    }

    .register{
        background:#28a745;
        color:white;
    }

    .register:hover{
        background:#1e7e34;
        transform:scale(1.05);
    }

    .view{
        background:#ffc107;
        color:black;
    }

    .view:hover{
        background:#e0a800;
        transform:scale(1.05);
    }

    .admin{
        background:#f8f9fa;
        color:black;
    }

    .admin:hover{
        background:#ddd;
        transform:scale(1.05);
    }

    /* Footer */
    .footer{
        text-align:center;
        padding:10px;
        color:#ccc;
        font-size:14px;
    }

</style>
</head>

<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">Sewa Setu</div>
   
</div>

<!-- Hero Section -->
<div class="hero">
    <div class="glass-box">
        <h1>Sewa Setu</h1>
        <p>Find Skilled Workers Near You</p>

        <div class="btn-container">
            <a href="register_worker.php" class="btn register">Register Worker</a>
            <a href="view_workers.php" class="btn view">View Workers</a>
            <a href="admin_login.php" class="btn admin">Admin Login</a>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    © 2026 Sewa Setu | Connecting Workers & People
</div>

</body>
</html>