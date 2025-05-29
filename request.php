<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$cities = [
    "New York", "London", "Paris", "Tokyo", "Sydney",
    "Dubai", "Toronto", "Berlin", "Rome", "Bangkok",
    "Istanbul", "Barcelona", "Los Angeles", "Chicago", "Singapore",
    "Amsterdam", "Seoul", "Moscow", "Mumbai", "Cairo"
];

$user_email = $_SESSION['email'];
$error = '';

$conn = new mysqli("localhost", "root", "", "aqi");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch user info for modal (if modal is to be shown)
$user = null;
if (isset($_GET['show_profile'])) {
    $stmt = $conn->prepare("SELECT fullName, email, dob, gender, country FROM users WHERE email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Handle city selection POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cities'])) {
    if (!isset($_POST['cities']) || count($_POST['cities']) < 1 || count($_POST['cities']) > 10) {
        $error = "⚠️ Please select at least one city but not more than ten.";
    } else {
        $_SESSION['selected_cities'] = $_POST['cities'];
        header("Location: showaqi.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Cities</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }

        .form-container {
            background: #fff;
            max-width: 450px;
            margin: auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .city-option {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        input[type="submit"] {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            margin-top: 15px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background: #0056b3;
        }

        button.logout-button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            margin-top: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button.logout-button:hover {
            background: #a71d2a;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Profile Button Container */
        .profile-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .profile-avatar-btn {
            background: linear-gradient(135deg, #4A90E2, #357ABD);
            border: none;
            cursor: pointer;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            padding: 0;
            box-shadow:
              0 4px 15px rgba(74, 144, 226, 0.6),
              0 0 8px rgba(74, 144, 226, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.4s ease, box-shadow 0.4s ease, transform 0.3s ease;
            filter: drop-shadow(0 0 4px #4A90E2);
            will-change: transform;
        }

        .profile-avatar-btn:hover {
            background: linear-gradient(135deg, #5AA0FF, #3A8CEB);
            box-shadow:
              0 6px 20px rgba(58, 140, 235, 0.8),
              0 0 15px rgba(58, 140, 235, 1);
            transform: scale(1.1) rotate(5deg);
        }

        .profile-avatar-btn svg {
            width: 48px;
            height: 48px;
            fill: white;
            filter: drop-shadow(0 0 3px rgba(255,255,255,0.7));
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Modal container */
        .profile-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 350px;
            max-width: 90vw;
            background-color: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transform: translate(-50%, -50%);
            z-index: 1000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .profile-modal h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        /* Profile Info Display */
        .profile-info {
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .profile-info:last-of-type {
            border-bottom: none;
        }

        .profile-label {
            font-weight: 600;
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .profile-value {
            color: #333;
            font-size: 15px;
        }

        /* Action Buttons */
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .edit-profile-btn {
            background-color: #4A90E2;
            color: white;
            font-weight: 700;
            border: none;
            padding: 12px 0;
            flex: 1;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.4);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .edit-profile-btn:hover {
            background-color: #357ABD;
            box-shadow: 0 6px 16px rgba(53, 122, 189, 0.6);
        }

        .close-profile {
            background: #aaa;
            border: none;
            padding: 12px 0;
            flex: 1;
            border-radius: 8px;
            cursor: pointer;
            color: white;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .close-profile:hover {
            background: #888;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Select at least One or at most Ten Cities</h2>

    <?php if ($error) echo "<div class='error'>$error</div>"; ?>

    <div class="profile-container">
      <form method="GET" action="">
        <button type="submit" name="show_profile" value="1" class="profile-avatar-btn" aria-label="User Profile">
          <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" width="36" height="36" aria-hidden="true">
            <path d="M12 12c2.7 0 4.9-2.2 4.9-4.9S14.7 2.2 12 2.2 7.1 4.4 7.1 7.1 9.3 12 12 12zm0 2.4c-3.3 0-9.8 1.7-9.8 5v2.3h19.6v-2.3c0-3.3-6.5-5-9.8-5z"/>
          </svg>
        </button>
      </form>
    </div>

    <form method="post">
        <?php foreach ($cities as $city): ?>
            <div class="city-option">
                <label><?= htmlspecialchars($city) ?></label>
                <input type="checkbox" name="cities[]" value="<?= htmlspecialchars($city) ?>">
            </div>
        <?php endforeach; ?>
        <input type="submit" value="Submit">
    </form>

    <button type="button" class="logout-button" window.location.href = 'index.php'>Logout</button>
</div>

<?php if (isset($_GET['show_profile']) && $user): ?>
    <div class="modal-overlay"></div>
    <div class="profile-modal">
        <h2>My Profile</h2>

        <div class="profile-info">
            <div class="profile-label">Full Name</div>
            <div class="profile-value"><?= htmlspecialchars($user['fullName']) ?></div>
        </div>

        <div class="profile-info">
            <div class="profile-label">Email</div>
            <div class="profile-value"><?= htmlspecialchars($user['email']) ?></div>
        </div>

        <div class="profile-info">
            <div class="profile-label">Date of Birth</div>
            <div class="profile-value"><?= htmlspecialchars($user['dob']) ?></div>
        </div>

        <div class="profile-info">
            <div class="profile-label">Gender</div>
            <div class="profile-value"><?= ucfirst(htmlspecialchars($user['gender'])) ?></div>
        </div>

        <div class="profile-info">
            <div class="profile-label">Country</div>
            <div class="profile-value"><?= htmlspecialchars($user['country']) ?></div>
        </div>

        <div class="modal-actions">
            <a href="profile.php" class="edit-profile-btn">Edit Profile</a>
            <form method="GET" action="" style="flex: 1;">
                <button class="close-profile" type="submit">Close</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script type="text/javascript">
    document.getElementById('logout-button').addEventListener('click', function () {
        // Trigger logout logic (e.g., API call, redirect, etc.)
        
        // Prevent back navigation after logout
        function preventBack() {
            window.history.forward();
        }
        setTimeout(preventBack, 0);
        window.onunload = function () { null };
        
        // Optional: Redirect to login or homepage
        window.location.href = 'index.php'; // replace with your login page
    });
</script>


</body>
</html>
