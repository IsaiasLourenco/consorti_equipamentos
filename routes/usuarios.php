<?php
// routes/usuarios.php

// Encoding UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/UsuariosController.php';
require_once __DIR__ . '/../models/Usuario.php';

$db = new DB();
$pdo = $db->connect();
$controller = new UsuariosController($pdo);

// Rota especial para login
if (isset($_GET['acao']) && $_GET['acao'] === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $controller->login();
    exit;
}

// CRUD padrão
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Se tem ID → buscar por ID
        if (isset($_GET['id'])) {
            $controller->buscarPorId((int)$_GET['id']);
        } else {
            // Senão → listar todos
            $controller->listarTodos();
        }
        break;

    case 'POST':
        // Criar novo usuário
        $controller->criar();
        break;

    case 'PUT':
        // Atualizar usuário (precisa de ID na URL)
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $controller->atualizar($id);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'erro', 'mensagem' => 'ID obrigatório para atualização'], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'DELETE':
        // Excluir usuário (precisa de ID na URL)
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $controller->excluir($id);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'erro', 'mensagem' => 'ID obrigatório para exclusão'], JSON_UNESCAPED_UNICODE);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
        break;
}