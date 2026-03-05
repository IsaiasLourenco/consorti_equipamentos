<?php
// controllers/MovimentacoesController.php

class MovimentacoesController {
    private $pdo;
    private $model;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new Movimentacao($pdo);
    }

    // Listar todas
    public function listarTodos() {
        $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $limite = isset($_GET['limite']) ? min(100, max(1, (int)$_GET['limite'])) : 50;
        
        $filtros = [
            'produto_id' => isset($_GET['produto_id']) ? (int)$_GET['produto_id'] : null,
            'tipo' => isset($_GET['tipo']) ? $_GET['tipo'] : null,
            'usuario_id' => isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null,
            'data_inicio' => isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null,
            'data_fim' => isset($_GET['data_fim']) ? $_GET['data_fim'] : null,
        ];
        
        // Remove filtros vazios
        $filtros = array_filter($filtros, function($v) { return $v !== null && $v !== ''; });
        
        $offset = ($pagina - 1) * $limite;
        
        $movimentacoes = $this->model->listarTodos($limite, $offset, $filtros);
        $total = $this->model->contarTotal($filtros);
        
        echo json_encode([
            'status' => 'sucesso',
            'dados' => $movimentacoes,
            'filtros' => $filtros,
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
        $movimentacao = $this->model->buscarPorId($id);
        
        if (!$movimentacao) {
            http_response_code(404);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Movimentação não encontrada'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        echo json_encode(['status' => 'sucesso', 'dados' => $movimentacao], JSON_UNESCAPED_UNICODE);
    }

    // Criar movimentação
    public function criar() {
        $dados = json_decode(file_get_contents('php://input'), true);
        
        if (!$dados || empty($dados['produto_id']) || empty($dados['tipo']) || !isset($dados['quantidade'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Campos obrigatórios: produto_id, tipo, quantidade'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Valida tipo
        $tipos_validos = ['entrada', 'saida', 'ajuste', 'pedido'];
        if (!in_array($dados['tipo'], $tipos_validos)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Tipo inválido. Permitidos: ' . implode(', ', $tipos_validos)
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Valida quantidade
        if (!is_numeric($dados['quantidade']) || $dados['quantidade'] <= 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Quantidade deve ser maior que zero'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $id = $this->model->criar(
                $dados['produto_id'],
                $dados['tipo'],
                (int)$dados['quantidade'],
                $dados['usuario_id'] ?? null,
                $dados['observacao'] ?? null,
                $dados['origem'] ?? 'manual'
            );
            
            http_response_code(201);
            echo json_encode([
                'status' => 'sucesso',
                'mensagem' => 'Movimentação registrada com sucesso',
                'id' => $id
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Erro ao registrar movimentação: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // Resumo por produto
    public function resumoPorProduto($produto_id) {
        $resumo = $this->model->resumoPorProduto($produto_id);
        
        if (!$resumo) {
            echo json_encode([
                'status' => 'sucesso',
                'dados' => [
                    'produto_id' => $produto_id,
                    'total_entradas' => 0,
                    'total_saidas' => 0,
                    'total_movimentacoes' => 0
                ]
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        echo json_encode(['status' => 'sucesso', 'dados' => $resumo], JSON_UNESCAPED_UNICODE);
    }
}