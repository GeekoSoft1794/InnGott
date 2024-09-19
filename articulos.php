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
$sql = "SELECT ArtConFalSec, ArtConFalCod, ArtConFalNom FROM ArtConFal";
$result = $conn->query($sql);
?>

<div class="container-fluid">
    <h1 class="h3 text-center" style="color: #2c3e50; font-weight: bold;">ARTÍCULOS</h1>
    
    <div class="row mb-3">
        <div class="col-md-4 col-12 mb-2">
            <!-- Botón Nuevo que abre el modal para crear un nuevo artículo -->
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#articuloModal" data-sec="">Nuevo</button>
        </div>
        <div class="col-md-8 col-12">
            <form class="form-inline" method="GET" action="">
                <div class="form-row">
                    <div class="col">
                        <input type="text" class="form-control form-control-sm mb-2 mr-sm-2" id="codigo" name="codigo" placeholder="Código" value="<?php echo isset($_GET['codigo']) ? $_GET['codigo'] : ''; ?>">
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
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Código</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Nombre</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Obtener los valores de los filtros
                $codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
                $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';

                // Construir la consulta SQL con los filtros
                $query = "SELECT * FROM ArtConFal WHERE 1=1";

                if (!empty($codigo)) {
                    $query .= " AND ArtConFalCod LIKE '%$codigo%'";
                }

                if (!empty($nombre)) {
                    $query .= " AND ArtConFalNom LIKE '%$nombre%'";
                }

                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='color: #000000;'>" . $row["ArtConFalCod"] . "</td>";
                        echo "<td style='color: #000000;'>" . $row["ArtConFalNom"] . "</td>";
                        echo "<td style='color: #000000;'>";
                        echo "<button type='button' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#articuloModal' data-sec='" . $row["ArtConFalSec"] . "'>Modificar</button> ";
                        echo "<a href='eliminar_articulo.php?id=" . $row["ArtConFalSec"] . "' class='btn btn-danger btn-sm'>Inactivar</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No se encontraron resultados</td></tr>"; 
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="articuloModal" tabindex="-1" role="dialog" aria-labelledby="articuloModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="articuloModalLabel">Creación de Artículos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Aquí se cargará el contenido del formulario -->
        <div id="modal-body-content"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Cuando se abre el modal de nuevo o modificar
    $('#articuloModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botón que abrió el modal
        var artConFalSec = button.data('sec'); // Extraer la información de ArtConFalSec

        // Preparar la URL dependiendo si es nuevo o modificar
        var url = 'articuloscrud.php';
        if (artConFalSec) {
            url += '?artconfalsec=' + artConFalSec;
        }

        // Cargar el contenido del formulario en el modal
        $('#modal-body-content').load(url);
    });
});
</script>
</body>
</html>

<?php
$conn->close();
?>

<?php
require_once "vistas/parteinferior.php"
?>