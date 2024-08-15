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
    die("Az adatbáziskapcsolat nem sikerült: " . $conn->connect_error);
}

// Csak bejelentkezett felhasználók használhatják a kosár funkciókat
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
$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kosár Tartalma</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Kosár Tartalma</h2>
    <div id="cart-content">
        <ul class="list-group" id="cart-list">
            <?php foreach ($cart_items as $item): ?>
                <li class="list-group-item">
                    <div class="d-flex align-items-center">
                        <img src="images/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" class="img-thumbnail" style="width: 50px; height: 50px; margin-right: 10px;" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div>
                            <h5><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p>Ár: <?php echo $item['price']; ?> Ft</p>
                            <p>Mennyiség: 
                                <button class="btn btn-secondary btn-sm update-quantity" data-id="<?php echo $item['id']; ?>" data-action="remove">-</button>
                                <span class="item-quantity"><?php echo $item['quantity']; ?></span>
                                <button class="btn btn-secondary btn-sm update-quantity" data-id="<?php echo $item['id']; ?>" data-action="add">+</button>
                                <button class="btn btn-danger btn-sm delete-item" data-id="<?php echo $item['id']; ?>">Törlés</button>
                            </p>
                            <p>Összesen: <span class="item-total"><?php echo $item['price'] * $item['quantity']; ?></span> Ft</p>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
            <li class="list-group-item"><strong>Teljes összeg: <span id="total-price"><?php echo $total_price; ?></span> Ft</strong></li>
        </ul>
    </div>
    <a href="index.php" class="btn btn-primary mt-3">Vásárlás folytatása</a>
    <!-- Fizetés gomb -->
    <a href="checkout.php" class="btn btn-success mt-3">Fizetés</a>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="cart_script.js"></script>
</body>
</html>
