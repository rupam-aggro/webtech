<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "aqi");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$email = $_SESSION['email'];
$message = "";
$error = "";

// Handle form submission (update profile)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName']);
    $dob = trim($_POST['dob']);
    $gender = strtolower(trim($_POST['gender']));
    $country = trim($_POST['country']);

    // Validate inputs
    if (empty($fullName) || empty($dob) || empty($gender) || empty($country)) {
        $error = "Please fill in all fields.";
    } elseif (!in_array($gender, ['male', 'female', 'other'])) {
        $error = "Invalid gender selected.";
    } else {
        // Update DB
        $stmt = $conn->prepare("UPDATE users SET fullName = ?, dob = ?, gender = ?, country = ? WHERE email = ?");
        $stmt->bind_param("sssss", $fullName, $dob, $gender, $country, $email);
        if ($stmt->execute()) {
            $message = "Profile updated successfully, You are redirected to the login page!";
            header("refresh:3;index.php");
        } else {
            $error = "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch current user info
$stmt = $conn->prepare("SELECT fullName, email, dob, gender, country FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

$genderLower = strtolower(trim($user['gender']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Update Profile</title>
<style>
   body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    padding: 20px;
}

.container {
    max-width: 450px;
    margin: auto;
    background: #fff;
    padding: 30px 25px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

/* Labels */
label {
    font-weight: 600;
    color: #555;
    font-size: 14px;
    margin-bottom: 5px;
    display: block;
}

/* Inputs and Selects */
input[type="text"],
input[type="date"],
select {
    color: #333;
    font-size: 15px;
    width: 100%;
    padding: 10px 14px;
    margin-bottom: 15px;
    border: 1.8px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus,
input[type="date"]:focus,
select:focus {
    border-color: #4A90E2;
    outline: none;
    box-shadow: 0 0 6px rgba(74,144,226,0.5);
}

/* Submit button */
input[type="submit"] {
    background-color: #4A90E2;
    color: white;
    font-weight: 700;
    border: none;
    padding: 14px 0;
    width: 100%;
    border-radius: 12px;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(74,144,226,0.4);
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

input[type="submit"]:hover {
    background-color: #357ABD;
    box-shadow: 0 6px 16px rgba(53,122,189,0.6);
}

    


    .message { text-align: center; margin-bottom: 15px; font-weight: 600; color: green; }
    .error { text-align: center; margin-bottom: 15px; font-weight: 600; color: red; }
</style>
</head>
<body>
<div class="container">
    <h2>Update Profile</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="profile.php">
        <label for="fullName">Full Name</label>
        <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($user['fullName']) ?>" required>

        <label for="email">Email (cannot change)</label>
        <input type="text" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>

        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($user['dob']) ?>" required>

        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
            <option value="male" <?= $genderLower === 'male' ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= $genderLower === 'female' ? 'selected' : '' ?>>Female</option>
            <option value="other" <?= $genderLower === 'other' ? 'selected' : '' ?>>Other</option>
        </select>

        <label for="country">Country</label>
        <input type="text" id="country" name="country" value="<?= htmlspecialchars($user['country']) ?>" required>

        <input type="submit" value="Update Profile">
    </form>
</div>
</body>
</html>
