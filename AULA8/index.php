<?php
session_start();
require_once 'conexao.php';

// Terminar sessão (Logout)
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$erro_login = false;

// Processar o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // Validação estrita solicitada
    if ($email === 'joao@sesi.br' && $senha === '1234') {
        $_SESSION['usuario_logado'] = $email;
    } else {
        $erro_login = true;
    }
}

// Se o utilizador estiver autenticado, consulta a tabela de alunos
$alunos = [];
if (isset($_SESSION['usuario_logado'])) {
    try {
        $stmt = $pdo->query("SELECT id_aluno, nome, data_nascimento, telefone, data_cadastro FROM aluno ORDER BY id_aluno ASC");
        $alunos = $stmt->fetchAll();
    } catch (PDOException $e) {
        $erro_consulta = "Erro ao consultar a base de dados: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escola de Linguagem - Gestão de Alunos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php if (!isset($_SESSION['usuario_logado'])): ?>
        <!-- Ecrã de Login -->
        <div class="login-container">
            <div class="login-header">
                <h2>Acesso ao Sistema</h2>
                <p>Escola de Linguagem</p>
            </div>

            <?php if ($erro_login): ?>
                <div class="alert-error">
                    E-mail ou palavra-passe incorretos!
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" placeholder="joao@sesi.br" required>
                </div>

                <div class="form-group">
                    <label for="senha">Palavra-passe</label>
                    <input type="password" name="senha" id="senha" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>
        </div>

    <?php else: ?>
        <!-- Tabela de Alunos (Visível apenas após autenticação com sucesso) -->
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div>
                    <h2>Tabela de Alunos</h2>
                    <span class="user-email">Base de Dados: escola_linguagem</span>
                </div>
                <div class="user-info">
                    <span class="user-email">Utilizador: <strong><?= htmlspecialchars($_SESSION['usuario_logado']) ?></strong></span>
                    <a href="index.php?logout=1" class="btn btn-danger">Sair</a>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID Aluno</th>
                            <th>Nome</th>
                            <th>Data de Nascimento</th>
                            <th>Telefone</th>
                            <th>Data de Cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($alunos)): ?>
                            <?php foreach ($alunos as $aluno): ?>
                                <tr>
                                    <td><span class="badge-id">#<?= htmlspecialchars($aluno['id_aluno']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($aluno['nome']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($aluno['data_nascimento'])) ?></td>
                                    <td><?= htmlspecialchars($aluno['telefone'] ?? 'N/A') ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($aluno['data_cadastro'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Nenhum registo encontrado na tabela de alunos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>