<?php
require_once "vistas/partesuperior.php"
?>

<?php
// Iniciar el almacenamiento en búfer de salida
ob_start();

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

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nomusuario = $_POST['nomusuario'] ?? '';
    $passusuario = $_POST['passusuario'] ?? '';
    $usuarioadmin = isset($_POST['usuarioadmin']) ? 'S' : 'N';
    $idvendedor = $_POST['idvendedor'] ?? '';
    $usupervercli = isset($_POST['usupervercli']) ? 'S' : 'N';
    $usuperverdespven = isset($_POST['usuperverdespven']) ? 'S' : 'N';
    $estusuario = 'A'; // Establecer el valor de estusuario

    // Obtener el valor máximo actual de la clave primaria para usuarios
    $query_max_id = "SELECT MAX(idusuarios) AS max_id FROM usuarios";
    $result_max_id = $conn->query($query_max_id);
    
    if ($result_max_id) {
        $row_max_id = $result_max_id->fetch_assoc();
        $new_id = $row_max_id['max_id'] + 1; // Incrementar el valor máximo actual
    } else {
        $new_id = 1; // En caso de que la tabla esté vacía
    }

    // Preparar la consulta de inserción
    $stmt = $conn->prepare("INSERT INTO usuarios (idusuarios, nomusuario, passusuario, usuarioadmin, idvendedor, usupervercli, usuperverdespven, estusuario, feccreusuario, usuariocreacion)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("issssssss", $new_id, $nomusuario, $passusuario, $usuarioadmin, $idvendedor, $usupervercli, $usuperverdespven, $estusuario, $_SESSION['username']);
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Obtener el ID del nuevo usuario
        $user_id = $new_id;

        // Obtener el valor máximo actual de la clave primaria para usuariospermiso
        $query_max_permiso_id = "SELECT MAX(idpermiso) AS max_id FROM usuariospermisos";
        $result_max_permiso_id = $conn->query($query_max_permiso_id);
        
        if ($result_max_permiso_id) {
            $row_max_permiso_id = $result_max_permiso_id->fetch_assoc();
            $new_permiso_id = $row_max_permiso_id['max_id'] + 1; // Incrementar el valor máximo actual
        } else {
            $new_permiso_id = 1; // En caso de que la tabla esté vacía
        }

        // Consultar pantallassistema y crear registros en usuariospermiso
        $query_pantallas = "SELECT codigopantalla, nombrepantalla FROM pantallassistema";
        $result_pantallas = $conn->query($query_pantallas);

        if ($result_pantallas) {
            while ($pantalla = $result_pantallas->fetch_assoc()) {
                // Preparar la consulta de inserción en usuariospermiso
                $stmt_permiso = $conn->prepare("INSERT INTO usuariospermisos (idpermiso, idusuarios, pangeedesc, pangeenom, percrear, pereliminar, permodificar, pervisualizar)
                                                VALUES (?, ?, ?, ?, 'N', 'N', 'N', 'N')");
                $stmt_permiso->bind_param("iiss", $new_permiso_id, $user_id, $pantalla['codigopantalla'], $pantalla['nombrepantalla']);
                
                // Ejecutar la consulta
                if (!$stmt_permiso->execute()) {
                    echo "Error al insertar permisos: " . $stmt_permiso->error;
                }
                
                // Incrementar el ID de permiso
                $new_permiso_id++;
            }
        } else {
            echo "Error al consultar pantallassistema: " . $conn->error;
        }

        // Redirigir a la lista de usuarios
        header("Location: usuarios.php");
        exit;
    } else {
        echo "Error al insertar el usuario: " . $stmt->error;
    }

    // Cerrar la declaración y la conexión
    $stmt->close();
    $conn->close();
}

// Obtener los vendedores disponibles que no tienen usuario asignado
$query = "SELECT idvendedor, VendedorNom 
          FROM empleado 
          WHERE vendedorest = 'A' 
          AND idvendedor NOT IN (SELECT DISTINCT idvendedor FROM usuarios WHERE idvendedor IS NOT NULL)";
$vendedores = $conn->query($query);

// Finalizar el almacenamiento en búfer de salida
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group select {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            width: 100%;
        }

        .form-group input[type="checkbox"] {
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .form-actions button,
        .form-actions a {
            padding: 10px 20px;
            font-size: 14px;
            text-align: center;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-actions button:hover,
        .form-actions a:hover {
            background-color: #0056b3;
        }

        .form-actions a {
            background-color: #dc3545;
        }

        .form-actions a:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Nuevo Usuario</h1>
        </header>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nomusuario">Nombre de Usuario:</label>
                <input type="text" id="nomusuario" name="nomusuario" required>
            </div>
            <div class="form-group">
                <label for="passusuario">Contraseña:</label>
                <input type="password" id="passusuario" name="passusuario" required>
            </div>
            <div class="form-group">
                <label for="usuarioadmin">Administrador:</label>
                <input type="checkbox" id="usuarioadmin" name="usuarioadmin">
            </div>
            <div class="form-group">
                <label for="idvendedor">Vendedor:</label>
                <select id="idvendedor" name="idvendedor">
                    <option value="">Seleccionar Vendedor</option>
                    <?php while ($vendedor = $vendedores->fetch_assoc()) : ?>
                        <option value="<?php echo htmlspecialchars($vendedor['idvendedor']); ?>">
                            <?php echo htmlspecialchars($vendedor['VendedorNom']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="usupervercli">Ver Clientes:</label>
                <input type="checkbox" id="usupervercli" name="usupervercli">
            </div>
            <div class="form-group">
                <label for="usuperverdespven">Ver Despachos:</label>
                <input type="checkbox" id="usuperverdespven" name="usuperverdespven">
            </div>
            <div class="form-actions">
                <button type="submit">Crear</button>
                <a href="usuarios.php">Salir</a>
            </div>
        </form>
    </div>
</body>
</html>

<?php
require_once "vistas/parteinferior.php"
?>