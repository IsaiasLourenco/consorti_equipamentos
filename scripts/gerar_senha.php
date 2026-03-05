<?php
// gerar_senha.php
// Gera hash bcrypt para uma senha simples

$senha = "123"; // ← Mude aqui se quiser outra senha
$hash = password_hash($senha, PASSWORD_DEFAULT);

echo "Senha: {$senha}\n";
echo "Hash: {$hash}\n\n";
echo "SQL para atualizar:\n";
echo "UPDATE usuarios SET senha = '{$hash}' WHERE email = 'admin@consorti.com.br';\n";
echo "UPDATE usuarios SET senha = '{$hash}' WHERE email = 'isaias.lourenco2020@outlook.com';\n";
?>