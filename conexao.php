//<?php
//$host = 'localhost';
//$dbname = 'loja_luz';
//$username = 'admin';
//$password = '953687';

//try {
    //$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//} catch (PDOException $e) {
    //die("Erro na conexão com o banco de dados: " . $e->getMessage());
//}
//?>



<?php
$host = "localhost";
$dbname = "loja_luz"; 
$username = "root";   
$password = "";       

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexão bem-sucedida!";
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>