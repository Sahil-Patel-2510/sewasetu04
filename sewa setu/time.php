<!DOCTYPE html>
<html>
<head>
<title>Request Sent</title>

<style>
body{
    font-family:Arial;
    background:linear-gradient(135deg,#667eea,#764ba2);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    margin:0;
}

.card{
    background:white;
    padding:40px;
    border-radius:12px;
    text-align:center;
    width:350px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

h2{
    margin-bottom:10px;
}

.timer{
    font-size:40px;
    font-weight:bold;
    color:#ff3b3b;
    margin-top:20px;
}

.status{
    margin-top:15px;
    font-weight:bold;
    color:#444;
}
</style>
</head>

<body>

<div class="card">
    <h2>Request Sent</h2>
    <p>Your request has been sent to nearby worker</p>

    <div class="timer" id="timer">02:00</div>

    <div class="status" id="status">
        Waiting for worker response...
    </div>
</div>

<script>
let time = 120;

const timer = document.getElementById("timer");
const status = document.getElementById("status");

let countdown = setInterval(function(){

    let minutes = Math.floor(time / 60);
    let seconds = time % 60;

    minutes = minutes < 10 ? "0" + minutes : minutes;
    seconds = seconds < 10 ? "0" + seconds : seconds;

    timer.innerHTML = minutes + ":" + seconds;

    if(time <= 0){
        clearInterval(countdown);
        status.innerHTML = "Worker Response Time Over";

        // Auto open next page
        setTimeout(function(){
            window.location.href = "view_workers.php";
        }, 1500);
    }

    time--;

}, 1000);
</script>

</body>
</html>