<?php
// models/Produto.php

class Produto {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function listarTodos($limit = 50, $offset = 0, $busca = null, $marca_id = null) {
        $sql = "SELECT 
                    p.id,
                    p.nome,
                    p.sku,
                    p.estoque,
                    p.preco_custo,
                    p.preco_venda,
                    p.ativo,
                    p.criado_em,
                    m.id as marca_id,
                    m.nome as marca_nome
                FROM produtos p
                INNER JOIN marcas m ON p.marca_id = m.id
                WHERE p.ativo = 1";
        
        $params = [];
        
        if ($busca) {
            $sql .= " AND (p.nome LIKE ? OR p.sku LIKE ?)";
            $params[] = "%{$busca}%";
            $params[] = "%{$busca}%";
        }
        
        if ($marca_id) {
            $sql .= " AND p.marca_id = ?";
            $params[] = $marca_id;
        }
        
        $sql .= " ORDER BY p.nome LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        
        // Bind correto dos parâmetros
        for ($i = 0; $i < count($params); $i++) {
            $type = ($i >= count($params) - 2) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($i + 1, $params[$i], $type);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                p.id, p.nome, p.sku, p.estoque,
                p.preco_custo, p.preco_venda, p.ativo, p.criado_em,
                m.id as marca_id, m.nome as marca_nome
            FROM produtos p
            INNER JOIN marcas m ON p.marca_id = m.id
            WHERE p.id = ? AND p.ativo = 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function contarTotal($busca = null, $marca_id = null) {
        $sql = "SELECT COUNT(*) FROM produtos p WHERE p.ativo = 1";
        $params = [];
        
        if ($busca) {
            $sql .= " AND (p.nome LIKE ? OR p.sku LIKE ?)";
            $params[] = "%{$busca}%";
            $params[] = "%{$busca}%";
        }
        if ($marca_id) {
            $sql .= " AND p.marca_id = ?";
            $params[] = $marca_id;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}