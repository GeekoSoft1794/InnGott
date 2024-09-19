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

// Obtener filtros si existen
$documento_cliente = isset($_GET['documento_cliente']) ? $_GET['documento_cliente'] : '';
$nombre_cliente = isset($_GET['nombre_cliente']) ? $_GET['nombre_cliente'] : '';
$vendedor = isset($_GET['vendedor']) ? $_GET['vendedor'] : '';
$numero_pedido = isset($_GET['numero_pedido']) ? $_GET['numero_pedido'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Consulta SQL principal
$sql = "
    SELECT 
        p.pedidoId,
        p.PedidosNum,
        c.ClienteNumDoc,
        c.ClienteNom,
        e.VendedorNom,
        p.PedidoEnviado,
        SUM(pd.detprecio * pd.detCantidad) AS Total
    FROM 
        pedidos p
    JOIN 
        cliente c ON p.clienteId = c.clienteId
    JOIN 
        empleado e ON p.IdVendedor = e.IdVendedor
    JOIN 
        pedidosdetalle pd ON p.pedidoId = pd.pedidoId
    WHERE 1=1
";

// Aplicar filtros
if (!empty($documento_cliente)) {
    $sql .= " AND c.ClienteNumDoc LIKE '%$documento_cliente%'";
}

if (!empty($nombre_cliente)) {
    $sql .= " AND c.ClienteNom LIKE '%$nombre_cliente%'";
}

if (!empty($vendedor)) {
    $sql .= " AND e.VendedorNom LIKE '%$vendedor%'";
}

if (!empty($numero_pedido)) {
    $sql .= " AND p.PedidosNum LIKE '%$numero_pedido%'";
}

if (!empty($estado)) {
    $sql .= " AND p.EstPedido = '$estado'";
}

// Agrupar resultados y ejecutar la consulta
$sql .= " GROUP BY p.pedidoId";
$result = $conn->query($sql);
?>

<div class="container-fluid">
    <h1 class="h3 text-center" style="color: #2c3e50; font-weight: bold;">PEDIDOS</h1>
    
    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-md-4 col-12 mb-2">
            <!-- Botón Nuevo Pedido -->
            <a href="nuevo_pedido.php" class="btn btn-primary btn-sm">Nuevo</a>
        </div>
        <div class="col-md-8 col-12">
            <form class="form-inline" method="GET" action="">
                <div class="form-row">
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" name="documento_cliente" placeholder="Documento Cliente" value="<?php echo $documento_cliente; ?>">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" name="nombre_cliente" placeholder="Nombre Cliente" value="<?php echo $nombre_cliente; ?>">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" name="vendedor" placeholder="Vendedor" value="<?php echo $vendedor; ?>">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" name="numero_pedido" placeholder="Número Pedido" value="<?php echo $numero_pedido; ?>">
                    </div>
                    <div class="col">
                        <select name="estado" class="form-control form-control-sm mb-2 mr-sm-2">
                            <option value="">Estado</option>
                            <option value="A" <?php if ($estado == 'A') echo 'selected'; ?>>Activo</option>
                            <option value="I" <?php if ($estado == 'I') echo 'selected'; ?>>Anulado</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary btn-sm mb-2">Buscar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Pedidos -->
    <div class="table-responsive">
        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Número Pedido</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Documento Cliente</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Nombre Cliente</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Vendedor</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Enviado</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Total</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["PedidosNum"] . "</td>";
                        echo "<td>" . $row["ClienteNumDoc"] . "</td>";
                        echo "<td>" . $row["ClienteNom"] . "</td>";
                        echo "<td>" . $row["VendedorNom"] . "</td>";
                        echo "<td>" . ($row["PedidoEnviado"] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . number_format($row["Total"], 2) . "</td>";
                        echo "<td>";
                        echo "<a href='modificar_pedido.php?id=" . $row["pedidoId"] . "' class='btn btn-warning btn-sm'>Modificar</a> ";
                        echo "<a href='eliminar_pedido.php?id=" . $row["pedidoId"] . "' class='btn btn-danger btn-sm'>Eliminar</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No se encontraron resultados</td></tr>"; 
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- Botón para descargar el consolidado en Excel -->
    <div class="text-right">
        <a href="descargar_consolidado.php?fecha_inicio=<?php echo isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : ''; ?>&fecha_fin=<?php echo isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : ''; ?>" class="btn btn-success btn-sm">Descargar Consolidado</a>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>

<?php
require_once "vistas/parteinferior.php"
?>