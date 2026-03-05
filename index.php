<?php
// index.php - Router principal da API Consorti
// Versão completa e funcional

declare(strict_types=1);

/**
 * Headers globais - Garantem JSON com UTF-8 e CORS
 */
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

/**
 * Responde OPTIONS imediatamente (CORS preflight)
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Normaliza URI removendo a base do projeto
 * Ex: /consorti_api/produtos/1 → produtos/1
 */
$basePath   = '/consorti_api';
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$path = trim(
    preg_replace("#^{$basePath}#", '', $requestUri),
    '/'
);

$segments = $path === '' ? [] : explode('/', $path);

$resource = $segments[0] ?? null;
$action   = $segments[1] ?? null;
$id       = $segments[2] ?? null;

/**
 * Dispatcher de rotas
 */
switch ($resource) {

    case 'produtos':
        require_once __DIR__ . '/routes/produtos.php';
        break;

    case 'marcas':
        require_once __DIR__ . '/routes/marcas.php';
        break;

    case 'categorias':
        // Redireciona para marcas (estrutura simplificada)
        require_once __DIR__ . '/routes/marcas.php';
        break;

    case 'usuarios':
        require_once __DIR__ . '/routes/usuarios.php';
        break;

    case 'movimentacoes':
        require_once __DIR__ . '/routes/movimentacoes.php';
        break;

    case 'relatorios':
        require_once __DIR__ . '/routes/relatorios.php';
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'status'   => 'erro',
            'mensagem' => 'Rota não encontrada'
        ], JSON_UNESCAPED_UNICODE);
        break;
}
