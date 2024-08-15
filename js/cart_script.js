$(document).ready(function() {
    // Kosár ikon frissítése
    function updateCartIcon() {
        $.get("cart.php", function(data) {
            var totalItems = 0;
            data = JSON.parse(data);
            $.each(data, function(index, item) {
                totalItems += item.quantity;
            });
            $("#cart-icon").text('(' + totalItems + ')');
        });
    }

    // Kosár tartalom betöltése
    function loadCart() {
        $.get("cart.php", function(data) {
            var cartContent = "<ul class='list-group'>";
            var totalPrice = 0;
            var totalItems = 0;
            data = JSON.parse(data);
            $.each(data, function(index, item) {
                var itemTotal = item.price * item.quantity;
                totalPrice += itemTotal;
                totalItems += item.quantity;
                cartContent += "<li class='list-group-item'>" +
                    "<div class='d-flex align-items-center'>" +
                    "<img src='images/" + item.image + "' class='img-thumbnail' style='width: 50px; height: 50px; margin-right: 10px;' alt='" + item.name + "'>" +
                    "<div>" +
                    "<h5>" + item.name + "</h5>" +
                    "<p>Ár: " + item.price + " Ft</p>" +
                    "<p>Mennyiség: " +
                    "<button class='btn btn-secondary btn-sm update-quantity' data-id='" + item.id + "' data-action='remove'>-</button> " +
                    "<span class='item-quantity'>" + item.quantity + "</span> " +
                    "<button class='btn btn-secondary btn-sm update-quantity' data-id='" + item.id + "' data-action='add'>+</button> " +
                    "<button class='btn btn-danger btn-sm delete-item' data-id='" + item.id + "'>Törlés</button></p>" +
                    "<p>Összesen: <span class='item-total'>" + itemTotal + "</span> Ft</p>" +
                    "</div>" +
                    "</div>" +
                    "</li>";
            });
            cartContent += "<li class='list-group-item'><strong>Teljes összeg: <span id='total-price'>" + totalPrice + "</span> Ft</strong></li>";
            cartContent += "</ul>";
            $("#cart-list").html(cartContent);
            $("#cart-icon").text('(' + totalItems + ')');
        });
    }

    // Dinamikus eseménykezelők a gombokhoz
    $("#cart-list").on("click", ".update-quantity, .delete-item", function() {
        var id = $(this).data("id");
        var action = $(this).data("action") || 'delete';
        $.post("cart.php", { product_id: id, action: action }, function(data) {
            loadCart(); // Frissítsd a kosarat az AJAX kérés után
        });
    });

    // Kosár tartalom automatikus betöltése az oldal betöltésekor
    loadCart();

    // Termék kosárba helyezése az index oldalon
    $(".add-to-cart").click(function() {
        var id = $(this).data("id");
        if (id) {
            $.post("cart.php", { product_id: id, action: "add" }, function(data) {
                alert("A termék sikeresen bekerült a kosárba!"); // Visszaigazoló üzenet
                loadCart(); // Kosár frissítése kosárba helyezés után
            });
        }
    });

    // Kosár tartalom betöltése a modális ablak megnyitásakor
    $("#cart-link").click(function() {
        $.get("cart.php", function(data) {
            data = JSON.parse(data);
            var cartContent = "<ul class='list-group'>";
            var totalPrice = 0;
            $.each(data, function(index, item) {
                var itemTotal = item.price * item.quantity;
                totalPrice += itemTotal;
                cartContent += "<li class='list-group-item'>" +
                    "<div class='d-flex align-items-center'>" +
                    "<img src='images/" + item.image + "' class='img-thumbnail' style='width: 50px; height: 50px; margin-right: 10px;' alt='" + item.name + "'>" +
                    "<div>" +
                    "<h5>" + item.name + "</h5>" +
                    "<p>Ár: " + item.price + " Ft</p>" +
                    "<p>Mennyiség: " + item.quantity + "</p>" +
                    "<p>Összesen: " + itemTotal + " Ft</p>" +
                    "</div>" +
                    "</div>" +
                    "</li>";
            });
            cartContent += "<li class='list-group-item'><strong>Teljes összeg: " + totalPrice + " Ft</strong></li>";
            cartContent += "</ul>";
            $("#cart-modal-content").html(cartContent);
        });
    });
});
