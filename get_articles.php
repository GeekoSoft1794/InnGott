<?php
$host = 'localhost';
$db = 'InnGott';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_POST['article_name'])) {
    $articleName = $_POST['article_name'];
    $articleQuery = "SELECT ArtConfalCod, ArtConFalNom FROM ArtConFal WHERE ArtConFalNom LIKE ?";
    $stmt = $conn->prepare($articleQuery);
    $likeName = "%$articleName%";
    $stmt->bind_param("s", $likeName);
    $stmt->execute();
    $articleResult = $stmt->get_result();
    $articles = $articleResult->fetch_all(MYSQLI_ASSOC);
    echo json_encode($articles);
}
?>
