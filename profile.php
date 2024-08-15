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

// Felhasználói adatok lekérdezése
$stmt = $conn->prepare("SELECT username, email, billing_address, shipping_address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $billing_address, $shipping_address);
$stmt->fetch();
$stmt->close();

// Rendelések lekérdezése
$stmt = $conn->prepare("SELECT orders.id, products.name, orders.quantity, orders.total_price, orders.order_date FROM orders INNER JOIN products ON orders.product_id = products.id WHERE orders.user_id = ? ORDER BY orders.order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilom</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Profilom</h2>
        <form method="POST" action="profile.php">
            <div class="form-group">
                <label for="username">Felhasználónév</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Jelszó (hagyja üresen, ha nem szeretné megváltoztatni)</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="form-group">
                <label for="billing_address">Számlázási cím</label>
                <input type="text" class="form-control" id="billing_address" name="billing_address" value="<?php echo htmlspecialchars($billing_address, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="shipping_address">Szállítási cím</label>
                <input type="text" class="form-control" id="shipping_address" name="shipping_address" value="<?php echo htmlspecialchars($shipping_address, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Profil frissítése</button>
        </form>
        
        <h3 class="mt-5">Rendeléseim</h3>
        <ul class="list-group">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <li class="list-group-item">
                        <div>
                            <strong>Termék:</strong> <?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?><br>
                            <strong>Mennyiség:</strong> <?php echo $order['quantity']; ?><br>
                            <strong>Összeg:</strong> <?php echo $order['total_price']; ?> Ft<br>
                            <strong>Dátum:</strong> <?php echo $order['order_date']; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-group-item">Nincsenek korábbi rendelései.</li>
            <?php endif; ?>
        </ul>

        <a href="index.php" class="btn btn-secondary mt-3">Vissza a főoldalra</a>
    </div>

    <!-- jQuery és Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
