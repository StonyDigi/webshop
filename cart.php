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

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if ($product_id === false) {
            echo json_encode(['error' => 'Hibás termékazonosító.']);
            exit;
        }

        if ($action === 'add') {
            // Termék mennyiségének növelése
            $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'remove') {
            // Termék mennyiségének csökkentése
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();

            // Tétel eltávolítása, ha a mennyiség nulla
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ? AND quantity <= 0");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'delete') {
            // Termék teljes eltávolítása
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Kosár tartalmának lekérdezése
$stmt = $conn->prepare("SELECT products.id, products.name, products.price, products.image, cart_items.quantity FROM cart_items INNER JOIN products ON cart_items.product_id = products.id WHERE cart_items.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$response = [];
while ($row = $result->fetch_assoc()) {
    $response[] = [
        'id' => $row['id'],
        'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'image' => htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8')
    ];
}
echo json_encode($response);

$stmt->close();
$conn->close();
?>
