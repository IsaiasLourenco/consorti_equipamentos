<?php
// sync_para_site.php - Exporta dados para o site online
// Rodar via Task Scheduler do Windows diariamente

require_once __DIR__ . '/../config/db.php';

$db = new DB();
$pdo = $db->connect();

// Exportar produtos atualizados nas últimas 24h
$stmt = $pdo->prepare("
    SELECT id, nome, sku, estoque, preco_venda, marca_id, atualizado_em
    FROM produtos
    WHERE ativo = 1
    AND (atualizado_em >= NOW() - INTERVAL 24 HOUR OR estoque != 0)
");
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Converter para JSON
$json = json_encode([
    'status' => 'sucesso',
    'data_export' => date('Y-m-d H:i:s'),
    'total' => count($produtos),
    'produtos' => $produtos
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Salvar em pasta compartilhada ou enviar via cURL para o site
$destino = __DIR__ . '/../sync/export_produtos.json';
file_put_contents($destino, $json);

// Opcional: Enviar para API do site via cURL
/*
$ch = curl_init('https://site-consorti.com.br/api/sync/receber');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
*/

echo "✅ Exportação concluída: " . count($produtos) . " produtos\n";
echo "📁 Arquivo salvo em: {$destino}\n";
?>