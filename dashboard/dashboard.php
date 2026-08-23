<?php
session_start();
if (!isset($_SESSION['usuario_logado']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: ../login.php"); 
    exit;
}
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analítico</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Painel de Vendas</span>
        </div>
    </nav>

    <div class="container">
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Faturamento Total</h5>
                        <h2 id="faturamento-total">R$ 0,00</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Produto Mais Vendido</h5>
                        <h2 id="produto-destaque">Carregando...</h2>
                    </div>
                </div>
            </div>
        </div>

       
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Últimas Vendas</h5>
                <input type="text" id="input-filtro" class="form-control w-25" placeholder="Filtrar cliente...">
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Produto</th>
                            <th>Valor Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-vendas">
                       
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <script src="dashboard.js"></script>
</body>
</html>