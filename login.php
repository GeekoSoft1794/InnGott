<?php
// Conectar a la base de datos
session_start();
$servername = "localhost";
$username_db = "root"; // Cambia esto si tu usuario de base de datos es diferente
$password_db = ""; // Cambia esto si tu contraseña de base de datos es diferente
$dbname = "InnGott"; // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar el formulario si se ha enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Preparar y ejecutar la consulta con los campos específicos
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE NomUsuario = ? AND PassUsuario = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si el usuario existe
    if ($result->num_rows > 0) {
        // Iniciar sesión y redirigir a index.php
        session_start();
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit(); // Asegúrate de salir después de redirigir
    } else {
        // Usuario no encontrado, mostrar mensaje de error
        echo "<p class='alert alert-danger text-center'>Usuario o contraseña incorrectos</p>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-header img {
            max-width: 100%;
            border-radius: 8px;
        }
        .login-header h1 {
            font-size: 2rem;
            color: #343a40;
            margin-top: 15px;
        }
        .form-control {
            border-radius: 4px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 4px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004b99;
        }
        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }
        .forgot-password a {
            color: #007bff;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="img/DCINNGOTT.png" alt="Logo" />
            <h1>Iniciar Sesión</h1>
        </div>
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" class="form-control" id="username" name=username placeholder="" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" class="form-control" id="password" name=password placeholder="" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
