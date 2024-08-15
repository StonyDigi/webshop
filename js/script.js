$(document).ready(function() {
    // Funkció a kosár ikon frissítésére
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

    // Termék hozzáadása a kosárhoz
    $(".add-to-cart").click(function() {
        var id = $(this).data("id");
        if (id) {
            $.post("cart.php", { product_id: id, action: "add" }, function(data) {
                alert("A termék sikeresen bekerült a kosárba!"); // Visszaigazoló üzenet
                updateCartIcon(); // Kosár ikon frissítése
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

    // Kosár tartalom automatikus betöltése az oldal betöltésekor
    updateCartIcon();
});
