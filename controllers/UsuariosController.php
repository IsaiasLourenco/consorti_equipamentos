<?php
// controllers/UsuariosController.php

class UsuariosController {
    private $pdo;
    private $model;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new Usuario($pdo);
    }

    // Listar todos
    public function listarTodos() {
        $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $limite = isset($_GET['limite']) ? min(100, max(1, (int)$_GET['limite'])) : 50;
        $ativo = isset($_GET['ativo']) ? (int)$_GET['ativo'] : null;
        
        $offset = ($pagina - 1) * $limite;
        
        $usuarios = $this->model->listarTodos($limite, $offset, $ativo);
        $total = $this->model->contarTotal($ativo);
        
        echo json_encode([
            'status' => 'sucesso',
            'dados' => $usuarios,
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
        $usuario = $this->model->buscarPorId($id);
        
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não encontrado'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        echo json_encode(['status' => 'sucesso', 'dados' => $usuario], JSON_UNESCAPED_UNICODE);
    }

    // Criar usuário
    public function criar() {
        $dados = json_decode(file_get_contents('php://input'), true);
        
        if (!$dados || empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Campos obrigatórios: nome, email, senha'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $id = $this->model->criar(
                $dados['nome'],
                $dados['email'],
                $dados['senha'],
                $dados['nivel'] ?? 'cliente'
            );
            
            http_response_code(201);
            echo json_encode([
                'status' => 'sucesso',
                'mensagem' => 'Usuário criado com sucesso',
                'id' => $id
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Erro ao criar usuário: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // Atualizar usuário
    public function atualizar($id) {
        $dados = json_decode(file_get_contents('php://input'), true);
        
        if (!$dados || empty($dados['nome']) || empty($dados['email'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Campos obrigatórios: nome, email'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $sucesso = $this->model->atualizar(
            $id,
            $dados['nome'],
            $dados['email'],
            $dados['nivel'] ?? 'cliente',
            $dados['ativo'] ?? 1
        );
        
        if ($sucesso) {
            echo json_encode(['status' => 'sucesso', 'mensagem' => 'Usuário atualizado'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar usuário'], JSON_UNESCAPED_UNICODE);
        }
    }

    // Excluir usuário
    public function excluir($id) {
        $sucesso = $this->model->excluir($id);
        
        if ($sucesso) {
            echo json_encode(['status' => 'sucesso', 'mensagem' => 'Usuário desativado'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir usuário'], JSON_UNESCAPED_UNICODE);
        }
    }

    // Login
    public function login() {
        $dados = json_decode(file_get_contents('php://input'), true);
        
        if (!$dados || empty($dados['email']) || empty($dados['senha'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Campos obrigatórios: email, senha'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $usuario = $this->model->login($dados['email'], $dados['senha']);
        
        if ($usuario) {
            echo json_encode([
                'status' => 'sucesso',
                'mensagem' => 'Login realizado com sucesso',
                'dados' => $usuario
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(401);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Email ou senha inválidos'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}