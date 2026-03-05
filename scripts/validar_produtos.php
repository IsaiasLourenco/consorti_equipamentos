<?php
// validar_produtos.php - Identifica produtos com nomes suspeitos
// Salvar em: C:\xampp\htdocs\consorti_api\scripts\validar_produtos.php

require_once __DIR__ . "/../config/db.php";

$db = new DB();
$pdo = $db->connect();

echo "<h2>🔍 Produtos com Nomes Suspeitos</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f4f4f4; }
    .warning { background: #fff3cd; }
    .success { background: #d4edda; }
</style>";

// Busca produtos com nomes muito curtos ou só números/dimensões
$stmt = $pdo->prepare("
    SELECT p.id, p.nome, p.sku, p.estoque, m.nome as marca_nome
    FROM produtos p
    INNER JOIN marcas m ON p.marca_id = m.id
    WHERE LENGTH(p.nome) <= 15 
      AND (p.nome REGEXP '^[0-9xX\\s]+$' OR p.nome REGEXP '^[0-9]+[xX][0-9]+$')
    ORDER BY m.nome, p.nome
");
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($produtos) === 0) {
    echo "<div class='success'><strong>✅ Nenhum produto suspeito encontrado!</strong></div>";
} else {
    echo "<div class='warning'><strong>⚠️ " . count($produtos) . "</strong> produtos encontrados:</div>";
    echo "<table>
        <tr>
            <th>ID</th>
            <th>Nome Atual</th>
            <th>SKU</th>
            <th>Marca</th>
            <th>Estoque</th>
            <th>Sugestão</th>
        </tr>";
    
    foreach ($produtos as $prod) {
        $sugestao = sugerirNome($prod['nome'], $prod['marca_nome']);
        
        echo "<tr>
            <td>{$prod['id']}</td>
            <td><strong>{$prod['nome']}</strong></td>
            <td>{$prod['sku']}</td>
            <td>{$prod['marca_nome']}</td>
            <td>{$prod['estoque']}</td>
            <td><em>{$sugestao}</em></td>
        </tr>";
    }
    echo "</table>";
    
    echo "<h3>📝 SQL para Corrigir</h3>";
    echo "<pre>";
    foreach ($produtos as $prod) {
        $sugestao = sugerirNome($prod['nome'], $prod['marca_nome']);
        echo "-- UPDATE produtos SET nome = '{$sugestao}' WHERE id = {$prod['id']};\n";
    }
    echo "</pre>";
}

function sugerirNome($nome, $marca) {
    $marcaLower = strtolower($marca);
    
    if (strpos($marcaLower, 'churrasqueira') !== false) {
        return "Grelha {$nome}";
    } elseif (strpos($marcaLower, 'chapa') !== false) {
        return "Chapa {$nome}";
    } elseif (strpos($marcaLower, 'grade') !== false) {
        return "Grade {$nome}";
    }
    
    return "Produto {$nome}";
}
?>