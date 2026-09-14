<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || ($_SESSION['nivel'] ?? '') !== 'admin') {
    header('Location: ../paginas/login.php');
    exit;
}

require_once __DIR__ . '/../conexao.php';

$secoes = ['produtos', 'clientes', 'pedidos'];
$secao = $_GET['secao'] ?? 'produtos';
if (!in_array($secao, $secoes, true)) {
    $secao = 'produtos';
}

$mensagem = $_SESSION['mensagem_admin'] ?? '';
unset($_SESSION['mensagem_admin']);
$erro = '';
$editar = null;

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $secao): never
{
    header('Location: gerenciar.php?secao=' . urlencode($secao));
    exit;
}

function salvarImagem(array $arquivo, ?string $imagemAtual = null): string
{
    if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        if ($imagemAtual !== null && $imagemAtual !== '') {
            return $imagemAtual;
        }
        throw new RuntimeException('Selecione uma imagem para o produto.');
    }

    if (($arquivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível enviar a imagem.');
    }

    if (($arquivo['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('A imagem deve ter no máximo 5 MB.');
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
    if (!isset($tiposPermitidos[$tipo])) {
        throw new RuntimeException('Formato inválido. Use JPG, PNG ou WEBP.');
    }

    $diretorio = __DIR__ . '/../imgsLoja';
    if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true)) {
        throw new RuntimeException('Não foi possível preparar a pasta de imagens.');
    }

    $nomeArquivo = bin2hex(random_bytes(12)) . '.' . $tiposPermitidos[$tipo];
    if (!move_uploaded_file($arquivo['tmp_name'], $diretorio . '/' . $nomeArquivo)) {
        throw new RuntimeException('Não foi possível salvar a imagem.');
    }

    return $nomeArquivo;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $secaoPost = $_POST['secao'] ?? $secao;
    if (!in_array($secaoPost, $secoes, true)) {
        $secaoPost = 'produtos';
    }

    try {
        if ($acao === 'excluir') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new RuntimeException('Registro inválido para exclusão.');
            }

            $tabelas = [
                'produtos' => ['Produtos', 'id_produto'],
                'clientes' => ['Cliente', 'id_cliente'],
                'pedidos' => ['pedido', 'id_pedido'],
            ];
            $pdo->beginTransaction();
            if ($secaoPost === 'pedidos') {
                $stmt = $pdo->prepare('DELETE FROM Itens_pedido WHERE id_pedido = :id');
                $stmt->execute(['id' => $id]);
            }
            [$tabela, $coluna] = $tabelas[$secaoPost];
            $stmt = $pdo->prepare("DELETE FROM {$tabela} WHERE {$coluna} = :id");
            $stmt->execute(['id' => $id]);
            $pdo->commit();
            $_SESSION['mensagem_admin'] = $stmt->rowCount() > 0
                ? 'Registro excluído com sucesso.'
                : 'Registro não encontrado.';
            redirecionar($secaoPost);
        }

        if ($acao !== 'salvar') {
            throw new RuntimeException('Ação inválida.');
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        if ($secaoPost === 'produtos') {
            $nome = trim((string)($_POST['nome'] ?? ''));
            $descricao = trim((string)($_POST['descricao'] ?? ''));
            $preco = filter_var($_POST['preco'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($nome === '' || $descricao === '' || $preco === false || $preco < 0) {
                throw new RuntimeException('Preencha nome, descrição e um preço válido.');
            }

            $imagemAtual = null;
            if ($id) {
                $consultaImagem = $pdo->prepare('SELECT imagem FROM Produtos WHERE id_produto = :id');
                $consultaImagem->execute(['id' => $id]);
                $imagemAtual = $consultaImagem->fetchColumn() ?: null;
            }
            $imagem = salvarImagem($_FILES['imagem'] ?? [], $imagemAtual);
            $dados = ['nome' => $nome, 'descricao' => $descricao, 'preco' => $preco, 'imagem' => $imagem];
            if ($id) {
                $dados['id'] = $id;
                $stmt = $pdo->prepare('UPDATE Produtos SET nome = :nome, descricao = :descricao, preco = :preco, imagem = :imagem WHERE id_produto = :id');
            } else {
                $stmt = $pdo->prepare('INSERT INTO Produtos (nome, descricao, preco, imagem) VALUES (:nome, :descricao, :preco, :imagem)');
            }
            $stmt->execute($dados);
        } elseif ($secaoPost === 'clientes') {
            $nome = trim((string)($_POST['nome'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $telefone = trim((string)($_POST['telefone'] ?? ''));
            $nivel = ($_POST['nivel'] ?? 'cliente') === 'admin' ? 'admin' : 'cliente';
            $senha = (string)($_POST['senha'] ?? '');
            if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $telefone === '' || (!$id && $senha === '')) {
                throw new RuntimeException('Preencha nome, e-mail, telefone e senha no cadastro.');
            }

            $dados = ['nome' => $nome, 'email' => $email, 'telefone' => $telefone, 'nivel' => $nivel];
            if ($senha !== '') {
                $dados['senha'] = password_hash($senha, PASSWORD_DEFAULT);
            }
            if ($id) {
                $dados['id'] = $id;
                $sql = 'UPDATE Cliente SET nome = :nome, email = :email, telefone = :telefone, nivel = :nivel';
                if ($senha !== '') {
                    $sql .= ', senha = :senha';
                }
                $sql .= ' WHERE id_cliente = :id';
                $stmt = $pdo->prepare($sql);
            } else {
                $stmt = $pdo->prepare('INSERT INTO Cliente (nome, email, senha, telefone, nivel) VALUES (:nome, :email, :senha, :telefone, :nivel)');
            }
            $stmt->execute($dados);
        } else {
            $cliente = filter_var($_POST['id_cliente'] ?? null, FILTER_VALIDATE_INT);
            $produto = filter_var($_POST['id_produto'] ?? null, FILTER_VALIDATE_INT);
            $quantidade = filter_var($_POST['quantidade'] ?? null, FILTER_VALIDATE_INT);
            $data = trim((string)($_POST['data_pedido'] ?? ''));
            $status = trim((string)($_POST['status'] ?? ''));
            if (!$cliente || !$produto || !$quantidade || $quantidade < 1 || $data === '' || $status === '') {
                throw new RuntimeException('Selecione cliente, produto, quantidade, data e status.');
            }

            $consultaProduto = $pdo->prepare('SELECT preco FROM Produtos WHERE id_produto = :id');
            $consultaProduto->execute(['id' => $produto]);
            $preco = $consultaProduto->fetchColumn();
            if ($preco === false) {
                throw new RuntimeException('Produto selecionado não foi encontrado.');
            }
            $total = (float)$preco * $quantidade;
            $pdo->beginTransaction();
            $dados = ['id_cliente' => $cliente, 'data_pedido' => $data, 'status' => $status, 'total' => $total];
            if ($id) {
                $dados['id'] = $id;
                $stmt = $pdo->prepare('UPDATE pedido SET id_cliente = :id_cliente, data_pedido = :data_pedido, status = :status, total = :total WHERE id_pedido = :id');
                $stmt->execute($dados);
                $stmtItem = $pdo->prepare('UPDATE Itens_pedido SET id_produto = :id_produto, quantidade = :quantidade, preco_unitario = :preco WHERE id_pedido = :id_pedido');
                $stmtItem->execute(['id_produto' => $produto, 'quantidade' => $quantidade, 'preco' => $preco, 'id_pedido' => $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO pedido (id_cliente, data_pedido, status, total) VALUES (:id_cliente, :data_pedido, :status, :total)');
                $stmt->execute($dados);
                $id = (int)$pdo->lastInsertId();
                $stmtItem = $pdo->prepare('INSERT INTO Itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (:id_pedido, :id_produto, :quantidade, :preco)');
                $stmtItem->execute(['id_pedido' => $id, 'id_produto' => $produto, 'quantidade' => $quantidade, 'preco' => $preco]);
            }
            $pdo->commit();
        }

        $_SESSION['mensagem_admin'] = $id ? 'Registro atualizado com sucesso.' : 'Registro criado com sucesso.';
        redirecionar($secaoPost);
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erro = 'Não foi possível salvar ou excluir. Verifique os vínculos e os dados informados.';
    } catch (RuntimeException $exception) {
        $erro = $exception->getMessage();
    }
}

if (isset($_GET['editar'])) {
    $id = filter_var($_GET['editar'], FILTER_VALIDATE_INT);
    if ($id) {
        $consultas = [
            'produtos' => ['SELECT * FROM Produtos WHERE id_produto = :id', 'id_produto'],
            'clientes' => ['SELECT id_cliente, nome, email, telefone, nivel FROM Cliente WHERE id_cliente = :id', 'id_cliente'],
            'pedidos' => ['SELECT p.*, ip.id_produto, ip.quantidade FROM pedido p JOIN Itens_pedido ip ON ip.id_pedido = p.id_pedido WHERE p.id_pedido = :id', 'id_pedido'],
        ];
        [$sql, $coluna] = $consultas[$secao];
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $editar = $stmt->fetch() ?: null;
    }
}

$dados = [];
if ($secao === 'produtos') {
    $dados = $pdo->query('SELECT * FROM Produtos ORDER BY id_produto DESC')->fetchAll();
} elseif ($secao === 'clientes') {
    $dados = $pdo->query('SELECT id_cliente, nome, email, telefone, nivel FROM Cliente ORDER BY id_cliente DESC')->fetchAll();
} else {
    $dados = $pdo->query('SELECT p.*, c.nome AS nome_cliente FROM pedido p JOIN Cliente c ON c.id_cliente = p.id_cliente ORDER BY p.id_pedido DESC')->fetchAll();
}
$clientes = $pdo->query('SELECT id_cliente, nome FROM Cliente ORDER BY nome')->fetchAll();
$produtos = $pdo->query('SELECT id_produto, nome, preco FROM Produtos ORDER BY nome')->fetchAll();
?>
<?php include '../header.php'; ?>
<main class="container py-4 pagina-gerenciar">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h2 mb-0">Gerenciamento</h1>
        <a class="btn btn-outline-secondary" href="dashboard.php">Voltar ao dashboard</a>
    </div>

    <?php if ($mensagem !== ''): ?><div class="alert alert-success"><?php echo escapar($mensagem); ?></div><?php endif; ?>
    <?php if ($erro !== ''): ?><div class="alert alert-danger"><?php echo escapar($erro); ?></div><?php endif; ?>

    <ul class="nav nav-tabs mb-4">
        <?php foreach ($secoes as $item): ?>
            <li class="nav-item"><a class="nav-link <?php echo $secao === $item ? 'active' : ''; ?>" href="?secao=<?php echo escapar($item); ?>"><?php echo ucfirst($item); ?></a></li>
        <?php endforeach; ?>
    </ul>

    <section class="card mb-4">
        <div class="card-header"> <?php echo $editar ? 'Editar registro' : 'Novo registro'; ?></div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="secao" value="<?php echo escapar($secao); ?>">
                <?php if ($editar): ?><input type="hidden" name="id" value="<?php echo (int)$editar[array_key_first($editar)]; ?>"><?php endif; ?>
                <?php if ($secao === 'produtos'): ?>
                    <div class="col-md-6"><label class="form-label" for="nome">Nome</label><input class="form-control" id="nome" name="nome" required value="<?php echo escapar($editar['nome'] ?? ''); ?>"></div>
                    <div class="col-md-3"><label class="form-label" for="preco">Preço</label><input class="form-control" id="preco" name="preco" type="number" min="0" step="0.01" required value="<?php echo escapar((string)($editar['preco'] ?? '')); ?>"></div>
                    <div class="col-md-3"><label class="form-label" for="imagem">Imagem</label><input class="form-control" id="imagem" name="imagem" type="file" accept="image/jpeg,image/png,image/webp" <?php echo $editar ? '' : 'required'; ?>><div class="form-text">JPG, PNG ou WEBP, até 5 MB.</div><?php if (!empty($editar['imagem'])): ?><div class="form-text">Atual: <?php echo escapar($editar['imagem']); ?></div><?php endif; ?></div>
                    <div class="col-12"><label class="form-label" for="descricao">Descrição</label><textarea class="form-control" id="descricao" name="descricao" required><?php echo escapar($editar['descricao'] ?? ''); ?></textarea></div>
                <?php elseif ($secao === 'clientes'): ?>
                    <div class="col-md-6"><label class="form-label" for="nome">Nome</label><input class="form-control" id="nome" name="nome" required value="<?php echo escapar($editar['nome'] ?? ''); ?>"></div>
                    <div class="col-md-6"><label class="form-label" for="email">E-mail</label><input class="form-control" id="email" name="email" type="email" required value="<?php echo escapar($editar['email'] ?? ''); ?>"></div>
                    <div class="col-md-4"><label class="form-label" for="telefone">Telefone</label><input class="form-control" id="telefone" name="telefone" required value="<?php echo escapar($editar['telefone'] ?? ''); ?>"></div>
                    <div class="col-md-4"><label class="form-label" for="senha">Senha <?php echo $editar ? '(opcional na edição)' : ''; ?></label><input class="form-control" id="senha" name="senha" type="password" <?php echo $editar ? '' : 'required'; ?>></div>
                    <div class="col-md-4"><label class="form-label" for="nivel">Nível</label><select class="form-select" id="nivel" name="nivel"><option value="cliente" <?php echo ($editar['nivel'] ?? '') === 'cliente' ? 'selected' : ''; ?>>Cliente</option><option value="admin" <?php echo ($editar['nivel'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrador</option></select></div>
                <?php else: ?>
                    <div class="col-md-3"><label class="form-label" for="id_cliente">Cliente</label><select class="form-select" id="id_cliente" name="id_cliente" required><option value="">Selecione</option><?php foreach ($clientes as $cliente): ?><option value="<?php echo (int)$cliente['id_cliente']; ?>" <?php echo (int)($editar['id_cliente'] ?? 0) === (int)$cliente['id_cliente'] ? 'selected' : ''; ?>><?php echo escapar($cliente['nome']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label" for="id_produto">Produto vendido</label><select class="form-select" id="id_produto" name="id_produto" required><option value="">Selecione</option><?php foreach ($produtos as $produto): ?><option value="<?php echo (int)$produto['id_produto']; ?>" data-preco="<?php echo htmlspecialchars((string)$produto['preco'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo (int)($editar['id_produto'] ?? 0) === (int)$produto['id_produto'] ? 'selected' : ''; ?>><?php echo escapar($produto['nome']); ?> - R$ <?php echo number_format((float)$produto['preco'], 2, ',', '.'); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label" for="quantidade">Quantidade</label><input class="form-control" id="quantidade" name="quantidade" type="number" min="1" step="1" required value="<?php echo (int)($editar['quantidade'] ?? 1); ?>"></div>
                    <div class="col-md-2"><label class="form-label" for="data_pedido">Data</label><input class="form-control" id="data_pedido" name="data_pedido" type="datetime-local" required value="<?php echo isset($editar['data_pedido']) ? escapar(date('Y-m-d\TH:i', strtotime($editar['data_pedido']))) : ''; ?>"></div>
                    <div class="col-md-2"><label class="form-label" for="status">Status</label><input class="form-control" id="status" name="status" required value="<?php echo escapar($editar['status'] ?? 'Pendente'); ?>"></div>
                    <div class="col-md-2"><label class="form-label" for="total">Total calculado</label><input class="form-control" id="total" name="total" type="text" readonly value="R$ <?php echo number_format((float)($editar['total'] ?? 0), 2, ',', '.'); ?>"></div>
                <?php endif; ?>
                <div class="col-12"><button class="btn btn-primary" type="submit"><?php echo $editar ? 'Atualizar' : 'Cadastrar'; ?></button><?php if ($editar): ?><a class="btn btn-outline-secondary ms-2" href="?secao=<?php echo escapar($secao); ?>">Cancelar</a><?php endif; ?></div>
            </form>
        </div>
    </section>

    <section class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark"><tr>
                <?php if ($secao === 'produtos'): ?><th>Nome</th><th>Preço</th><th>Imagem</th>
                <?php elseif ($secao === 'clientes'): ?><th>Nome</th><th>E-mail</th><th>Nível</th>
                <?php else: ?><th>Cliente</th><th>Data</th><th>Status</th><th>Total</th><?php endif; ?><th>Ações</th>
            </tr></thead>
            <tbody>
            <?php foreach ($dados as $item): ?><tr>
                <?php if ($secao === 'produtos'): ?><td><?php echo escapar($item['nome']); ?></td><td>R$ <?php echo number_format((float)$item['preco'], 2, ',', '.'); ?></td><td><?php echo escapar($item['imagem']); ?></td><td>
                <?php elseif ($secao === 'clientes'): ?><td><?php echo escapar($item['nome']); ?></td><td><?php echo escapar($item['email']); ?></td><td><?php echo escapar($item['nivel']); ?></td><td>
                <?php else: ?><td><?php echo escapar($item['nome_cliente']); ?></td><td><?php echo escapar($item['data_pedido']); ?></td><td><?php echo escapar($item['status']); ?></td><td>R$ <?php echo number_format((float)$item['total'], 2, ',', '.'); ?></td><td>
                <?php endif; ?><a class="btn btn-sm btn-outline-primary" href="?secao=<?php echo escapar($secao); ?>&editar=<?php echo (int)$item[array_key_first($item)]; ?>">Editar</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este registro?');"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="secao" value="<?php echo escapar($secao); ?>"><input type="hidden" name="id" value="<?php echo (int)$item[array_key_first($item)]; ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button></form>
                </td>
            </tr><?php endforeach; ?>
            <?php if (!$dados): ?><tr><td class="text-center text-muted" colspan="5">Nenhum registro encontrado.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
<?php if ($secao === 'pedidos'): ?>
<script>
    const produtoPedido = document.getElementById('id_produto');
    const quantidadePedido = document.getElementById('quantidade');
    const totalPedido = document.getElementById('total');
    const atualizarTotalPedido = () => {
        const opcao = produtoPedido instanceof HTMLSelectElement ? produtoPedido.selectedOptions[0] : null;
        const preco = Number(opcao?.dataset.preco ?? 0);
        const quantidade = Number(quantidadePedido instanceof HTMLInputElement ? quantidadePedido.value : 0);
        totalPedido.value = `R$ ${(preco * quantidade).toFixed(2).replace('.', ',')}`;
    };
    produtoPedido?.addEventListener('change', atualizarTotalPedido);
    quantidadePedido?.addEventListener('input', atualizarTotalPedido);
</script>
<?php endif; ?>
<?php include '../footer.php'; ?>
