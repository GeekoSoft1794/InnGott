<?php
require_once "vistas/partesuperior.php"
?>

<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';
$db = 'InnGott';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el ID del usuario logueado (esto debería ser reemplazado con tu método de autenticación real)

$usuarioId = $_SESSION['username'];

// Obtener datos del usuario logueado
$userQuery = "SELECT UsuarioAdmin FROM usuarios WHERE IdUsuarios = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$isAdmin = isset($userData['UsuarioAdmin']) && $userData['UsuarioAdmin'] === 'S';

// Función para formatear el precio con separador de miles
function formatPrice($price) {
    return number_format($price, 2, '.', ',');
}

// Procesar el formulario
$clients = $articles = $orderDetails = [];
$finalPrice = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Búsqueda de clientes
    if (isset($_POST['client_name'])) {
        $clientName = $_POST['client_name'];
        $clientQuery = "SELECT clienteId, clienteNom, CliNomRazonSoc, CliNomComercial FROM cliente WHERE clienteNom LIKE ? OR CliNomRazonSoc LIKE ? OR CliNomComercial LIKE ?";
        $stmt = $conn->prepare($clientQuery);
        $likeName = "%$clientName%";
        $stmt->bind_param("sss", $likeName, $likeName, $likeName);
        $stmt->execute();
        $clientResult = $stmt->get_result();
        $clients = $clientResult->fetch_all(MYSQLI_ASSOC);
    }

    // Búsqueda de artículos
    if (isset($_POST['article_name'])) {
        $articleName = $_POST['article_name'];
        $articleQuery = "SELECT ArtConfalCod, ArtConFalNom FROM ArtConFal WHERE ArtConFalNom LIKE ?";
        $stmt = $conn->prepare($articleQuery);
        $likeName = "%$articleName%";
        $stmt->bind_param("s", $likeName);
        $stmt->execute();
        $articleResult = $stmt->get_result();
        $articles = $articleResult->fetch_all(MYSQLI_ASSOC);
    }

    // Calcular bonificación
    if (isset($_POST['calculate_bonificado'])) {
        $articleCode = $_POST['article_code'];
        $quantity = $_POST['quantity'];

        $today = date('Y-m-d');
        $bonificadoQuery = "SELECT * FROM bonificados WHERE FechaInicio <= ? AND FechaFin >= ?";
        $stmt = $conn->prepare($bonificadoQuery);
        $stmt->bind_param("ss", $today, $today);
        $stmt->execute();
        $bonificadoResult = $stmt->get_result();
        $bonificados = $bonificadoResult->fetch_all(MYSQLI_ASSOC);

        $bonifiedQuantity = 0;
        foreach ($bonificados as $bonificado) {
            // Verificar bonificación por cantidad
            $articleBonificadoQuery = "SELECT * FROM articulosbonificadosporcantidad WHERE ArticuloCodigo = ? AND BonificadoID = ?";
            $stmt = $conn->prepare($articleBonificadoQuery);
            $stmt->bind_param("si", $articleCode, $bonificado['BonificadoID']);
            $stmt->execute();
            $articleBonificadoResult = $stmt->get_result();

            if ($articleBonificadoResult->num_rows > 0) {
                $bonifiedData = $articleBonificadoResult->fetch_assoc();
                $bonifiedQuantity = $quantity * ($bonifiedData['CantidadBonificada'] / $bonifiedData['CantidadInicial']);
                break;
            }

            // Verificar bonificación por artículo
            $articleBonificadoQuery = "SELECT * FROM articulosbonificadosporarticulo WHERE ArticuloCodigo = ? AND BonificadoID = ?";
            $stmt->prepare($articleBonificadoQuery);
            $stmt->bind_param("si", $articleCode, $bonificado['BonificadoID']);
            $stmt->execute();
            $articleBonificadoResult = $stmt->get_result();

            if ($articleBonificadoResult->num_rows > 0) {
                $bonifiedArticle = $articleBonificadoResult->fetch_assoc();
                $articleBonificadoQuery = "SELECT * FROM ArtConFal WHERE ArtConfalCod = ?";
                $stmt = $conn->prepare($articleBonificadoQuery);
                $stmt->bind_param("s", $bonifiedArticle['ArticuloBonificadoCodigo']);
                $stmt->execute();
                $bonifiedArticleResult = $stmt->get_result();
                $bonifiedArticleData = $bonifiedArticleResult->fetch_assoc();
                $bonifiedQuantity = $quantity * ($bonifiedArticleData['CantidadBonificada'] / $bonifiedArticleData['CantidadInicial']);
                break;
            }
        }

        $articlePriceQuery = "SELECT PrecioListPre FROM ArtConFalListPre WHERE CodArticulo = ?";
        $stmt->prepare($articlePriceQuery);
        $stmt->bind_param("s", $articleCode);
        $stmt->execute();
        $articlePriceResult = $stmt->get_result();
        $articlePrice = $articlePriceResult->fetch_assoc();

        $finalPrice = formatPrice($articlePrice['PrecioListPre'] * $quantity);
    }

    // Añadir artículo al pedido
    if (isset($_POST['add_article'])) {
        $clientID = $_POST['client_id'];
        $articleCode = $_POST['article_code'];
        $quantity = $_POST['quantity'];

        // Insertar nuevo pedido
        $orderIDQuery = "INSERT INTO pedidos (fecha, cliente_id) VALUES (NOW(), ?)";
        $stmt->prepare($orderIDQuery);
        $stmt->bind_param("i", $clientID);
        $stmt->execute();
        $orderID = $conn->insert_id;

        // Insertar detalle del pedido
        $orderDetailQuery = "INSERT INTO pedidosdetalle (pedido_id, articulo_codigo, cantidad) VALUES (?, ?, ?)";
        $stmt->prepare($orderDetailQuery);
        $stmt->bind_param("isi", $orderID, $articleCode, $quantity);
        $stmt->execute();

        // Obtener detalles del pedido
        $orderDetailsQuery = "SELECT * FROM pedidosdetalle WHERE pedido_id = ?";
        $stmt->prepare($orderDetailsQuery);
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $orderDetailsResult = $stmt->get_result();
        $orderDetails = $orderDetailsResult->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Artículos</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; }
        .modal-body { max-height: 70vh; overflow-y: auto; }
        .btn-custom { background-color: #007bff; color: #fff; }
        .btn-custom:hover { background-color: #0056b3; }
        .table th, .table td { text-align: center; vertical-align: middle; }
        .suggestions { border: 1px solid #ccc; max-height: 150px; overflow-y: auto; }
        .suggestions div { padding: 10px; cursor: pointer; }
        .suggestions div:hover { background-color: #f0f0f0; }
    </style>
    <script>
        $(document).ready(function() {
            $('#client_name').on('input', function() {
                var clientName = $(this).val();
                if (clientName.length > 1) {
                    $.ajax({
                        url: 'get_clients.php',
                        method: 'POST',
                        data: { client_name: clientName },
                        success: function(data) {
                            var suggestions = JSON.parse(data);
                            var suggestionsHtml = '';
                            suggestions.forEach(function(suggestion) {
                                suggestionsHtml += '<div data-id="' + suggestion.clienteId + '">' + suggestion.clienteNom + '</div>';
                            });
                            $('#client_suggestions').html(suggestionsHtml).show();
                        }
                    });
                } else {
                    $('#client_suggestions').empty().hide();
                }
            });

            $('#article_name').on('input', function() {
                var articleName = $(this).val();
                if (articleName.length > 1) {
                    $.ajax({
                        url: 'get_articles.php',
                        method: 'POST',
                        data: { article_name: articleName },
                        success: function(data) {
                            var suggestions = JSON.parse(data);
                            var suggestionsHtml = '';
                            suggestions.forEach(function(suggestion) {
                                suggestionsHtml += '<div data-code="' + suggestion.ArtConfalCod + '">' + suggestion.ArtConFalNom + '</div>';
                            });
                            $('#article_suggestions').html(suggestionsHtml).show();
                        }
                    });
                } else {
                    $('#article_suggestions').empty().hide();
                }
            });

            $(document).on('click', '#client_suggestions div', function() {
                $('#client_name').val($(this).text());
                $('#client_id').val($(this).data('id'));
                $('#client_suggestions').empty().hide();
            });

            $(document).on('click', '#article_suggestions div', function() {
                $('#article_name').val($(this).text());
                $('#article_code').val($(this).data('code'));
                $('#article_suggestions').empty().hide();
            });
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2>Gestión de Pedidos</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="client_name">Buscar Cliente:</label>
                <input type="text" id="client_name" name="client_name" class="form-control" placeholder="Nombre del cliente">
                <div id="client_suggestions" class="suggestions"></div>
            </div>
            <input type="hidden" id="client_id" name="client_id">
            <div class="form-group">
                <label for="article_name">Buscar Artículo:</label>
                <input type="text" id="article_name" name="article_name" class="form-control" placeholder="Nombre del artículo">
                <div id="article_suggestions" class="suggestions"></div>
            </div>
            <input type="hidden" id="article_code" name="article_code">
            <div class="form-group">
                <label for="quantity">Cantidad:</label>
                <input type="number" id="quantity" name="quantity" class="form-control" min="1">
            </div>
            <button type="submit" name="calculate_bonificado" class="btn btn-custom">Calcular Bonificación</button>
            <button type="submit" name="add_article" class="btn btn-primary">Añadir Artículo</button>
        </form>

        <?php if ($finalPrice > 0): ?>
            <h4>Precio Final: <?php echo $finalPrice; ?></h4>
        <?php endif; ?>

        <h3>Detalles del Pedido</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderDetails as $detail): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['articulo_codigo']); ?></td>
                        <td><?php echo htmlspecialchars($detail['cantidad']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>



<?php
require_once "vistas/parteinferior.php"
?>