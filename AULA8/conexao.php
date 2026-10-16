<?php
// Configuração da ligação à base de dados MySQL
$host    = 'localhost';
$dbname  = 'escola_linguagem'; // Nome da base de dados
$usuario = 'root';             // Utilizador do MySQL
$senha   = '12345678';                 // Palavra-passe do MySQL (deixe vazio se utilizar a configuração padrão do XAMPP)

try {
    // Conexão segura utilizando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na ligação à base de dados: " . $e->getMessage());
}
?>