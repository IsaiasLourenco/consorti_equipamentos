<?php
// models/Relatorio.php

class Relatorio {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // === RELATÓRIO 1: Vendas por Período (COM LUCRO E DESCONTO) ===
    public function vendasPorPeriodo($data_inicio, $data_fim, $agrupar_por = 'dia') {
        $formato = [
            'dia' => '%Y-%m-%d',
            'mes' => '%Y-%m',
            'ano' => '%Y'
        ];
        
        $sql = "
            SELECT 
                DATE_FORMAT(criado_em, '{$formato[$agrupar_por]}') as periodo,
                COUNT(*) as total_vendas,
                SUM(quantidade) as total_itens,
                SUM(total_venda) as receita_bruta,
                SUM(desconto) as total_descontos,
                SUM(lucro) as lucro_liquido,
                SUM(total_venda - desconto) as receita_liquida
            FROM movimentacoes mov
            WHERE tipo = 'saida' 
            AND criado_em BETWEEN ? AND ?
            GROUP BY periodo
            ORDER BY periodo ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$data_inicio, $data_fim]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === RELATÓRIO 2: Produtos Mais Vendidos (COM LUCRO POR PRODUTO) ===
    public function produtosMaisVendidos($limite = 10, $data_inicio = null, $data_fim = null) {
        $sql = "
            SELECT 
                p.id,
                p.nome,
                p.sku,
                m.nome as marca_nome,
                p.preco_custo,
                p.preco_venda,
                SUM(mov.quantidade) as total_vendido,
                SUM(mov.total_venda) as receita_bruta,
                SUM(mov.desconto) as total_descontos,
                SUM(mov.lucro) as lucro_total,
                p.estoque as estoque_atual
            FROM movimentacoes mov
            INNER JOIN produtos p ON mov.produto_id = p.id
            INNER JOIN marcas m ON p.marca_id = m.id
            WHERE mov.tipo = 'saida'
        ";
        
        $params = [];
        
        if ($data_inicio && $data_fim) {
            $sql .= " AND mov.criado_em BETWEEN ? AND ?";
            $params[] = $data_inicio;
            $params[] = $data_fim;
        }
        
        $sql .= "
            GROUP BY p.id, p.nome, p.sku, m.nome, p.preco_custo, p.preco_venda, p.estoque
            ORDER BY lucro_total DESC
            LIMIT ?
        ";
        $params[] = $limite;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === RELATÓRIO 3: Estoque Baixo ===
    public function estoqueBaixo($minimo = 5) {
        $sql = "
            SELECT 
                p.id,
                p.nome,
                p.sku,
                p.estoque,
                p.preco_custo,
                p.preco_venda,
                (p.preco_venda - p.preco_custo) as lucro_unitario,
                CASE 
                    WHEN p.preco_custo > 0 THEN ROUND(((p.preco_venda - p.preco_custo) / p.preco_venda) * 100, 1)
                    ELSE 0
                END as margem_percentual,
                m.nome as marca_nome,
                CASE 
                    WHEN p.estoque = 0 THEN 'Esgotado'
                    WHEN p.estoque <= ? THEN 'Crítico'
                    ELSE 'Baixo'
                END as status_estoque
            FROM produtos p
            INNER JOIN marcas m ON p.marca_id = m.id
            WHERE p.ativo = 1 AND p.estoque <= ?
            ORDER BY p.estoque ASC, lucro_unitario DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$minimo, $minimo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === RELATÓRIO 4: Resumo Geral de Estoque (COM LUCRO POTENCIAL) ===
    public function resumoGeralEstoque() {
        $sql = "
            SELECT 
                COUNT(*) as total_produtos,
                SUM(estoque) as total_itens_estoque,
                SUM(estoque * preco_custo) as valor_total_custo,
                SUM(estoque * preco_venda) as valor_total_venda,
                SUM(estoque * (preco_venda - preco_custo)) as lucro_potencial,
                SUM(CASE WHEN estoque = 0 THEN 1 ELSE 0 END) as produtos_esgotados,
                SUM(CASE WHEN estoque <= 5 THEN 1 ELSE 0 END) as produtos_estoque_baixo,
                (SELECT COUNT(*) FROM marcas) as total_marcas,
                (SELECT COUNT(*) FROM produtos WHERE estoque > 0) as produtos_disponiveis
            FROM produtos
            WHERE ativo = 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // === RELATÓRIO 5: Movimentações por Usuário (COM LUCRO POR VENDEDOR) ===
    public function movimentacoesPorUsuario($data_inicio = null, $data_fim = null) {
        $sql = "
            SELECT 
                u.id,
                u.nome,
                u.email,
                u.nivel,
                COUNT(mov.id) as total_movimentacoes,
                SUM(CASE WHEN mov.tipo = 'entrada' THEN mov.quantidade ELSE 0 END) as total_entradas,
                SUM(CASE WHEN mov.tipo = 'saida' THEN mov.quantidade ELSE 0 END) as total_saidas,
                SUM(CASE WHEN mov.tipo = 'saida' THEN mov.lucro ELSE 0 END) as lucro_total_gerado,
                SUM(CASE WHEN mov.tipo = 'saida' THEN mov.desconto ELSE 0 END) as descontos_concedidos
            FROM usuarios u
            LEFT JOIN movimentacoes mov ON u.id = mov.usuario_id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($data_inicio && $data_fim) {
            $sql .= " AND mov.criado_em BETWEEN ? AND ?";
            $params[] = $data_inicio;
            $params[] = $data_fim;
        }
        
        $sql .= "
            GROUP BY u.id, u.nome, u.email, u.nivel
            ORDER BY lucro_total_gerado DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === RELATÓRIO 6: Produtos por Marca (COM LUCRO POR MARCA) ===
    public function produtosPorMarca() {
        $sql = "
            SELECT 
                m.id,
                m.nome,
                COUNT(p.id) as total_produtos,
                SUM(p.estoque) as total_estoque,
                SUM(p.estoque * p.preco_custo) as valor_custo_estoque,
                SUM(p.estoque * p.preco_venda) as valor_venda_estoque,
                SUM(p.estoque * (p.preco_venda - p.preco_custo)) as lucro_potencial_estoque,
                AVG(CASE WHEN p.preco_custo > 0 THEN ((p.preco_venda - p.preco_custo) / p.preco_venda) * 100 ELSE 0 END) as margem_media
            FROM marcas m
            LEFT JOIN produtos p ON m.id = p.marca_id AND p.ativo = 1
            GROUP BY m.id, m.nome
            ORDER BY lucro_potencial_estoque DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === RELATÓRIO 7: Receita por Período (COM LUCRO E DESCONTO DETALHADO) ===
    public function receitaPorPeriodo($data_inicio, $data_fim) {
        $sql = "
            SELECT 
                COUNT(*) as total_vendas,
                SUM(mov.quantidade) as total_itens_vendidos,
                SUM(mov.total_venda + mov.desconto) as receita_bruta,
                SUM(mov.desconto) as total_descontos,
                SUM(mov.total_venda) as receita_liquida,
                SUM(mov.quantidade * p.preco_custo) as custo_total,
                SUM(mov.lucro) as lucro_liquido,
                AVG(mov.total_venda) as ticket_medio,
                AVG(mov.lucro) as lucro_medio_por_venda,
                CASE 
                    WHEN SUM(mov.total_venda + mov.desconto) > 0 
                    THEN ROUND((SUM(mov.lucro) / SUM(mov.total_venda + mov.desconto)) * 100, 2)
                    ELSE 0
                END as margem_media_percentual
            FROM movimentacoes mov
            INNER JOIN produtos p ON mov.produto_id = p.id
            WHERE mov.tipo = 'saida'
            AND mov.criado_em BETWEEN ? AND ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$data_inicio, $data_fim]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // === RELATÓRIO 8: Ranking de Lucro por Produto ===
    public function rankingLucroPorProduto($limite = 20, $data_inicio = null, $data_fim = null) {
        $sql = "
            SELECT 
                p.id,
                p.nome,
                p.sku,
                m.nome as marca_nome,
                p.preco_custo,
                p.preco_venda,
                (p.preco_venda - p.preco_custo) as lucro_unitario,
                CASE 
                    WHEN p.preco_custo > 0 THEN ROUND(((p.preco_venda - p.preco_custo) / p.preco_venda) * 100, 1)
                    ELSE 0
                END as margem_percentual,
                SUM(mov.quantidade) as total_vendido,
                SUM(mov.lucro) as lucro_total_real,
                SUM(mov.desconto) as descontos_concedidos
            FROM produtos p
            INNER JOIN marcas m ON p.marca_id = m.id
            LEFT JOIN movimentacoes mov ON p.id = mov.produto_id AND mov.tipo = 'saida'
        ";
        
        $params = [];
        
        if ($data_inicio && $data_fim) {
            $sql .= " WHERE mov.criado_em BETWEEN ? AND ?";
            $params = [$data_inicio, $data_fim];
        }
        
        $sql .= "
            GROUP BY p.id, p.nome, p.sku, m.nome, p.preco_custo, p.preco_venda
            ORDER BY lucro_total_real DESC
            LIMIT ?
        ";
        $params[] = $limite;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === RELATÓRIO 9: Descontos Concedidos por Período ===
    public function descontosPorPeriodo($data_inicio, $data_fim, $agrupar_por = 'dia') {
        $formato = [
            'dia' => '%Y-%m-%d',
            'mes' => '%Y-%m',
            'ano' => '%Y'
        ];
        
        $sql = "
            SELECT 
                DATE_FORMAT(criado_em, '{$formato[$agrupar_por]}') as periodo,
                COUNT(*) as total_vendas,
                SUM(desconto) as total_descontos,
                SUM(total_venda) as receita_liquida,
                CASE 
                    WHEN SUM(total_venda + desconto) > 0 
                    THEN ROUND((SUM(desconto) / SUM(total_venda + desconto)) * 100, 2)
                    ELSE 0
                END as percentual_desconto_medio,
                SUM(lucro) as lucro_liquido
            FROM movimentacoes
            WHERE tipo = 'saida'
            AND desconto > 0
            AND criado_em BETWEEN ? AND ?
            GROUP BY periodo
            ORDER BY periodo ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$data_inicio, $data_fim]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === Log de Acesso ao Relatório ===
    public function logAcesso($usuario_id, $tipo_relatorio, $parametros = []) {
        $stmt = $this->pdo->prepare("
            INSERT INTO relatorios_log (usuario_id, tipo_relatorio, parametros)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $usuario_id,
            $tipo_relatorio,
            json_encode($parametros, JSON_UNESCAPED_UNICODE)
        ]);
        return $this->pdo->lastInsertId();
    }
}