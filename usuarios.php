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
$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$documento = isset($_GET['documento']) ? $_GET['documento'] : '';

// Construir la consulta SQL con los filtros
$query = "
SELECT u.idusuarios, u.nomusuario, u.estusuario, e.VendedorNom, e.VendedorDoc
FROM usuarios u
LEFT JOIN empleado e ON u.idvendedor = e.idvendedor
WHERE 1=1;";

if (!empty($nombre)) {
    $query .= " AND u.nomusuario LIKE '%$nombre%'";
}

if (!empty($estado)) {
    $query .= " AND u.estusuario LIKE '%$estado%'";
}

if (!empty($documento)) {
    $query .= " AND e.VendedorDoc LIKE '%$documento%'";
}

$result = $conn->query($query);
?>

<div class="container-fluid">
    <h1 class="h3 text-center" style="color: #2c3e50; font-weight: bold;">USUARIOS</h1>

    <div class="row mb-3">
        <div class="col-md-4 col-12 mb-2">
            <a href="insertar_usuario.php" class="btn btn-primary btn-sm">Nuevo</a>
        </div>
        <div class="col-md-8 col-12">
            <form class="form-inline" method="GET" action="">
                <div class="form-row">
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" id="documento" name="documento" placeholder="Documento" value="<?php echo $documento; ?>">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" id="nombre" name="nombre" placeholder="Nombre Usuario" value="<?php echo $nombre; ?>">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" id="estado" name="estado" placeholder="Estado" value="<?php echo $estado; ?>">
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
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Id</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Documento Vendedor</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Nombre Vendedor</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Usuario</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Estado</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='color: #000000;'>" . $row["idusuarios"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["VendedorDoc"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["VendedorNom"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["nomusuario"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["estusuario"] . "</td>";
                        echo "<td style='color: #000000;'>";
                        echo "<a href='modificar_usuario.php?id=" . $row["idusuarios"] . "' class='btn btn-warning btn-sm'>Modificar</a> ";
                        echo "<a href='eliminar_usuario.php?id=" . $row["idusuarios"] . "' class='btn btn-danger btn-sm'>Inactivar</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No se encontraron resultados</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
?>


<?php
require_once "vistas/parteinferior.php"
?>