<?php
// models/Movimentacao.php

class Movimentacao {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Listar todas as movimentações (com filtros)
    public function listarTodos($limit = 50, $offset = 0, $filtros = []) {
        $sql = "
            SELECT 
                mov.id,
                mov.tipo,
                mov.quantidade,
                mov.estoque_anterior,
                mov.estoque_atual,
                mov.observacao,
                mov.origem,
                mov.criado_em,
                p.id as produto_id,
                p.nome as produto_nome,
                p.sku as produto_sku,
                u.id as usuario_id,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM movimentacoes mov
            INNER JOIN produtos p ON mov.produto_id = p.id
            LEFT JOIN usuarios u ON mov.usuario_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filtros['produto_id'])) {
            $sql .= " AND mov.produto_id = ?";
            $params[] = $filtros['produto_id'];
        }
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND mov.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND mov.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND mov.criado_em >= ?";
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND mov.criado_em <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        $sql .= " ORDER BY mov.criado_em DESC LIMIT ? OFFSET ?";
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
            SELECT 
                mov.id,
                mov.tipo,
                mov.quantidade,
                mov.estoque_anterior,
                mov.estoque_atual,
                mov.observacao,
                mov.origem,
                mov.criado_em,
                p.id as produto_id,
                p.nome as produto_nome,
                p.sku as produto_sku,
                u.id as usuario_id,
                u.nome as usuario_nome
            FROM movimentacoes mov
            INNER JOIN produtos p ON mov.produto_id = p.id
            LEFT JOIN usuarios u ON mov.usuario_id = u.id
            WHERE mov.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Criar movimentação (e atualiza estoque do produto)
    public function criar($produto_id, $tipo, $quantidade, $usuario_id = null, $observacao = null, $origem = 'manual') {
        // Inicia transação
        $this->pdo->beginTransaction();
        
        try {
            // Busca estoque atual do produto
            $stmt = $this->pdo->prepare("SELECT estoque FROM produtos WHERE id = ?");
            $stmt->execute([$produto_id]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$produto) {
                throw new Exception("Produto não encontrado");
            }
            
            $estoque_anterior = $produto['estoque'];
            
            // Calcula novo estoque
            if ($tipo === 'entrada') {
                $estoque_atual = $estoque_anterior + $quantidade;
            } elseif ($tipo === 'saida') {
                $estoque_atual = $estoque_anterior - $quantidade;
                if ($estoque_atual < 0) {
                    throw new Exception("Estoque insuficiente para esta saída");
                }
            } elseif ($tipo === 'ajuste') {
                $estoque_atual = $quantidade; // Ajuste define o valor direto
            } else {
                $estoque_atual = $estoque_anterior - $quantidade; // pedido = saída
            }
            
            // Registra movimentação
            $stmt = $this->pdo->prepare("
                INSERT INTO movimentacoes (produto_id, usuario_id, tipo, quantidade, estoque_anterior, estoque_atual, observacao, origem)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $produto_id,
                $usuario_id,
                $tipo,
                $quantidade,
                $estoque_anterior,
                $estoque_atual,
                $observacao,
                $origem
            ]);
            
            $movimentacao_id = $this->pdo->lastInsertId();
            
            // Atualiza estoque do produto
            $stmt = $this->pdo->prepare("
                UPDATE produtos 
                SET estoque = ?, ultimo_movimento = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$estoque_atual, $produto_id]);
            
            // Commit da transação
            $this->pdo->commit();
            
            return $movimentacao_id;
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Contar total
    public function contarTotal($filtros = []) {
        $sql = "SELECT COUNT(*) FROM movimentacoes WHERE 1=1";
        $params = [];
        
        if (!empty($filtros['produto_id'])) {
            $sql .= " AND produto_id = ?";
            $params[] = $filtros['produto_id'];
        }
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND tipo = ?";
            $params[] = $filtros['tipo'];
        }
        
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND criado_em >= ?";
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND criado_em <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Resumo de movimentações por produto
    public function resumoPorProduto($produto_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                produto_id,
                SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END) as total_entradas,
                SUM(CASE WHEN tipo = 'saida' THEN quantidade ELSE 0 END) as total_saidas,
                COUNT(*) as total_movimentacoes
            FROM movimentacoes
            WHERE produto_id = ?
            GROUP BY produto_id
        ");
        $stmt->execute([$produto_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}