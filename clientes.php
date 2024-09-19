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
$clienteNumDoc = isset($_GET['clienteNumDoc']) ? $_GET['clienteNumDoc'] : '';
$clienteNombre = isset($_GET['clienteNombre']) ? $_GET['clienteNombre'] : '';

// Construir la consulta SQL con los filtros
$query = "SELECT * FROM cliente WHERE 1=1";

if (!empty($clienteNumDoc)) {
    $query .= " AND ClienteNumDoc LIKE '%$clienteNumDoc%'";
}

if (!empty($clienteNombre)) {
    $query .= " AND (ClienteNom LIKE '%$clienteNombre%' OR CliNomRazonSoc LIKE '%$clienteNombre%' OR CliNomComercial LIKE '%$clienteNombre%')";
}

$result = $conn->query($query);
?>

<div class="container-fluid">
    <h1 class="h3 text-center" style="color: #2c3e50; font-weight: bold;">CLIENTES</h1>
    
    <div class="row mb-3">
        <div class="col-md-4 col-12 mb-2">
            <a href="nuevo_cliente.php" class="btn btn-primary btn-sm">Nuevo</a> <!-- Botón más pequeño -->
        </div>
        <div class="col-md-8 col-12">
            <form class="form-inline" method="GET" action="">
                <div class="form-row">
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" id="clienteNumDoc" name="clienteNumDoc" placeholder="Número Documento" value="<?php echo isset($_GET['clienteNumDoc']) ? $_GET['clienteNumDoc'] : ''; ?>">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" id="clienteNombre" name="clienteNombre" placeholder="Nombre/Razón Social/Nombre Comercial" value="<?php echo isset($_GET['clienteNombre']) ? $_GET['clienteNombre'] : ''; ?>">
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
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Número Documento</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Nombre</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Razón Social</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Nombre Comercial</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Email</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Teléfono 1</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Teléfono 2</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='color: #000000;'>" . $row["ClienteNumDoc"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["clienteNom"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["CliNomRazonSoc"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["CliNomComercial"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["ClienteEmail"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["ClienteTel1"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["ClienteTel2"] . "</td>";
                        echo "<td style='color: #000000;'>";
                        echo "<a href='modificar_cliente.php?id=" . $row["clienteId"] . "' class='btn btn-warning btn-sm'>Modificar</a> ";
                        echo "<a href='inactivar_cliente.php?id=" . $row["clienteId"] . "' class='btn btn-danger btn-sm'>Inactivar</a>";
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