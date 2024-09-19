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

// Consulta SQL
$sql = "SELECT VendedorDoc, VendedorNom, VendedorEst, VendedorEmailPer,VendedorEsTat,VendedorEsCon,VendedorEsCom FROM empleado";
$result = $conn->query($sql);
?>


<div class="container-fluid">
    <h1 class="h3 text-center" style="color: #2c3e50; font-weight: bold;">EMPLEADOS</h1>
    
    <div class="row mb-3">
        <div class="col-md-4 col-12 mb-2">
            <a href="nuevo_empleado.php" class="btn btn-primary btn-sm">Nuevo</a> <!-- Botón más pequeño -->
        </div>
        <div class="col-md-8 col-12">
            <form class="form-inline" method="GET" action="">
                <div class="form-row">
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" id="documento" name="documento" placeholder="Documento" value="<?php echo isset($_GET['documento']) ? $_GET['documento'] : ''; ?>">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" id="nombre" name="nombre" placeholder="Nombre" value="<?php echo isset($_GET['nombre']) ? $_GET['nombre'] : ''; ?>">
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
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Documento</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Nombre</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Estado</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Email Personal</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Es Vendedor</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Es Comercial</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Es Conductor</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Obtener los valores de los filtros
                $documento = isset($_GET['documento']) ? $_GET['documento'] : '';
                $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';

                // Construir la consulta SQL con los filtros
                $query = "SELECT * FROM empleado WHERE 1=1";

                if (!empty($documento)) {
                    $query .= " AND VendedorDoc LIKE '%$documento%'";
                }

                if (!empty($nombre)) {
                    $query .= " AND VendedorNom LIKE '%$nombre%'";
                }

                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='color: #000000;'>" . $row["VendedorDoc"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["VendedorNom"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["VendedorEst"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["VendedorEmailPer"] . "</td>";
                        echo "<td style='color: #000000;'><input type='checkbox' " . ($row["VendedorEsTat"] === 'S' ? 'checked' : '') . " disabled></td>";
                        echo "<td style='color: #000000;'><input type='checkbox' " . ($row["VendedorEsCon"] === 'S' ? 'checked' : '') . " disabled></td>";
                        echo "<td style='color: #000000;'><input type='checkbox' " . ($row["VendedorEsCom"] === 'S' ? 'checked' : '') . " disabled></td>";
                        echo "<td style='color: #000000;'>";
                        echo "<a href='editar_empleado.php?id=" . $row["VendedorDoc"] . "' class='btn btn-warning btn-sm'>Modificar</a> ";
                        echo "<a href='eliminar_empleado.php?id=" . $row["VendedorDoc"] . "' class='btn btn-danger btn-sm'>Inactivar</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No se encontraron resultados</td></tr>"; 
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