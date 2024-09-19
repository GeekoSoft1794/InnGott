<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "InnGott";
$artConFalSec = isset($_GET['artconfalsec']) ? intval($_GET['artconfalsec']) : null;

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicializar variables vacías para la creación
$artConFalCod = '';
$artConFalNom = '';
$artConFalTip = '';
$artTipoIva = '';

// Si se está modificando, cargar los datos del artículo
if ($artConFalSec) {
    $sql = "SELECT * FROM ArtConFal WHERE ArtConFalSec = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $artConFalSec);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $artConFalCod = $row['ArtConFalCod'];
            $artConFalNom = $row['ArtConFalNom'];
            $artConFalTip = $row['ArtConFalTip'];
            $artTipoIva = $row['ArtTipoIva'];
        }
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta: " . $conn->error;
    }
} else {
    // Si es nuevo, buscar el siguiente valor de ArtConFalSec
    $sql = "SELECT MAX(ArtConFalSec) AS max_sec FROM ArtConFal";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $artConFalSec = $row['max_sec'] ? $row['max_sec'] + 1 : 1;
    } else {
        echo "Error en la consulta: " . $conn->error;
    }
}

// Guardar los datos cuando se presiona "Guardar"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artConFalCod = $_POST['artconfalcod'];
    $artConFalNom = $_POST['artconfalnom'];
    $artConFalTip = $_POST['artconfaltip'];
    $artTipoIva = $_POST['arttipoiva'];

    if (isset($_POST['artconfalsec']) && $_POST['artconfalsec']) {
        // Actualizar el registro si se está modificando
        $sql = "UPDATE ArtConFal SET ArtConFalCod=?, ArtConFalNom=?, ArtConFalTip=?, ArtTipoIva=? WHERE ArtConFalSec=?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssssi", $artConFalCod, $artConFalNom, $artConFalTip, $artTipoIva, $artConFalSec);
            if ($stmt->execute()) {
                echo "<script>
                        alert('Artículo actualizado exitosamente');
                        window.top.window.closeModal();
                      </script>";
            } else {
                echo "Error en la ejecución de la consulta: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    } else {
        // Insertar un nuevo registro si se está creando
        $sql = "INSERT INTO ArtConFal (ArtConFalSec, ArtConFalCod, ArtConFalNom, ArtConFalTip, ArtTipoIva) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isssi", $artConFalSec, $artConFalCod, $artConFalNom, $artConFalTip, $artTipoIva);
            if ($stmt->execute()) {
                echo "<script>
                        alert('Artículo guardado exitosamente');
                        window.top.window.closeModal();
                      </script>";
            } else {
                echo "Error en la ejecución de la consulta: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    }
    $conn->close();
}
?>

<div class="container-fluid">
    <h1 class="h3 text-center" style="color: #2c3e50; font-weight: bold;">Creación de Artículos</h1>

    <form method="POST" action="">
        <!-- Campo oculto para ArtConFalSec -->
        <input type="hidden" name="artconfalsec" value="<?php echo htmlspecialchars($artConFalSec); ?>">

        <div class="row mb-3">
            <div class="col-md-6 col-12">
                <label for="artconfalcod">Código</label>
                <input type="text" class="form-control form-control-sm" id="artconfalcod" name="artconfalcod" value="<?php echo htmlspecialchars($artConFalCod); ?>">
            </div>
            <div class="col-md-6 col-12">
                <label for="artconfalnom">Nombre</label>
                <input type="text" class="form-control form-control-sm" id="artconfalnom" name="artconfalnom" value="<?php echo htmlspecialchars($artConFalNom); ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 col-12">
                <label for="artconfaltip">Tipo de Artículo</label>
                <select class="form-control form-control-sm" id="artconfaltip" name="artconfaltip">
                    <option value="A" <?php echo ($artConFalTip == 'A') ? 'selected' : ''; ?>>Aseo</option>
                    <option value="C" <?php echo ($artConFalTip == 'C') ? 'selected' : ''; ?>>Cafetería</option>
                    <option value="P" <?php echo ($artConFalTip == 'P') ? 'selected' : ''; ?>>Papelería</option>
                </select>
            </div>
            <div class="col-md-6 col-12">
                <label for="arttipoiva">Tipo de IVA</label>
                <input type="number" class="form-control form-control-sm" id="arttipoiva" name="arttipoiva" value="<?php echo htmlspecialchars($artTipoIva); ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-12">
                <button type="submit" class="btn btn-success btn-sm">Guardar</button>
            </div>
            <div class="col-md-6 col-12">
                <a href="articulos.php" class="btn btn-danger btn-sm">Salir</a>
            </div>
        </div>
    </form>
</div>

<script>
function closeModal() {
    // Cerrar el modal, si se está usando Bootstrap, puedes utilizar este código para cerrar el modal
    if (window.top.window.$('#myModal').length) {
        window.top.window.$('#myModal').modal('hide');
    }
}
</script>
