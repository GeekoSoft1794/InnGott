<?php
require_once "vistas/partesuperior.php"
?>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "InnGott";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar el formulario para agregar un nuevo bonificado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        // Insertar en la tabla padre
        $nombre = $_POST['nombre'];
        $fechaInicio = $_POST['fechaInicio'];
        $fechaFin = $_POST['fechaFin'];

        // Obtener el siguiente ID
        $result = $conn->query("SELECT COALESCE(MAX(BonificadoID), 0) + 1 AS next_id FROM Bonificados");
        $nextId = $result->fetch_assoc()['next_id'];

        $sql = "INSERT INTO Bonificados (BonificadoID, Nombre, FechaInicio, FechaFin) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isss', $nextId, $nombre, $fechaInicio, $fechaFin);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] === 'assign') {
        $bonificadoID = $_POST['bonificadoID'];
        $tipoBonificacion = $_POST['tipoBonificacion'];
        $articuloID = $_POST['selectedArticuloID'];
        $cantidadInicial = $_POST['cantidadInicial'];
        $cantidadBonificada = $_POST['cantidadBonificada'];

        if ($tipoBonificacion === 'A') {
            $sql = "INSERT INTO articulosbonificadosporarticulo (BonificadoID, ArticuloID, CantidadInicial, CantidadBonificada) VALUES (?, ?, ?, ?)";
        } elseif ($tipoBonificacion === 'B') {
            $sql = "INSERT INTO articulosbonificadosporcantidad (BonificadoID, ArticuloID, CantidadInicial, CantidadBonificada) VALUES (?, ?, ?, ?)";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isii', $bonificadoID, $articuloID, $cantidadInicial, $cantidadBonificada);
        $stmt->execute();
        $stmt->close();
    }
}

// Consultar los bonificados existentes
$queryCantidad = "SELECT * FROM articulosbonificadosporcantidad";
$resultCantidad = $conn->query($queryCantidad);

$queryArticulo = "SELECT * FROM articulosbonificadosporarticulo";
$resultArticulo = $conn->query($queryArticulo);

// Buscar artículos
if (isset($_GET['query'])) {
    $search = $_GET['query'];
    $sql = "SELECT ArtConFalCod, ArtConFalNom FROM ArtConFal WHERE ArtConFalNom LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchParam = "%$search%";
    $stmt->bind_param('s', $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $articulos = [];
    while ($row = $result->fetch_assoc()) {
        $articulos[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($articulos);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonificados</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .autocomplete-items {
            border: 1px solid #ddd;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            position: absolute;
            background-color: #fff;
        }
        .autocomplete-item {
            padding: 10px;
            cursor: pointer;
        }
        .autocomplete-item:hover {
            background-color: #ddd;
        }
        .section {
            margin-bottom: 20px;
        }
        .form-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-section h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <h1 class="h3 text-center" style="color: #2c3e50; font-weight: bold;">Bonificados</h1>

    <!-- Datos del Bonificado (Tabla Padre) -->
    <div class="form-section">
        <h3>Datos del Bonificado</h3>
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="nombre">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="fechaInicio">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" value="<?php echo isset($fechaInicio) ? htmlspecialchars($fechaInicio) : ''; ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="fechaFin">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin" name="fechaFin" value="<?php echo isset($fechaFin) ? htmlspecialchars($fechaFin) : ''; ?>" required>
                </div>
            </div>
            <div class="form-row">
                <button type="submit" class="btn btn-success" name="action" value="create">Crear</button>
                <a href="bonificados.php" class="btn btn-danger ml-2">Salir</a>
            </div>
        </form>
    </div>

    <!-- Sección para campos adicionales (Tabla Hijas) -->
    <div class="form-section" id="additional-fields" style="display: none;">
        <h3>Detalles del Bonificado</h3>
        <form method="POST" action="">
            <input type="hidden" id="bonificadoID" name="bonificadoID" value="<?php echo isset($nextId) ? htmlspecialchars($nextId) : ''; ?>">
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="articuloID">Código del Artículo</label>
                    <input type="text" class="form-control" id="articuloID" name="articuloID" readonly required>
                    <input type="hidden" id="selectedArticuloID" name="selectedArticuloID">
                    <div id="autocomplete-container" class="autocomplete-items"></div>
                </div>
                <div class="form-group col-md-6" id="second-article-section" style="display: none;">
                    <label for="secondArticuloID">Código del Segundo Artículo</label>
                    <input type="text" class="form-control" id="secondArticuloID" name="secondArticuloID" readonly>
                    <input type="hidden" id="selectedSecondArticuloID" name="selectedSecondArticuloID">
                    <div id="autocomplete-second-container" class="autocomplete-items"></div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="tipoBonificacion">Tipo de Bonificación</label>
                    <select class="form-control" id="tipoBonificacion" name="tipoBonificacion" required>
                        <option value="">Seleccione...</option>
                        <option value="A">Por Artículo</option>
                        <option value="B">Por Cantidad</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="cantidadInicial">Cantidad Inicial</label>
                    <input type="number" class="form-control" id="cantidadInicial" name="cantidadInicial" step="1" min="0" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cantidadBonificada">Cantidad Bonificada</label>
                    <input type="number" class="form-control" id="cantidadBonificada" name="cantidadBonificada" step="1" min="0" required>
                </div>
            </div>
            <div class="form-row">
                <button type="submit" class="btn btn-success" name="action" value="assign">Asignar</button>
            </div>
        </form>
    </div>

    <!-- Tabla de Bonificados por Cantidad -->
    <div class="form-section">
        <h3>Bonificados por Cantidad</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID Bonificado</th>
                    <th>Artículo</th>
                    <th>Cantidad Inicial</th>
                    <th>Cantidad Bonificada</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultCantidad->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['BonificadoID']); ?></td>
                        <td><?php echo htmlspecialchars($row['ArticuloID']); ?></td>
                        <td><?php echo htmlspecialchars($row['CantidadInicial']); ?></td>
                        <td><?php echo htmlspecialchars($row['CantidadBonificada']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Tabla de Bonificados por Artículo -->
    <div class="form-section">
        <h3>Bonificados por Artículo</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID Bonificado</th>
                    <th>Artículo</th>
                    <th>Cantidad Inicial</th>
                    <th>Cantidad Bonificada</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultArticulo->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['BonificadoID']); ?></td>
                        <td><?php echo htmlspecialchars($row['ArticuloID']); ?></td>
                        <td><?php echo htmlspecialchars($row['CantidadInicial']); ?></td>
                        <td><?php echo htmlspecialchars($row['CantidadBonificada']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Funcionalidad de Autocompletar
    function setupAutocomplete(input, container) {
        let currentFocus;

        input.addEventListener("input", function() {
            let val = this.value;
            container.innerHTML = "";
            if (!val) return false;
            fetch("bonificados.php?query=" + val)
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {
                        const div = document.createElement("div");
                        div.classList.add("autocomplete-item");
                        div.innerHTML = item.ArtConFalNom;
                        div.addEventListener("click", function() {
                            input.value = item.ArtConFalNom;
                            document.getElementById(input.id + "ID").value = item.ArtConFalCod;
                            container.innerHTML = "";
                        });
                        container.appendChild(div);
                    });
                });
        });

        input.addEventListener("keydown", function(e) {
            let items = container.getElementsByClassName("autocomplete-item");
            if (e.keyCode === 40) { // Arrow down
                currentFocus++;
                addActive(items);
            } else if (e.keyCode === 38) { // Arrow up
                currentFocus--;
                addActive(items);
            } else if (e.keyCode === 13) { // Enter
                e.preventDefault();
                if (currentFocus > -1) {
                    if (items) items[currentFocus].click();
                }
            }
        });

        function addActive(items) {
            if (!items) return false;
            removeActive(items);
            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = items.length - 1;
            items[currentFocus].classList.add("autocomplete-active");
        }

        function removeActive(items) {
            for (let i = 0; i < items.length; i++) {
                items[i].classList.remove("autocomplete-active");
            }
        }

        document.addEventListener("click", function(e) {
            container.innerHTML = "";
        });
    }

    setupAutocomplete(document.getElementById("articuloID"), document.getElementById("autocomplete-container"));
    setupAutocomplete(document.getElementById("secondArticuloID"), document.getElementById("autocomplete-second-container"));

    // Mostrar u ocultar la sección de campos adicionales
    document.getElementById("tipoBonificacion").addEventListener("change", function() {
        const type = this.value;
        const secondArticleSection = document.getElementById("second-article-section");
        if (type === "A") {
            secondArticleSection.style.display = "block";
        } else {
            secondArticleSection.style.display = "none";
        }
    });
</script>
</body>
</html>


<?php $conn->close(); ?>


<?php
require_once "vistas/parteinferior.php"
?>