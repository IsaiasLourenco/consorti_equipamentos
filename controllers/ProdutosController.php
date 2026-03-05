<?php
// controllers/ProdutosController.php

class ProdutosController {
    private $pdo;
    private $model;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new Produto($pdo);
    }

    // Listar todos
    public function listarTodos($limite = 50, $pagina = 1, $busca = null, $marca_id = null) {
        $offset = ($pagina - 1) * $limite;
        
        $produtos = $this->model->listarTodos($limite, $offset, $busca, $marca_id);
        $total = $this->model->contarTotal($busca, $marca_id);
        
        echo json_encode([
            'status' => 'sucesso',
            'dados' => $produtos,
            'paginacao' => [
                'pagina_atual' => $pagina,
                'limite' => $limite,
                'total' => $total,
                'total_paginas' => ceil($total / $limite)
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    // Buscar por ID
    public function buscarPorId($id) {
        $produto = $this->model->buscarPorId($id);
        
        if (!$produto) {
            http_response_code(404);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Produto não encontrado'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        echo json_encode(['status' => 'sucesso', 'dados' => $produto], JSON_UNESCAPED_UNICODE);
    }

    // ✅ NOVO: Atualizar produto (apenas preços)
    public function atualizar($id) {
        $dados = json_decode(file_get_contents('php://input'), true);
        
        if (!$dados || (!isset($dados['preco_custo']) && !isset($dados['preco_venda']))) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Dados inválidos. Envie preco_custo e/ou preco_venda'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE produtos 
                SET preco_custo = COALESCE(?, preco_custo),
                    preco_venda = COALESCE(?, preco_venda),
                    atualizado_em = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([
                $dados['preco_custo'] ?? null,
                $dados['preco_venda'] ?? null,
                $id
            ]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'status' => 'sucesso',
                    'mensagem' => 'Produto atualizado com sucesso'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => 'Produto não encontrado'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}