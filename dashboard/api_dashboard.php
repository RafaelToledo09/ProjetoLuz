<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_logado']) || ($_SESSION['nivel'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['erro' => 'Acesso não autorizado.']);
    exit;
}

require_once __DIR__ . '/../conexao.php';

$nome_cliente = trim((string)($_GET['cliente'] ?? ''));

try {
    $sql = 'SELECT nome_cliente, data_pedido, nome_produto, valor_total_item
            FROM vw_dados_dashboard
            ORDER BY data_pedido DESC';
    $consulta = $pdo->prepare($sql);
    $consulta->execute();

    $vendas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $vendasFiltradas = array_filter($vendas, static function (array $venda) use ($nome_cliente): bool {
        return $nome_cliente === '' || stripos($venda['nome_cliente'], $nome_cliente) !== false;
    });
    $listagemDeVendas = array_map(static function (array $venda): array {
        return [
            'nome_cliente' => $venda['nome_cliente'],
            'data_pedido' => $venda['data_pedido'],
            'nome_produto' => $venda['nome_produto'],
            'valor_total_item' => (float)$venda['valor_total_item'],
        ];
    }, $vendasFiltradas);

    echo json_encode($listagemDeVendas, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Falha ao carregar os dados do dashboard.']);
}
