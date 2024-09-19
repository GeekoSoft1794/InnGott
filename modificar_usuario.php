<?php
require_once "vistas/partesuperior.php"
?>

<?php
// Conexión a la base de datos
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

// Inicializar variables con valores predeterminados
$idusuario = '';
$nomusuario = '';
$passusuario = '';
$usuarioadmin = 'N';
$idvendedor = '';
$usupervercli = 'N';
$usuperverdespven = 'N';
$estusuario = 'A';

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_idusuarios']) && $_POST['form_idusuarios'] === 'guardar_usuario') {
    // Obtener los datos del formulario
    $idusuario = $_POST['idusuario'] ?? '';
    $nomusuario = $_POST['nomusuario'] ?? '';
    $passusuario = $_POST['passusuario'] ?? '';
    $usuarioadmin = isset($_POST['usuarioadmin']) ? 'S' : 'N';
    $usupervercli = isset($_POST['usupervercli']) ? 'S' : 'N';
    $usuperverdespven = isset($_POST['usuperverdespven']) ? 'S' : 'N';
    $estusuario = $_POST['estusuario'] ?? 'A'; // Valor por defecto
    
    // Actualizar la información del usuario
    $stmt = $conn->prepare("UPDATE usuarios SET nomusuario=?, passusuario=?, usuarioadmin=?, usupervercli=?, usuperverdespven=?, estusuario=? WHERE idusuarios=?");
    $stmt->bind_param("sssssss", $nomusuario, $passusuario, $usuarioadmin, $usupervercli, $usuperverdespven, $estusuario, $idusuario);    
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Redirigir a la lista de usuarios
        header("Location: usuarios.php");
        exit;
    } else {
        echo "Error al actualizar el usuario: " . $stmt->error;
    }

    // Cerrar la declaración
    $stmt->close();
}

// Obtener el ID del usuario desde la URL
$idusuario = $_GET['id'] ?? '';

// Verificar que se haya proporcionado un ID
if (empty($idusuario)) {
    echo "ID de usuario no proporcionado.";
    exit;
}

// Consultar la base de datos para obtener la información del usuario
$query = "SELECT * FROM usuarios WHERE idusuarios=?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("i", $idusuario);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se obtuvieron resultados
if ($user_data = $result->fetch_assoc()) {
    //para imprimir respuesta

    // var_dump($user_data);
    // exit();
    $nomusuario = $user_data['NomUsuario'];
    $passusuario = $user_data['PassUsuario'];
    $usuarioadmin = $user_data['UsuarioAdmin'];
    $idvendedor = $user_data['IdVendedor'];
    $usupervercli = $user_data['UsuPerVerCli'];
    $usuperverdespven = $user_data['UsuPerVerDespVen'];
    $estusuario = $user_data['EstUsuario'];
} else {
    echo "No se encontró el usuario con ID $idusuario.";
    exit;
}

// Manejo del formulario de permisos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['permisos']) && isset($_POST['form_idpermisos']) && $_POST['form_idpermisos'] === 'guardar_permisos') {

    $permisos = $_POST['permisos'] ?? [];

    // Verificar si $permisos es realmente un array
    if (is_array($permisos)) {
        foreach ($permisos as $pantallaID => $permisosData) {
            $crear = isset($permisosData['crear']) ? 'S' : 'N';
            $eliminar = isset($permisosData['eliminar']) ? 'S' : 'N';
            $modificar = isset($permisosData['modificar']) ? 'S' : 'N';
            $visualizar = isset($permisosData['visualizar']) ? 'S' : 'N';

            // Actualizar la información de permisos
            $stmt = $conn->prepare("UPDATE usuariospermisos SET PerCrear=?, PerEliminar=?, PerModificar=?, PerVisualizar=? WHERE IdUsuarios=? AND PanGeeDesc=?");
            $stmt->bind_param("ssssss", $crear, $eliminar, $modificar, $visualizar, $idusuario, $pantallaID);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        echo "Error: los permisos no son válidos.";
    }
}

// Consultar vendedores disponibles
// $query_vendedores = "SELECT IdVendedor, VendedorNom FROM empleado WHERE vendedorest = 'A' AND IdVendedor NOT IN (SELECT IdVendedor FROM usuarios WHERE idusuarios = ?)";
$query_vendedores = "SELECT IdVendedor, VendedorNom FROM empleado WHERE vendedorest = 'A' AND IdVendedor = ?";
$stmt_vendedores = $conn->prepare($query_vendedores);
$stmt_vendedores->bind_param("i", $idvendedor);
$stmt_vendedores->execute();
$vendedores = $stmt_vendedores->get_result();

// Consultar permisos del usuario
$query_permisos = "SELECT * FROM usuariospermisos WHERE idusuarios = ?";
$stmt_permisos = $conn->prepare($query_permisos);
$stmt_permisos->bind_param("i", $idusuario);
$stmt_permisos->execute();
$result_permisos = $stmt_permisos->get_result();

// Cerrar todas las declaraciones y la conexión
//$stmt->close();
$stmt_vendedores->close();
$stmt_permisos->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <style>
        /* Estilos */
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 80%;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 1em;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5em;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.5em;
            box-sizing: border-box;
        }
        .form-actions {
            margin-top: 1em;
        }
        .form-actions button, .form-actions a {
            padding: 0.5em 1em;
            text-decoration: none;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-actions button {
            background-color: #28a745;
        }
        .form-actions a {
            background-color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2em;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 0.5em;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Modificar Usuario</h1>
        </header>
        <form id="guardar_usuario" method="POST" action="">
            <input type="hidden" name="form_idusuarios" value="guardar_usuario">
            <input type="hidden" name="idusuario" value="<?php echo htmlspecialchars($idusuario); ?>">
            <div class="form-group">
                <label for="nomusuario">Nombre de Usuario:</label>
                <input type="text" id="nomusuario" name="nomusuario" value="<?php echo htmlspecialchars($nomusuario); ?>" required>
            </div>
            <div class="form-group">
                <label for="passusuario">Contraseña:</label>
                <input type="password" id="passusuario" name="passusuario" value="<?php echo htmlspecialchars($passusuario); ?>" required>
            </div>
            <div class="form-group">
                <label for="idvendedor">Vendedor:</label>
                <select id="idvendedor" name="idvendedor" disabled>
                    <?php while ($vendedor = $vendedores->fetch_assoc()) : ?>
                        <option value="<?php echo htmlspecialchars($vendedor['IdVendedor']); ?>"
                            <?php echo $vendedor['IdVendedor'] == $idvendedor ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($vendedor['VendedorNom']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="usuarioadmin">Administrador:</label>
                <input type="checkbox" id="usuarioadmin" name="usuarioadmin" <?php echo $usuarioadmin === 'S' ? 'checked' : ''; ?>>
            </div>
            <div class="form-group">
                <label for="usupervercli">Ver Clientes:</label>
                <input type="checkbox" id="usupervercli" name="usupervercli" <?php echo $usupervercli === 'S' ? 'checked' : ''; ?>>
            </div>
            <div class="form-group">
                <label for="usuperverdespven">Ver Despachos y Ventas:</label>
                <input type="checkbox" id="usuperverdespven" name="usuperverdespven" <?php echo $usuperverdespven === 'S' ? 'checked' : ''; ?>>
            </div>
            <div class="form-actions">
                <button type="submit">Guardar</button>
                <a href="usuarios.php">Cancelar</a>
            </div>
        </form>

        <h2>Permisos</h2>
        <form id="guardar_permisos" method="POST" action="">
            <input type="hidden" name="form_idpermisos" value="guardar_permisos">
            <input type="hidden" name="idusuario" value="<?php echo htmlspecialchars($idusuario); ?>">
            <table>
                <thead>
                    <tr>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Código Pantalla</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Nombre Pantalla</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Crear</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Eliminar</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Modificar</th>
                    <th style="background-color: #2c3e50; color: white; font-weight: bold;">Visualizar</th>
                    </tr>
                </thead>
                <tbody>
            <?php while ($permiso = $result_permisos->fetch_assoc()) : ?>
                <tr>
                    <td ><?php echo htmlspecialchars($permiso['PanGeeDesc']); ?></td>
                    <td><?php echo htmlspecialchars($permiso['PanGeeNom']); ?></td>
                    <td><input type="checkbox" name="permisos[<?php echo htmlspecialchars($permiso['PanGeeDesc']); ?>][crear]" <?php echo $permiso['PerCrear'] === 'S' ? 'checked' : ''; ?>></td>
                    <td><input type="checkbox" name="permisos[<?php echo htmlspecialchars($permiso['PanGeeDesc']); ?>][eliminar]" <?php echo $permiso['PerEliminar'] === 'S' ? 'checked' : ''; ?>></td>
                    <td><input type="checkbox" name="permisos[<?php echo htmlspecialchars($permiso['PanGeeDesc']); ?>][modificar]" <?php echo $permiso['PerModificar'] === 'S' ? 'checked' : ''; ?>></td>
                    <td><input type="checkbox" name="permisos[<?php echo htmlspecialchars($permiso['PanGeeDesc']); ?>][visualizar]" <?php echo $permiso['PerVisualizar'] === 'S' ? 'checked' : ''; ?>></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <div class="form-actions">
        <button type="submit" name="guardar_permisos">Guardar Permisos</button>
    </div>
</form>
    </div>
</body>
</html>

<?php
require_once "vistas/parteinferior.php"
?>

<script>

    setTimeout(() => {

        var idv='<?php echo $idvendedor ?>';
        console.log(idv);
          $("#idvendedor").val(idv).trigger('change');
        
    }, 1000);
</script>