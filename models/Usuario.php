<?php
// models/Usuario.php

class Usuario {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Listar todos os usuários
    public function listarTodos($limit = 50, $offset = 0, $ativo = null) {
        $sql = "SELECT id, nome, email, nivel, ativo, criado_em, atualizado_em FROM usuarios WHERE 1=1";
        $params = [];

        if ($ativo !== null) {
            $sql .= " AND ativo = ?";
            $params[] = $ativo;
        }

        $sql .= " ORDER BY nome ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        
        for ($i = 0; $i < count($params); $i++) {
            $type = ($i >= count($params) - 2) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($i + 1, $params[$i], $type);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar por ID
    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("
            SELECT id, nome, email, nivel, ativo, criado_em, atualizado_em 
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Buscar por email
    public function buscarPorEmail($email) {
        $stmt = $this->pdo->prepare("
            SELECT id, nome, email, senha, nivel, ativo, criado_em, atualizado_em 
            FROM usuarios 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Criar usuário
    public function criar($nome, $email, $senha, $nivel = 'cliente') {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, nivel, ativo)
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([$nome, $email, $senhaHash, $nivel]);
        return $this->pdo->lastInsertId();
    }

    // Atualizar usuário
    public function atualizar($id, $nome, $email, $nivel, $ativo) {
        $stmt = $this->pdo->prepare("
            UPDATE usuarios 
            SET nome = ?, email = ?, nivel = ?, ativo = ?
            WHERE id = ?
        ");
        $stmt->execute([$nome, $email, $nivel, $ativo, $id]);
        return $stmt->rowCount() > 0;
    }

    // Atualizar senha
    public function atualizarSenha($id, $senha) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("
            UPDATE usuarios SET senha = ? WHERE id = ?
        ");
        $stmt->execute([$senhaHash, $id]);
        return $stmt->rowCount() > 0;
    }

    // Excluir (desativar) usuário
    public function excluir($id) {
        $stmt = $this->pdo->prepare("
            UPDATE usuarios SET ativo = 0 WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // Contar total
    public function contarTotal($ativo = null) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE 1=1";
        $params = [];

        if ($ativo !== null) {
            $sql .= " AND ativo = ?";
            $params[] = $ativo;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Login (verifica email e senha)
    public function login($email, $senha) {
        $usuario = $this->buscarPorEmail($email);
        
        if (!$usuario || !$usuario['ativo']) {
            return false;
        }

        if (password_verify($senha, $usuario['senha'])) {
            // Remove senha do retorno
            unset($usuario['senha']);
            return $usuario;
        }

        return false;
    }
}