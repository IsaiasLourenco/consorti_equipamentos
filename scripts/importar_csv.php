<?php
// importar_csv.php - VERSÃO DEFINITIVA (SEM DOUBLE-ENCODING)
// Converte, limpa, padroniza e importa produtos do CSV para o banco

// === FUNÇÕES DE LIMPEZA E PADRONIZAÇÃO ===

function limparTexto($texto) {
    if (!is_string($texto) || empty(trim($texto))) return $texto;
    
    // 1. Remove espaços extras e tabs (já está em UTF-8, não converter de novo!)
    $texto = trim(preg_replace('/\s+/', ' ', $texto));
    
    // 2. Padroniza abreviações comuns
    $substituicoes = [
        ' p/ ' => ' para ',
        ' c/ ' => ' com ',
        ' c/' => ' com ',
        ' p/' => ' para ',
        ' n. ' => ' nº ',
        ' n.' => ' nº',
        ' nº ' => ' nº ',
        ' x ' => 'x',  // dimensões: 25 x 30 → 25x30
    ];
    $texto = str_replace(array_keys($substituicoes), array_values($substituicoes), $texto);
    
    // 3. Capitaliza: Primeira letra de cada palavra maiúscula
    $texto = mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
    
    // 4. Corrige unidades e siglas para minúsculo
    $texto = preg_replace_callback('/\b(nº|cm|mm|kg|l|w|v|db|dc)\b/i', function($m) {
        return strtolower($m[1]);
    }, $texto);
    
    return $texto;
}

function gerarSKU($marca, $produto) {
    $marcaLimpa = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $marca), 0, 3));
    $produtoHash = substr(md5($produto), 0, 8);
    return "{$marcaLimpa}-{$produtoHash}";
}

// === CONFIGURAÇÃO E CONEXÃO ===

require_once __DIR__ . "/../config/db.php";

$db = new DB();
$pdo = $db->connect();
$arquivoCsv = __DIR__ . "/produtos_limpos_utf8.csv";

if (!file_exists($arquivoCsv)) {
    die("❌ Arquivo não encontrado: {$arquivoCsv}");
}

echo "🚀 Iniciando importação...\n<hr>";

$pdo->beginTransaction();

try {
    $arquivo = fopen($arquivoCsv, "r");
    
    // Remove BOM se existir
    $bom = fread($arquivo, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($arquivo);
    }
    
    $linhaNumero = 0;
    $produtosInseridos = 0;
    $produtosAtualizados = 0;
    $marcasCriadas = 0;
    $cacheMarcas = [];
    $exemplos = [];

    // Pula cabeçalho
    fgetcsv($arquivo, 0, "\t");

    while (($linha = fgetcsv($arquivo, 0, "\t")) !== false) {
        $linhaNumero++;
        
        // ⚠️ NÃO converter encoding aqui! O CSV já está em UTF-8.
        // Apenas faz trim nos campos
        $linha = array_map('trim', $linha);
        
        // Ignora linhas com menos de 4 colunas
        if (count($linha) < 4) continue;

        // === APLICA LIMPEZA E PADRONIZAÇÃO ===
        $marcaNome = limparTexto($linha[0]);
        $nomeProduto = limparTexto($linha[1]);
        $estoque = (int)($linha[2] ?? 0);
        $preco = (float)($linha[3] ?? 0);

        // Ignora linhas sem marca ou produto
        if (empty($marcaNome) || empty($nomeProduto)) continue;

        // === BUSCA OU CRIA MARCA ===
        if (!isset($cacheMarcas[$marcaNome])) {
            $stmt = $pdo->prepare("SELECT id FROM marcas WHERE nome = ?");
            $stmt->execute([$marcaNome]);
            $marcaId = $stmt->fetchColumn();

            if (!$marcaId) {
                $stmt = $pdo->prepare("INSERT INTO marcas (nome) VALUES (?)");
                $stmt->execute([$marcaNome]);
                $marcaId = $pdo->lastInsertId();
                $marcasCriadas++;
            }
            $cacheMarcas[$marcaNome] = $marcaId;
        } else {
            $marcaId = $cacheMarcas[$marcaNome];
        }

        // === GERA SKU ===
        $sku = gerarSKU($marcaNome, $nomeProduto);

        // === INSERE OU ATUALIZA PRODUTO ===
        $stmt = $pdo->prepare("
            INSERT INTO produtos (marca_id, nome, sku, estoque, preco_custo, preco_venda, ativo)
            VALUES (?, ?, ?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE
                marca_id = VALUES(marca_id),
                nome = VALUES(nome),
                estoque = VALUES(estoque),
                preco_custo = VALUES(preco_custo),
                preco_venda = VALUES(preco_venda),
                atualizado_em = CURRENT_TIMESTAMP
        ");

        $stmt->execute([$marcaId, $nomeProduto, $sku, $estoque, $preco, $preco]);
        
        // Conta insert vs update
        if ($pdo->lastInsertId()) {
            $produtosInseridos++;
        } else {
            $produtosAtualizados++;
        }
        
        // Coleta exemplos para mostrar no final
        if (count($exemplos) < 10) {
            $exemplos[] = [
                'original' => $linha[1],
                'padronizado' => $nomeProduto,
                'marca' => $marcaNome
            ];
        }
    }

    fclose($arquivo);
    $pdo->commit();

    // === RELATÓRIO FINAL ===
    echo "✅ <strong>Importação concluída com sucesso!</strong><br><br>";
    echo "📦 Produtos inseridos: <strong>{$produtosInseridos}</strong><br>";
    echo "🔄 Produtos atualizados: <strong>{$produtosAtualizados}</strong><br>";
    echo "🏷️  Marcas criadas: <strong>{$marcasCriadas}</strong><br><br>";
    
    echo "<strong>🔍 Exemplos de padronização:</strong><br><pre>";
    foreach ($exemplos as $ex) {
        echo "ANTES: \"{$ex['original']}\"\n";
        echo "DEPOIS: \"{$ex['padronizado']}\" ({$ex['marca']})\n";
        echo str_repeat("-", 60) . "\n";
    }
    echo "</pre>";
    
    echo "🎉 Tudo pronto! Teste a API em:<br>";
    echo "<a href='http://localhost/consorti_api/produtos?limite=5' target='_blank'>http://localhost/consorti_api/produtos?limite=5</a>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ <strong>Erro na linha {$linhaNumero}:</strong> " . $e->getMessage() . "<br>";
    echo "<pre>" . print_r($linha ?? [], true) . "</pre>";
}