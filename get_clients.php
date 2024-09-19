<?php
$host = 'localhost';
$db = 'InnGott';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_POST['client_name'])) {
    $clientName = $_POST['client_name'];
    $clientQuery = "SELECT clienteId, clienteNom, CliNomRazonSoc, CliNomComercial FROM cliente WHERE clienteNom LIKE ? OR CliNomRazonSoc LIKE ? OR CliNomComercial LIKE ?";
    $stmt = $conn->prepare($clientQuery);
    $likeName = "%$clientName%";
    $stmt->bind_param("sss", $likeName, $likeName, $likeName);
    $stmt->execute();
    $clientResult = $stmt->get_result();
    $clients = $clientResult->fetch_all(MYSQLI_ASSOC);
    echo json_encode($clients);
}
?>
