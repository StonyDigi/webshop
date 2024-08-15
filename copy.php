<?php
session_start();

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = array();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["product_id"])) {
        $product_id = $_POST["product_id"];
        array_push($_SESSION["cart"], $product_id);
        echo "Termék hozzáadva a kosárhoz.";
    }
} else {
    // Kosár megjelenítése
    echo "<!DOCTYPE html>";
    echo "<html lang='hu'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Kosár</title>";
    echo "<link href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' rel='stylesheet'>";
    echo "</head>";
    echo "<body>";
    echo "<div class='container mt-5'>";
    echo "<h2>Kosár</h2>";
    if (!empty($_SESSION["cart"])) {
        echo "<ul class='list-group'>";
        foreach (array_count_values($_SESSION["cart"]) as $id => $qty) {
            echo "<li class='list-group-item'>Termék ID: $id - Mennyiség: $qty</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='text-warning'>A kosár üres.</p>";
    }
    echo "<a href='index.php' class='btn btn-primary mt-3'>Vissza a főoldalra</a>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
}
?>
