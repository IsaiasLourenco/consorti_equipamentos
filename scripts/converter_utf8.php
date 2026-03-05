<?php
// converter_utf8.php
// Converte o CSV para UTF-8 sem BOM

$csvOriginal = __DIR__ . '/produtos_limpos.csv';
$csvDestino = __DIR__ . '/produtos_limpos_utf8.csv';

if (!file_exists($csvOriginal)) {
    die("❌ Arquivo não encontrado: {$csvOriginal}");
}

echo "📁 Lendo: {$csvOriginal}\n";

// Lê o conteúdo
$conteudo = file_get_contents($csvOriginal);

if ($conteudo === false) {
    die("❌ Erro ao ler o arquivo.");
}

// Detecta encoding original
$encoding = mb_detect_encoding($conteudo, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'CP1252'], true);

// Fallback se não detectar
if (!$encoding) {
    echo "⚠️ Não foi possível detectar o encoding. Assumindo Windows-1252 (padrão Excel BR)\n";
    $encoding = 'Windows-1252';
}

echo "🔍 Encoding detectado: {$encoding}\n";

// Converte para UTF-8 (se necessário)
if ($encoding !== 'UTF-8') {
    $conteudo = mb_convert_encoding($conteudo, 'UTF-8', $encoding);
    echo "✅ Convertido de {$encoding} para UTF-8\n";
} else {
    echo "⚠️ Já está em UTF-8, apenas removendo BOM se existir\n";
}

// Remove BOM (Byte Order Mark) se existir
$bom = "\xEF\xBB\xBF";
if (substr($conteudo, 0, 3) === $bom) {
    $conteudo = substr($conteudo, 3);
    echo "🗑️ BOM removido\n";
}

// Salva o novo arquivo (sobrescreve se existir)
if (file_put_contents($csvDestino, $conteudo) === false) {
    die("❌ Erro ao salvar o arquivo: {$csvDestino}");
}

echo "💾 Arquivo salvo em: {$csvDestino}\n";
echo "📏 Tamanho: " . round(filesize($csvDestino) / 1024, 2) . " KB\n";

// Verifica se tem acentos corretos agora
if (strpos($conteudo, 'ç') !== false || strpos($conteudo, 'ã') !== false || strpos($conteudo, 'é') !== false) {
    echo "✅ Acentos detectados corretamente no arquivo!\n";
} else {
    echo "⚠️ Atenção: não encontrei acentos comuns (ç, ã, é). Pode estar correto ou o CSV original não os contém.\n";
}

echo "\n🎉 Conversão concluída! Pode prosseguir com a importação.\n";
?>