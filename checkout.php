<?php
session_start();

// Adatbázis kapcsolat beállítása
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "products";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kapcsolat ellenőrzése
if ($conn->connect_error) {
    die("Az adatbáziskapcsolat nem sikerült, próbáld újra később.");
}

// Csak bejelentkezett felhasználók férhetnek hozzá
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Kosár tartalmának lekérdezése
$stmt = $conn->prepare("SELECT products.id, products.name, products.price, products.image, cart_items.quantity FROM cart_items INNER JOIN products ON cart_items.product_id = products.id WHERE cart_items.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0;
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_price += $row['price'] * $row['quantity'];
}

$stmt->close();

// Rendelés véglegesítése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    try {
        foreach ($cart_items as $item) {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
            $item_total_price = $item['price'] * $item['quantity'];
            $stmt->bind_param("iiid", $user_id, $item['id'], $item['quantity'], $item_total_price);
            $stmt->execute();
            $stmt->close();
        }

        // Töröljük a kosár tartalmát
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo "<script>alert('Rendelés sikeresen leadva!'); window.location.href='profile.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Hiba történt a rendelés feldolgozása során.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fizetés</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Fizetés</h2>
    <ul class="list-group mb-4">
        <?php foreach ($cart_items as $item): ?>
            <li class="list-group-item">
                <div class="d-flex align-items-center">
                    <img src="images/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" class="img-thumbnail" style="width: 50px; height: 50px; margin-right: 10px;" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div>
                        <h5><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                        <p>Ár: <?php echo $item['price']; ?> Ft</p>
                        <p>Mennyiség: <?php echo $item['quantity']; ?></p>
                        <p>Összesen: <?php echo $item['price'] * $item['quantity']; ?> Ft</p>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
        <li class="list-group-item"><strong>Teljes összeg: <?php echo $total_price; ?> Ft</strong></li>
    </ul>
    <form method="POST" action="checkout.php">
        <button type="submit" class="btn btn-success">Rendelés leadása</button>
    </form>
    <a href="index.php" class="btn btn-secondary mt-3">Vissza a főoldalra</a>
</div>

<!-- jQuery és Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
