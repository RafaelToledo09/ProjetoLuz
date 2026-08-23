<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: ../login.php"); 
    exit;
}
?>



<?php
header('Content-Type: application/json; charset=utf-8');

$nome_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

try {
    
    $pdo = new PDO('mysql:host=localhost;dbname=loja_luz;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $sql = "CALL sp_buscar_vendas_dashboard(:nome, :limite, :offset)";
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':nome', $nome_cliente);
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    echo json_encode($resultados);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erro" => "Falha no banco de dados: " . $e->getMessage()]);
}
?>