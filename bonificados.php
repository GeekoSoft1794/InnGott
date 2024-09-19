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

// Obtener los valores de los filtros
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Construir la consulta SQL con los filtros
$query = "SELECT * FROM Bonificados WHERE 1=1";
$params = [];
$types = '';

if (!empty($fechaInicio)) {
    $query .= " AND FechaInicio >= ?";
    $params[] = $fechaInicio;
    $types .= 's'; // String type
}

if (!empty($fechaFin)) {
    $query .= " AND FechaFin <= ?";
    $params[] = $fechaFin;
    $types .= 's'; // String type
}

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($query);

if ($types) {
    // Bind parameters only if types are not empty
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">
    <h1 class="h3 text-center" style="color: #2c3e50; font-weight: bold;">BONIFICADOS</h1>
    
    <div class="row mb-3">
        <div class="col-md-4 col-12 mb-2">
            <a href="bonificadoscrud.php" class="btn btn-primary btn-sm">Nuevo</a> <!-- Botón más pequeño -->
        </div>
        <div class="col-md-8 col-12">
            <form class="form-inline" method="GET" action="">
                <div class="form-row">
                    <div class="col">
                        <input type="date" class="form-control form-control-sm mb-2 mr-sm-2" id="fecha_inicio" name="fecha_inicio" placeholder="Fecha Inicio" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                    </div>
                    <div class="col">
                        <input type="date" class="form-control form-control-sm mb-2 mr-sm-2" id="fecha_fin" name="fecha_fin" placeholder="Fecha Fin" value="<?php echo htmlspecialchars($fechaFin); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary btn-sm mb-2">Buscar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" id="dataTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">ID Bonificado</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Nombre</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Fecha Inicio</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Fecha Fin</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='color: #000000;'>" . htmlspecialchars($row["BonificadoID"]) . "</td>";
                        echo "<td style='color: #000000;'>" . htmlspecialchars($row["Nombre"]) . "</td>";
                        echo "<td style='color: #000000;'>" . htmlspecialchars($row["FechaInicio"]) . "</td>";
                        echo "<td style='color: #000000;'>" . htmlspecialchars($row["FechaFin"]) . "</td>";
                        echo "<td style='color: #000000;'>";
                        echo "<a href='editar_bonificado.php?id=" . urlencode($row["BonificadoID"]) . "' class='btn btn-warning btn-sm'>Modificar</a> ";
                        echo "<a href='eliminar_bonificado.php?id=" . urlencode($row["BonificadoID"]) . "' class='btn btn-danger btn-sm'>Eliminar</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No se encontraron resultados</td></tr>"; 
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
?>



<?php
require_once "vistas/parteinferior.php"
?>