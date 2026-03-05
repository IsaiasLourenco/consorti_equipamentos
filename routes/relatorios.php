<?php
// routes/relatorios.php

// Encoding UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/RelatoriosController.php';
require_once __DIR__ . '/../models/Relatorio.php';

$db = new DB();
$pdo = $db->connect();
$controller = new RelatoriosController($pdo);

// Apenas GET permitido para relatórios
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido. Use GET.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Roteamento interno por tipo de relatório
$tipo = $_GET['tipo'] ?? 'dashboard';

switch ($tipo) {
    case 'vendas_periodo':
        $controller->vendasPorPeriodo();
        break;
        
    case 'produtos_mais_vendidos':
        $controller->produtosMaisVendidos();
        break;
        
    case 'estoque_baixo':
        $controller->estoqueBaixo();
        break;
        
    case 'resumo_estoque':
        $controller->resumoGeralEstoque();
        break;
        
    case 'movimentacoes_usuario':
        $controller->movimentacoesPorUsuario();
        break;
        
    case 'produtos_marca':
        $controller->produtosPorMarca();
        break;
        
    case 'receita_periodo':
        $controller->receitaPorPeriodo();
        break;
        
    case 'dashboard':
    default:
        $controller->dashboard();
        break;
}