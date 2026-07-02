<?php
include "config/db.php";

function normalizeMobile($mobile) {
    $clean = preg_replace('/\D+/', '', $mobile);
    if(strlen($clean) === 10) {
        return '+91' . $clean;
    }
    if(strlen($clean) === 12 && substr($clean, 0, 2) === '91') {
        return '+' . $clean;
    }
    return false;
}

$message = "";

if(isset($_POST['submit'])){
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $skill = trim($_POST['skill']);
    $experience = (int)$_POST['experience'];
    $location = trim($_POST['location']);

    if($name === '' || $phone === '' || $skill === '' || $location === '' || $experience < 0) {
        $message = "Please fill in all required fields correctly.";
    } elseif(!($normalized_phone = normalizeMobile($phone))) {
        $message = "Please enter a valid 10-digit mobile number, with optional +91 prefix.";
    } else {
        $phone = $normalized_phone;

        $stmt = mysqli_prepare($conn, "SELECT id FROM workers WHERE phone = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $phone);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if(mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Mobile number already exists. Please use a different mobile number.";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($conn, "INSERT INTO workers(name,phone,skill,experience,location) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssds", $name, $phone, $skill, $experience, $location);

            if(mysqli_stmt_execute($stmt)){
                header("Location: worker.php");
                exit;
            } else {
                $message = "Registration failed: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Worker</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="login-container">
    <h2>Register Worker</h2>
    <?php if(!empty($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" class="login-form">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" required>

        <label for="skill">Skill</label>
        <select id="skill" name="skill" required>
            <option value="Mechanical">Mechanical</option>
            <option value="Electrical">Electrical</option>
            <option value="Plumber">Plumber</option>
            <option value="Labor">Labor</option>
            <option value="Carpenter">Carpenter</option>
        </select>

        <label for="experience">Experience (Years)</label>
        <input type="number" id="experience" name="experience" min="0" required>

        <label for="location">Location</label>
        <input type="text" id="location" name="location" required>

        <button type="submit" name="submit">Join</button>
    </form>
</div>

</body>
</html>