<?php include 'header.php'; ?>

    
    <div id="meuCarrosselDeArtes" class="carousel slide" data-bs-ride="carousel">
  
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#meuCarrosselDeArtes" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Arte 1"></button>
    <button type="button" data-bs-target="#meuCarrosselDeArtes" data-bs-slide-to="1" aria-label="Arte 2"></button>
    <button type="button" data-bs-target="#meuCarrosselDeArtes" data-bs-slide-to="2" aria-label="Arte 3"></button>
    <button type="button" data-bs-target="#meuCarrosselDeArtes" data-bs-slide-to="3" aria-label="Arte 4"></button>
    <button type="button" data-bs-target="#meuCarrosselDeArtes" data-bs-slide-to="4" aria-label="Arte 5"></button>
    <button type="button" data-bs-target="#meuCarrosselDeArtes" data-bs-slide-to="5" aria-label="Arte 6"></button>
  </div>
  <div class="carousel-inner">
    
    <div class="carousel-item active">
      <img src="/ProjetoLuz/imgscarrosel/6t.jpeg" class="d-block w-100" alt="Primeira obra do artista">
    </div>
    
    <div class="carousel-item">
      <img src="/ProjetoLuz/imgscarrosel/2.jpg" class="d-block w-100" alt="Segunda obra do artista">
    </div>
    
    <div class="carousel-item">
      <img src="/ProjetoLuz/imgscarrosel/3.jpg" class="d-block w-100" alt="Terceira obra do artista">
    </div>

    <div class="carousel-item">
      <img src="/ProjetoLuz/imgscarrosel/4.jpg" class="d-block w-100" alt="Quarta obra do artista">
    </div>

    <div class="carousel-item">
      <img src="/ProjetoLuz/imgscarrosel/5.jpg" class="d-block w-100" alt="Quinta obra do artista">
    </div>

    <div class="carousel-item">
      <img src="/ProjetoLuz/imgscarrosel/1.jpg" class="d-block w-100" alt="Sexta obra do artista">
    </div>

  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#meuCarrosselDeArtes" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Arte Anterior</span>
  </button>
  
  <button class="carousel-control-next" type="button" data-bs-target="#meuCarrosselDeArtes" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Próxima Arte</span>
  </button>
<!-- Carrosel vai até aqui -->
</div>

<!-- Sobre Nós -->

        <main>
        </section>
        <section class="sobre">
            <div class="container">
                <h2>Sobre nós</h2>
                <div class="row">
                    <div class="col">
                        <img src="imgs/sobrenos.png" alt="Sobre a Luz">
                    </div>
                    <div class="col">
                        <p>Leonardo Faian (LUZ) é artista visual e designer com trajetória consolidada em muralismo, arte urbana e intervenções em contextos públicos e privados. Sua produção é uma síntese contemporânea que conecta referências clássicas, estética urbana e identidade territorial. Tendo a luz como fundamento conceitual e elemento central de sua linguagem, Faian desenvolve obras que transitam entre a pintura, o design e a experimentação visual, explorando a dualidade como motor de construção da realidade. Através do projeto LUZ, atua na criação e difusão de arte contemporânea, transformando ambientes e aproximando a experiência artística da vivência cotidiana da população.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


  
  <?php 
// 1. CONEXÃO COM O BANCO E TEMPLATE (Web Moderna)
include 'conexao.php';
include 'header.php'; 

// Puxar dados do banco
$stmt = $pdo->query("SELECT * FROM Produtos");
$dadosDoBanco = $stmt->fetchAll(PDO::FETCH_ASSOC);

// REQUISITO: ARMAZENAMENTO ESTRUTURADO COM ARRAYS
$listaProdutos = [];
foreach ($dadosDoBanco as $linha) {
    $listaProdutos[] = [
        'id'          => $linha['id_produto'],
        'nome'        => $linha['nome'],
        'descricao'   => $linha['descricao'],
        'preco'       => (float)$linha['preco'],
        'imagem'      => $linha['imagem']
    ];
}

// REQUISITO: MODULARIZAÇÃO, PARAMETROS E RETORNO
function calcularDescontoPix($precoOriginal) {
    if ($precoOriginal > 130) {
        return $precoOriginal * 0.85; 
    }
    return $precoOriginal * 0.95;
}

// REQUISITO: LÓGICA DE FILTRO
$produtosExibidos = $listaProdutos;
if (isset($_GET['filtrar_premium'])) {
    $produtosExibidos = [];
    foreach ($listaProdutos as $produto) {
        if ($produto['preco'] > 130) {
            $produtosExibidos[] = $produto;
        }
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Galeria de Artes - Luz</h1>

    <div class="mb-4 text-center">
        <a href="index.php" class="btn btn-outline-dark">Mostrar Todas</a>
        <a href="index.php?filtrar_premium=true" class="btn btn-dark">Artes Premium (> R$130)</a>
    </div>

    <div class="row">
        <?php
        // REQUISITO: CONDICIONAIS DE VALIDAÇÃO
        if (empty($produtosExibidos)) {
            echo "<div class='col-12'><p class='alert alert-warning text-center'>Nenhuma arte encontrada!</p></div>";
        } else {
            foreach ($produtosExibidos as $prod) {
                $precoComDesconto = calcularDescontoPix($prod['preco']);
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $prod['imagem']; ?>" class="card-img-top" alt="<?php echo $prod['nome']; ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo $prod['nome']; ?></h5>
                            <p class="card-text text-muted text-truncate"><?php echo $prod['descricao']; ?></p>
                            <div class="mt-auto">
                                <p class="mb-1"><strong>R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></strong></p>
                                <a href="produto.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary w-100 mt-2">Ver Detalhes</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>