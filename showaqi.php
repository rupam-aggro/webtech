<?php
session_start();

if (!isset($_SESSION['selected_cities']) || count($_SESSION['selected_cities']) < 1 || count($_SESSION['selected_cities']) > 10) {
    header("Location: request.php");
    exit();
}

$bgColor = isset($_COOKIE['color']) ? htmlspecialchars($_COOKIE['color']) : '#f4f6f8';
$host = "localhost";
$db = "aqi";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selectedCities = $_SESSION['selected_cities'];
$placeholders = rtrim(str_repeat('?,', count($selectedCities)), ',');
$sql = "SELECT city, country, aqi FROM info WHERE city IN ($placeholders)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($selectedCities)), ...$selectedCities);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Selected City Info</title>
    <style>
        body {
            font-family: Arial;
            background: <?= $bgColor ?>;
            padding: 20px;
        }
        table {
            width: 60%;
            margin: auto;
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
        }
        .logout-button:hover {
            background: #a71d2a;
        }
    </style>
</head>
<body>

<h2>Air Quality Index (AQI) for Selected Cities</h2>

<table>
    <tr>
        <th>City</th>
        <th>Country</th>
        <th>AQI</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['city']) . "</td>
                    <td>" . htmlspecialchars($row['country']) . "</td>
                    <td>" . htmlspecialchars($row['aqi']) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No data found.</td></tr>";
    }
    $stmt->close();
    $conn->close();
    ?>
</table>

<div class="logout-container">
    <form action="index.php" method="post">
        <button type="submit" class="logout-button">Logout</button>
    </form>
</div>

</body>
</html>
