<!DOCTYPE html>
<html>
<head>
    <title>Select Cities</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        .form-container {
            background: #fff;
            max-width: 400px;
            margin: auto;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .city-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .city-option:last-child {
            border-bottom: none;
        }

        input[type="submit"] {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            width: 100%;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background: #0056b3;
        }

        ul {
            list-style-type: disc;
            padding-left: 20px;
        }

        .results {
            max-width: 400px;
            margin: 20px auto;
            background: #e8f0fe;
            padding: 15px 20px;
            border-radius: 6px;
            border-left: 5px solid #007bff;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Select Your Favorite Cities</h2>

    <form action="" method="post">
        <?php
        $cities = [
            "New York", "London", "Paris", "Tokyo", "Sydney",
            "Dubai", "Toronto", "Berlin", "Rome", "Bangkok",
            "Istanbul", "Barcelona", "Los Angeles", "Chicago", "Singapore",
            "Amsterdam", "Seoul", "Moscow", "Mumbai", "Cairo"
        ];

        foreach ($cities as $city) {
            echo '<div class="city-option">';
            echo '<span>' . htmlspecialchars($city) . '</span>';
            echo '<input type="checkbox" name="cities[]" value="' . htmlspecialchars($city) . '">';
            echo '</div>';
        }
        ?>

        <input type="submit" name="submit" value="Submit">
    </form>
</div>

<?php
if (isset($_POST['submit'])) {
    echo '<div class="results">';
    if (!empty($_POST['cities'])) {
        echo "<h3>You selected:</h3><ul>";
        foreach ($_POST['cities'] as $selectedCity) {
            echo "<li>" . htmlspecialchars($selectedCity) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p><strong>No cities selected.</strong></p>";
    }
    echo '</div>';
}
?>

</body>
</html>
