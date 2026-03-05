<?php
// routes/produtos.php
// Roteador para endpoints de Produtos

// Encoding UTF-8 e CORS
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Responde OPTIONS imediatamente (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/ProdutosController.php';
require_once __DIR__ . '/../models/Produto.php';

$db = new DB();
$pdo = $db->connect();

// Captura parâmetros da URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : null;
$marca_id = isset($_GET['marca_id']) ? (int)$_GET['marca_id'] : null;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$limite = isset($_GET['limite']) ? min(100, max(1, (int)$_GET['limite'])) : 50;

$controller = new ProdutosController($pdo);

// 🔹 PUT → Atualizar produto (apenas preços)
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $id) {
    $controller->atualizar($id);
    exit;
}

// 🔹 GET com ID → buscar produto específico
if ($id && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->buscarPorId($id);
    exit;
}

// 🔹 GET sem ID → listar com filtros e paginação
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->listarTodos($limite, $pagina, $busca, $marca_id);
    exit;
}

// Método não permitido
http_response_code(405);
echo json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);