<?php

$cities = [
    "New York", "London", "Paris", "Tokyo", "Sydney",
    "Dubai", "Toronto", "Berlin", "Rome", "Bangkok",
    "Istanbul", "Barcelona", "Los Angeles", "Chicago", "Singapore",
    "Amsterdam", "Seoul", "Moscow", "Mumbai", "Cairo"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['cities']) || count($_POST['cities']) !== 10) {
        $error = "⚠️ Please select exactly 10 cities.";
    } else {
        $_SESSION['selected_cities'] = $_POST['cities'];
        header("Location: showaqi.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select 10 Cities</title>
    <style>
        body { font-family: Arial; background: #f4f6f8; padding: 20px; }
        .form-container { background: #fff; max-width: 450px; margin: auto; padding: 25px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .city-option { display: flex; justify-content: space-between; padding: 8px 0; }
        input[type="submit"] { background: #007bff; color: white; border: none; padding: 10px; border-radius: 5px; width: 100%; margin-top: 15px; cursor: pointer; }
        input[type="submit"]:hover { background: #0056b3; }
        .error { color: red; font-weight: bold; margin-top: 10px; }
    </style>
    <script>
        // Optional JS: limit selection to 10
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const checked = document.querySelectorAll('input[type="checkbox"]:checked').length;
                    if (checked > 10) {
                        checkbox.checked = false;
                        alert("You can select only 10 cities.");
                    }
                });
            });
        });
    </script>
</head>
<body>

<div class="form-container">
    <h2>Select Exactly 10 Cities</h2>
    
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    
    <form method="post">
        <?php foreach ($cities as $city): ?>
            <div class="city-option">
                <label><?= htmlspecialchars($city) ?></label>
                <input type="checkbox" name="cities[]" value="<?= htmlspecialchars($city) ?>">
            </div>
        <?php endforeach; ?>
        <input type="submit" value="Submit">
    </form>
</div>

</body>
</html>
