<?php
// routes/marcas.php
// Lista todas as marcas cadastradas com contagem de produtos

// Encoding UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$db = new DB();
$pdo = $db->connect();

// Apenas método GET permitido
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Método não permitido. Use GET.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Busca todas as marcas com quantidade de produtos ativos
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.nome,
            m.criado_em,
            COUNT(p.id) as total_produtos
        FROM marcas m
        LEFT JOIN produtos p ON p.marca_id = m.id AND p.ativo = 1
        GROUP BY m.id, m.nome, m.criado_em
        ORDER BY m.nome ASC
    ");
    $stmt->execute();
    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'sucesso',
        'dados' => $marcas,
        'total' => count($marcas)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao buscar marcas: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}