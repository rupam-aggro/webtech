<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['fullName'], $_POST['email'], $_POST['password'])) {
    header("Location: index.php");
    exit();
}

$fullName = $_POST['fullName'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$dob = $_POST['dob'] ?? '';
$gender = $_POST['gender'] ?? '';
$country = $_POST['country'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $conn = new mysqli("localhost", "root", "", "aqi");
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO users (fullName, email, password, dob, gender, country) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssss", $fullName, $email, $hashedPassword, $dob, $gender, $country);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    $message = "✅ Your data has been saved to the database!";
    $redirect = true;

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])) {
    $message = "⚠️ Your data was not saved.";
    $redirect = true;

} else {
    $redirect = false;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirmation</title>
    <?php if ($redirect): ?>
    <meta http-equiv="refresh" content="2;url=index.php">
    <?php endif; ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #c9d6ff, #e2e2e2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .confirmation-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .confirmation-box p {
            font-size: 16px;
            margin: 10px 0;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        .cancel {
            background-color: #f44336;
            color: white;
        }

        .confirm {
            background-color: #4CAF50;
            color: white;
        }

        .message {
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h2>Confirm Your Information</h2>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($fullName); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($dob); ?></p>
        <p><strong>Gender:</strong> <?php echo htmlspecialchars($gender); ?></p>
        <p><strong>Country:</strong> <?php echo htmlspecialchars($country); ?></p>

        <?php if (!$redirect): ?>
        <form method="POST">
            <input type="hidden" name="fullName" value="<?php echo htmlspecialchars($fullName); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="hidden" name="password" value="<?php echo htmlspecialchars($password); ?>">
            <input type="hidden" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
            <input type="hidden" name="gender" value="<?php echo htmlspecialchars($gender); ?>">
            <input type="hidden" name="country" value="<?php echo htmlspecialchars($country); ?>">

            <div class="buttons">
                <button type="submit" name="cancel" class="cancel">Cancel</button>
                <button type="submit" name="confirm" class="confirm">Confirm</button>
            </div>
        </form>
        <?php else: ?>
            <div class="message"><?php echo $message; ?></div>
            <div class="note">Redirecting to homepage in 2 seconds...</div>
        <?php endif; ?>
    </div>
</body>
</html>
