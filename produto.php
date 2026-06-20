<?php
include 'conexao.php';
include 'header.php';


if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='container mt-5'><p class='alert alert-danger text-center'>Produto não especificado!</p></div>";
    include 'footer.php';
    exit;
}

$id_produto = (int)$_GET['id'];


$stmt = $pdo->prepare("SELECT * FROM Produtos WHERE id_produto = :id");
$stmt->execute(['id' => $id_produto]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$produto) {
    echo "<div class='container mt-5'><p class='alert alert-danger text-center'>Arte não encontrada no sistema!</p></div>";
    include 'footer.php';
    exit;
}
?>

<div class="container mt-5 py-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <img src="imgsLoja/<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>" class="img-fluid rounded>" style="width: 100%; max-height: 500px; object-fit: cover;">
        </div>
        
        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php" class="text-dark">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Galeria</li>
                </ol>
            </nav>
            
            <h1 class="display-5 fw-bold mb-3"><?php echo $produto['nome']; ?></h1>
            <p class="text-muted fs-5 mb-4"><?php echo $produto['descricao']; ?></p>
            
            <hr class="my-4">
            
            <div class="bg-light p-4 rounded mb-4">
                <span class="text-muted d-block mb-1">Preço Original:</span>
                <h3 class="text-decoration-line-through text-muted fs-4 mb-2">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></h3>
                
                <span class="text-dark d-block mb-1 fw-bold">Preço especial no PIX:</span>
                <h2 class="text-success display-6 fw-bold">
                    R$ <?php 
                    $precoDesconto = ($produto['preco'] > 130) ? $produto['preco'] * 0.85 : $produto['preco'] * 0.95;
                    echo number_format($precoDesconto, 2, ',', '.'); 
                    ?>
                </h2>
                <small class="text-muted">* Desconto aplicado automaticamente no checkout.</small>
            </div>

            <form action="carrinho.php" method="POST">
                <input type="hidden" name="id_produto" value="<?php echo $produto['id_produto']; ?>">
                
                <div class="mb-4">
                    <label for="quantidade" class="form-label fw-bold">Quantidade:</label>
                    <input type="number" class="form-control" id="quantidade" name="quantidade" value="1" min="1" style="width: 100px;">
                </div>
                
                <button type="submit" class="btn btn-dark btn-lg w-100 mb-3">Adicionar ao Carrinho</button>
                <a href="index.php" class="btn btn-outline-secondary btn-lg w-100">Voltar para a Galeria</a>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>