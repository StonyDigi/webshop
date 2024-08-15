<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "products";

// Adatbázis kapcsolat létrehozása
$conn = new mysqli($servername, $username, $password, $dbname);

// Kapcsolat ellenőrzése
if ($conn->connect_error) {
    die("Az adatbáziskapcsolat nem sikerült, próbáld újra később.");
}

// Termékek lekérdezése
$stmt = $conn->prepare("SELECT id, name, description, price, image FROM products");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webshop</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome Ikonok -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand" href="#">Webshop logo</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="index.php">Főoldal</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Kategóriák
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="#">Elektronika</a>
                            <a class="dropdown-item" href="#">Ruházat</a>
                            <a class="dropdown-item" href="#">Sport</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="cart-link" data-toggle="modal" data-target="#cartModal">Kosár <span id="cart-icon">(0)</span></a>
                    </li>
                    <!-- Dinamikus felhasználói menü -->
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Regisztráció</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Bejelentkezés</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <span class="navbar-text">Bejelentkezve: <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Kijelentkezés</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container mt-5">
        <h2 class="mb-4">Kiemelt termékek</h2>
        <!-- Carousel -->
        <div id="productCarousel" class="carousel slide mb-5" data-ride="carousel">
            <div class="carousel-inner">
                <?php
                $active = 'active';
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='carousel-item " . $active . "'>";
                        echo "<img src='images/" . htmlspecialchars($row["image"], ENT_QUOTES, 'UTF-8') . "' class='d-block w-100' alt='" . htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8') . "'>";
                        echo "<div class='carousel-caption d-none d-md-block'>";
                        echo "<h5>" . htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8') . "</h5>";
                        echo "<p>" . htmlspecialchars($row["description"], ENT_QUOTES, 'UTF-8') . "</p>";
                        echo "</div>";
                        echo "</div>";
                        $active = '';
                    }
                } else {
                    echo "Nincsenek elérhető termékek.";
                }
                ?>
            </div>
            <a class="carousel-control-prev" href="#productCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Előző</span>
            </a>
            <a class="carousel-control-next" href="#productCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Következő</span>
            </a>
        </div>

        <h2 class="mb-4">Összes termék</h2>
        <div class="row" id="product-list">
            <?php
            $result->data_seek(0); // Visszaállítjuk a mutatót az eredmény elejére
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='col-md-4'>";
                    echo "<div class='card mb-4 shadow-sm'>";
                    echo "<img src='images/" . htmlspecialchars($row["image"], ENT_QUOTES, 'UTF-8') . "' class='card-img-top' alt='" . htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8') . "'>";
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>" . htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8') . "</h5>";
                    echo "<p class='card-text'>" . htmlspecialchars($row["description"], ENT_QUOTES, 'UTF-8') . "</p>";
                    echo "<p class='card-text'>" . htmlspecialchars($row["price"], ENT_QUOTES, 'UTF-8') . " Ft</p>";
                    if (isset($_SESSION['user_id'])) {
                        echo "<button class='btn btn-primary add-to-cart' data-id='" . htmlspecialchars($row["id"], ENT_QUOTES, 'UTF-8') . "'>Kosárba</button>";
                    } else {
                        echo "<p class='text-danger'>Jelentkezz be a vásárláshoz</p>";
                    }
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "Nincsenek elérhető termékek.";
            }
            ?>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-4">
                    <h5>Kapcsolat</h5>
                    <ul class="list-unstyled">
                        <li>Cím: 3300 Eger, Fő utca 1.</li>
                        <li>Telefon: +36 1 234 5678</li>
                        <li>Email: info@webshop.hu</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Hasznos linkek</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Szállítási feltételek</a></li>
                        <li><a href="#" class="text-white">Adatvédelmi nyilatkozat</a></li>
                        <li><a href="#" class="text-white">Vásárlási feltételek</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Kövess minket</h5>
                    <a href="https://www.facebook.com" class="text-white mr-2" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.twitter.com" class="text-white mr-2" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com" class="text-white" target="_blank"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="text-center mt-3">
                &copy; 2024 Somossy László Webshopja.
                <p>Minden jog fenntartva.</p>
            </div>
        </div>
    </footer>

    <!-- Kosár Modális -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Kosár tartalma</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="cart-modal-content">
                    <!-- Kosár tartalma dinamikusan kerül ide -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Bezárás</button>
                    <a href="cart_page.php" class="btn btn-primary">Kosár oldal</a>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery és Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
