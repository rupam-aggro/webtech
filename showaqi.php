<?php
session_start();

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['selected_cities']) || count($_SESSION['selected_cities']) < 1 || count($_SESSION['selected_cities']) > 10) {
    header("Location: request.php");
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$bgColor = isset($_COOKIE['color']) ? htmlspecialchars($_COOKIE['color']) : '#f4f6f8';
$host = "localhost";
$db = "aqi";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_email = $_SESSION['email'];

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

// Fetch AQI data
$selectedCities = $_SESSION['selected_cities'];
$placeholders = rtrim(str_repeat('?,', count($selectedCities)), ',');
$sql = "SELECT city, country, aqi FROM info WHERE city IN ($placeholders)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($selectedCities)), ...$selectedCities);
$stmt->execute();
$aqiResult = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Selected City Info</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: <?= $bgColor ?>;
            padding: 20px;
            margin: 0;
        }
        table {
            width: 60%;
            margin: 50px auto 0;
            border-collapse: collapse;
            background: #fff;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .logout-container {
            width: 60%;
            margin: 20px auto 0;
            text-align: center;
        }
        .logout-button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .logout-button:hover {
            background: #a71d2a;
        }
        
        /* Navigation Buttons Container */
        .nav-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        .back-button {
            background: linear-gradient(135deg, #28a745, #20933c);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 12px rgba(40, 167, 69, 0.4);
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: linear-gradient(135deg, #34ce57, #28a745);
            transform: translateY(-2px);
            box-shadow: 0 5px 16px rgba(40, 167, 69, 0.6);
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            table {
                width: 95%;
                font-size: 14px;
            }
            .logout-container {
                width: 95%;
            }
            .profile-avatar-btn {
                width: 60px;
                height: 60px;
            }
            .profile-avatar-btn svg {
                width: 32px;
                height: 32px;
            }
        }
    </style>
</head>
<body>

<!-- Navigation (Back to city selection) -->
<div class="nav-container">
    <a href="request.php" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
        </svg>
        Back to Cities
    </a>
</div>

<!-- Profile button top-right -->
<div class="profile-container">
    <form method="GET" action="">
        <button type="submit" name="show_profile" value="1" class="profile-avatar-btn" aria-label="User Profile">
            <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 12c2.7 0 4.9-2.2 4.9-4.9S14.7 2.2 12 2.2 7.1 4.4 7.1 7.1 9.3 12 12 12zm0 2.4c-3.3 0-9.8 1.7-9.8 5v2.3h19.6v-2.3c0-3.3-6.5-5-9.8-5z"/>
            </svg>
        </button>
    </form>
</div>

<h2>Air Quality Index (AQI) for Selected Cities</h2>

<table>
    <tr>
        <th>City</th>
        <th>Country</th>
        <th>AQI</th>
        <th>Status</th>
    </tr>

    <?php
    if ($aqiResult->num_rows > 0) {
        while ($row = $aqiResult->fetch_assoc()) {
            $aqi = intval($row['aqi']);
            $status = '';
            $statusColor = '';
            
            // AQI Status based on standard ranges
            if ($aqi <= 50) {
                $status = 'Good';
                $statusColor = '#00e400';
            } elseif ($aqi <= 100) {
                $status = 'Moderate';
                $statusColor = '#ffff00';
            } elseif ($aqi <= 150) {
                $status = 'Unhealthy for Sensitive';
                $statusColor = '#ff7e00';
            } elseif ($aqi <= 200) {
                $status = 'Unhealthy';
                $statusColor = '#ff0000';
            } elseif ($aqi <= 300) {
                $status = 'Very Unhealthy';
                $statusColor = '#8f3f97';
            } else {
                $status = 'Hazardous';
                $statusColor = '#7e0023';
            }
            
            echo "<tr>
                    <td>" . htmlspecialchars($row['city']) . "</td>
                    <td>" . htmlspecialchars($row['country']) . "</td>
                    <td style='font-weight: bold; color: " . $statusColor . ";'>" . htmlspecialchars($row['aqi']) . "</td>
                    <td style='color: " . $statusColor . "; font-weight: 600;'>" . $status . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No data found for selected cities.</td></tr>";
    }
    ?>
</table>

<div class="logout-container">
   <a href="showaqi.php?logout=true" class="logout-button" window.location.href = 'index.php'>Logout</a>
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
