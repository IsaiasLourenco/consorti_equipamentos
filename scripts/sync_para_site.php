<?php
/**
 * sync_para_site.php - Exporta dados do sistema local para o site online
 * 
 * Este script deve ser executado diariamente via Task Scheduler do Windows.
 * Ele exporta produtos, marcas e estoque atualizados para um arquivo JSON
 * que pode ser consumido pelo site via URL ou upload FTP.
 * 
 * @author [Seu Nome]
 * @version 1.0.0
 * @date 2026-03-04
 */

// Configurações de erro para log (não exibir no navegador)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/sync_errors.log');

// Tempo máximo de execução (para grandes volumes de dados)
set_time_limit(300);

require_once __DIR__ . '/../config/db.php';

// === CONFIGURAÇÕES ===
$config = [
    // Pasta para salvar arquivos de exportação (acessível via web ou FTP)
    'export_folder' => __DIR__ . '/../sync/',
    
    // URL do site para enviar os dados (deixe vazio para só salvar arquivo)
    'site_api_url' => 'https://seusite.com.br/api/sync/receber',
    
    // Token de autenticação para a API do site (se necessário)
    'site_api_token' => 'seu_token_aqui',
    
    // Exportar apenas produtos atualizados nas últimas X horas
    'hours_back' => 24,
    
    // Incluir produtos com estoque zerado na exportação
    'include_zero_stock' => true,
    
    // Formato de saída: 'json' ou 'csv'
    'output_format' => 'json',
    
    // Log detalhado
    'log_file' => __DIR__ . '/../logs/sync_log.txt',
];

// Criar pasta de exportação se não existir
if (!is_dir($config['export_folder'])) {
    mkdir($config['export_folder'], 0755, true);
}

// Criar pasta de logs se não existir
if (!is_dir(dirname($config['log_file']))) {
    mkdir(dirname($config['log_file']), 0755, true);
}

// Função para log
function logMessage($message, $config) {
    $timestamp = date('Y-m-d H:i:s');
    $log = "[{$timestamp}] {$message}\n";
    file_put_contents($config['log_file'], $log, FILE_APPEND);
    echo $log;
}

logMessage("🚀 Iniciando sincronização...", $config);

try {
    $db = new DB();
    $pdo = $db->connect();
    
    // === EXPORTAR PRODUTOS ===
    logMessage("📦 Exportando produtos...", $config);
    
    $sql = "
        SELECT 
            p.id,
            p.nome,
            p.sku,
            p.estoque,
            p.preco_custo,
            p.preco_venda,
            p.ativo,
            p.atualizado_em,
            m.nome as marca_nome,
            m.id as marca_id
        FROM produtos p
        INNER JOIN marcas m ON p.marca_id = m.id
        WHERE p.ativo = 1
    ";
    
    // Filtrar por atualização recente (opcional)
    if ($config['hours_back'] > 0) {
        $sql .= " AND (p.atualizado_em >= NOW() - INTERVAL {$config['hours_back']} HOUR)";
    }
    
    // Incluir ou excluir estoque zerado
    if (!$config['include_zero_stock']) {
        $sql .= " AND p.estoque > 0";
    }
    
    $sql .= " ORDER BY p.atualizado_em DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    logMessage("✅ {$stmt->rowCount()} produtos encontrados para exportação", $config);
    
    // === EXPORTAR MARCAS ===
    logMessage("🏷️ Exportando marcas...", $config);
    
    $stmt = $pdo->query("SELECT id, nome, criado_em FROM marcas ORDER BY nome ASC");
    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    logMessage("✅ " . count($marcas) . " marcas encontradas", $config);
    
    // === EXPORTAR ESTOQUE BAIXO (Alerta) ===
    logMessage("⚠️ Exportando alerta de estoque baixo...", $config);
    
    $stmt = $pdo->prepare("
        SELECT id, nome, sku, estoque, preco_venda, marca_nome
        FROM produtos
        WHERE ativo = 1 AND estoque <= 5
        ORDER BY estoque ASC, nome ASC
        LIMIT 50
    ");
    $stmt->execute();
    $estoque_baixo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    logMessage("✅ " . count($estoque_baixo) . " produtos com estoque baixo", $config);
    
    // === MONTAR PACOTE DE EXPORTAÇÃO ===
    $export = [
        'status' => 'sucesso',
        'export_timestamp' => date('Y-m-d H:i:s'),
        'export_version' => '1.0.0',
        'config' => [
            'hours_back' => $config['hours_back'],
            'include_zero_stock' => $config['include_zero_stock']
        ],
        'summary' => [
            'total_produtos' => count($produtos),
            'total_marcas' => count($marcas),
            'alertas_estoque_baixo' => count($estoque_baixo)
        ],
        'data' => [
            'produtos' => $produtos,
            'marcas' => $marcas,
            'alertas' => $estoque_baixo
        ]
    ];
    
    // === SALVAR ARQUIVO JSON ===
    if ($config['output_format'] === 'json') {
        $json_file = $config['export_folder'] . '/export_' . date('Y-m-d') . '.json';
        $json_content = json_encode($export, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        if (file_put_contents($json_file, $json_content) !== false) {
            logMessage("💾 Arquivo JSON salvo: {$json_file}", $config);
        } else {
            throw new Exception("Falha ao salvar arquivo JSON");
        }
        
        // Criar link simbólico para "latest.json" (sempre aponta para o mais recente)
        $latest_link = $config['export_folder'] . '/latest.json';
        if (file_exists($latest_link) || is_link($latest_link)) {
            unlink($latest_link);
        }
        symlink(basename($json_file), $latest_link);
        logMessage("🔗 Link 'latest.json' atualizado", $config);
    }
    
    // === SALVAR ARQUIVO CSV (Opcional) ===
    if ($config['output_format'] === 'csv' || true) {
        $csv_file = $config['export_folder'] . '/produtos_' . date('Y-m-d') . '.csv';
        
        if ($fp = fopen($csv_file, 'w')) {
            // Cabeçalho
            fputcsv($fp, [
                'id', 'nome', 'sku', 'estoque', 'preco_custo', 
                'preco_venda', 'marca_nome', 'atualizado_em'
            ], ';');
            
            // Dados
            foreach ($produtos as $prod) {
                fputcsv($fp, [
                    $prod['id'],
                    $prod['nome'],
                    $prod['sku'],
                    $prod['estoque'],
                    number_format($prod['preco_custo'], 2, ',', '.'),
                    number_format($prod['preco_venda'], 2, ',', '.'),
                    $prod['marca_nome'],
                    $prod['atualizado_em']
                ], ';');
            }
            
            fclose($fp);
            logMessage("💾 Arquivo CSV salvo: {$csv_file}", $config);
        }
    }
    
    // === ENVIAR PARA API DO SITE (Opcional) ===
    if (!empty($config['site_api_url'])) {
        logMessage("🌐 Enviando dados para API do site...", $config);
        
        $ch = curl_init($config['site_api_url']);
        
        $payload = json_encode([
            'token' => $config['site_api_token'],
            'source' => 'consorti_local',
            'timestamp' => time(),
            'data' => $export['data']
        ], JSON_UNESCAPED_UNICODE);
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $config['site_api_token'],
                'X-Source: Consorti-Local/1.0'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($http_code >= 200 && $http_code < 300) {
            logMessage("✅ Dados enviados com sucesso (HTTP {$http_code})", $config);
            logMessage("📄 Resposta: " . substr($response, 0, 200), $config);
        } else {
            logMessage("❌ Falha ao enviar: HTTP {$http_code} - {$error}", $config);
            logMessage("📄 Resposta: {$response}", $config);
        }
    }
    
    // === LIMPAR ARQUIVOS ANTIGOS (Manter últimos 7 dias) ===
    logMessage("🧹 Limpando arquivos antigos...", $config);
    
    $files = glob($config['export_folder'] . '/export_*.json');
    $keep_days = 7;
    
    foreach ($files as $file) {
        $file_time = filemtime($file);
        if (time() - $file_time > ($keep_days * 86400)) {
            unlink($file);
            logMessage("🗑️ Excluído: " . basename($file), $config);
        }
    }
    
    logMessage("🎉 Sincronização concluída com sucesso!", $config);
    
    // Retorno para Task Scheduler
    exit(0);
    
} catch (Exception $e) {
    logMessage("❌ ERRO: " . $e->getMessage(), $config);
    logMessage("📍 Stack: " . $e->getTraceAsString(), $config);
    
    // Retorno de erro para Task Scheduler
    exit(1);
}
?>