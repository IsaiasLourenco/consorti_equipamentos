<?php
// routes/movimentacoes.php

// Encoding UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/MovimentacoesController.php';
require_once __DIR__ . '/../models/Movimentacao.php';

$db = new DB();
$pdo = $db->connect();
$controller = new MovimentacoesController($pdo);

// Rota especial para resumo por produto
if (isset($_GET['resumo_produto'])) {
    $controller->resumoPorProduto((int)$_GET['resumo_produto']);
    exit;
}

// CRUD padrão
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Se tem ID → buscar por ID
        if (isset($_GET['id'])) {
            $controller->buscarPorId((int)$_GET['id']);
        } else {
            // Senão → listar todos (com filtros)
            $controller->listarTodos();
        }
        break;

    case 'POST':
        // Criar nova movimentação
        $controller->criar();
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
        break;
}