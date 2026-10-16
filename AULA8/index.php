<?php
session_start();
require_once 'conexao.php'; // Usa a conexão PDO configurada[cite: 2]
 
// Terminar sessão (Logout)
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
 
$erro_login = false;
$msg_erro = '';
 
// 1. Processar Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
 
    if ($email === 'Murilo@sesi.br' && $senha === '1234') {
        $_SESSION['usuario_logado'] = $email;
    } else {
        $erro_login = true;
    }
}
 
// Ações restritas para usuários logados
if (isset($_SESSION['usuario_logado'])) {
 
    // 2. EXCLUIR ALUNO (DELETE)
    if (isset($_GET['deletar'])) {
        $id_deletar = (int)$_GET['deletar'];
        try {
            $stmt = $pdo->prepare("DELETE FROM aluno WHERE id_aluno = :id");
            $stmt->execute([':id' => $id_deletar]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $msg_erro = "Não é possível excluir o aluno pois ele possui vínculo na tabela de matrículas.";
        }
    }
 
    // 3. ADICIONAR OU EDITAR ALUNO (INSERT / UPDATE)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_aluno'])) {
        $id_aluno = $_POST['id_aluno'] ?? '';
        $nome = trim($_POST['nome'] ?? '');
        $data_nascimento = $_POST['data_nascimento'] ?? '';
        $telefone = trim($_POST['telefone'] ?? '');
 
        if (!empty($nome) && !empty($data_nascimento)) {
            if (!empty($id_aluno)) {
                // UPDATE
                $stmt = $pdo->prepare("UPDATE aluno SET nome = :nome, data_nascimento = :data_nascimento, telefone = :telefone WHERE id_aluno = :id");
                $stmt->execute([
                    ':nome' => $nome,
                    ':data_nascimento' => $data_nascimento,
                    ':telefone' => $telefone,
                    ':id' => $id_aluno
                ]);
            } else {
                // INSERT
                $stmt = $pdo->prepare("INSERT INTO aluno (nome, data_nascimento, telefone) VALUES (:nome, :data_nascimento, :telefone)");
                $stmt->execute([
                    ':nome' => $nome,
                    ':data_nascimento' => $data_nascimento,
                    ':telefone' => $telefone
                ]);
            }
            header('Location: index.php');
            exit;
        }
    }
 
    // 4. CARREGAR DADOS PARA EDIÇÃO (GET)[cite: 1]
    $aluno_edicao = null;
    if (isset($_GET['editar'])) {
        $id_editar = (int)$_GET['editar'];
        $stmt = $pdo->prepare("SELECT * FROM aluno WHERE id_aluno = :id");
        $stmt->execute([':id' => $id_editar]);
        $aluno_edicao = $stmt->fetch();
    }
 
    // 5. CONSULTAR TODOS OS ALUNOS (SELECT)[cite: 1]
    try {
        $stmt = $pdo->query("SELECT id_aluno, nome, data_nascimento, telefone, data_cadastro FROM aluno ORDER BY id_aluno ASC");
        $alunos = $stmt->fetchAll();
    } catch (PDOException $e) {
        $msg_erro = "Erro ao consultar banco de dados: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escola de Linguagem - Gestão de Alunos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
 
    <?php if (!isset($_SESSION['usuario_logado'])): ?>
        <!-- Tela de Login[cite: 3] -->
        <div class="login-container">
            <div class="login-header">
                <h2>Acesso ao Sistema</h2>
                <p>Escola de Linguagem</p>
            </div>
 
            <?php if ($erro_login): ?>
                <div class="alert-error">
                    E-mail ou senha incorretos!
                </div>
            <?php endif; ?>
 
            <form method="POST" action="index.php">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" placeholder="Murilo@sesi.br" required>
                </div>
 
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" name="senha" id="senha" placeholder="••••••••" required>
                </div>
 
                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>
        </div>
 
    <?php else: ?>
        <!-- Painel Principal de Alunos[cite: 3] -->
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div>
                    <h2>Gestão de Alunos</h2>
                    <span class="user-email">Banco de Dados: escola_linguagem</span>
                </div>
                <div class="user-info">
                    <span class="user-email">Usuário: <strong><?= htmlspecialchars($_SESSION['usuario_logado']) ?></strong></span>
                    <a href="index.php?logout=1" class="btn btn-danger">Sair</a>
                </div>
            </div>
 
            <?php if (!empty($msg_erro)): ?>
                <div class="alert-error"><?= htmlspecialchars($msg_erro) ?></div>
            <?php endif; ?>
 
            <!-- Formulário para Cadastrar / Editar Aluno -->
            <div class="form-card">
                <h3><?= $aluno_edicao ? 'Editar Aluno' : 'Cadastrar Novo Aluno' ?></h3>
                <form method="POST" action="index.php" class="form-inline">
                    <input type="hidden" name="salvar_aluno" value="1">
                    <input type="hidden" name="id_aluno" value="<?= $aluno_edicao['id_aluno'] ?? '' ?>">
 
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($aluno_edicao['nome'] ?? '') ?>" placeholder="Ex: Carlos Silva" required>
                    </div>
 
                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" id="data_nascimento" value="<?= $aluno_edicao['data_nascimento'] ?? '' ?>" required>
                    </div>
 
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" name="telefone" id="telefone" value="<?= htmlspecialchars($aluno_edicao['telefone'] ?? '') ?>" placeholder="(19) 99999-0000">
                    </div>
 
                    <div class="form-group btn-group">
                        <button type="submit" class="btn btn-success"><?= $aluno_edicao ? 'Atualizar' : 'Salvar Aluno' ?></button>
                        <?php if ($aluno_edicao): ?>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
 
            <!-- Tabela de Alunos[cite: 3] -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Nascimento</th>
                            <th>Telefone</th>
                            <th>Data Cadastro</th>
                            <th>Ações</th>
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
                                    <td>
                                        <a href="index.php?editar=<?= $aluno['id_aluno'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                        <a href="index.php?deletar=<?= $aluno['id_aluno'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este aluno?');">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Nenhum registro encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
 
</body>
</html>