<?php
// controllers/RelatoriosController.php

class RelatoriosController {
    private $pdo;
    private $model;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new Relatorio($pdo);
    }

    // === Vendas por Período ===
    public function vendasPorPeriodo() {
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
        $agrupar_por = $_GET['agrupar_por'] ?? 'dia';
        
        $dados = $this->model->vendasPorPeriodo($data_inicio, $data_fim, $agrupar_por);
        
        echo json_encode([
            'status' => 'sucesso',
            'relatorio' => 'vendas_por_periodo',
            'parametros' => [
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'agrupar_por' => $agrupar_por
            ],
            'dados' => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    // === Produtos Mais Vendidos ===
    public function produtosMaisVendidos() {
        $limite = isset($_GET['limite']) ? min(100, max(1, (int)$_GET['limite'])) : 10;
        $data_inicio = $_GET['data_inicio'] ?? null;
        $data_fim = $_GET['data_fim'] ?? null;
        
        $dados = $this->model->produtosMaisVendidos($limite, $data_inicio, $data_fim);
        
        echo json_encode([
            'status' => 'sucesso',
            'relatorio' => 'produtos_mais_vendidos',
            'parametros' => [
                'limite' => $limite,
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim
            ],
            'dados' => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    // === Estoque Baixo ===
    public function estoqueBaixo() {
        $minimo = isset($_GET['minimo']) ? max(1, (int)$_GET['minimo']) : 5;
        
        $dados = $this->model->estoqueBaixo($minimo);
        
        echo json_encode([
            'status' => 'sucesso',
            'relatorio' => 'estoque_baixo',
            'parametros' => ['minimo' => $minimo],
            'dados' => $dados,
            'total' => count($dados)
        ], JSON_UNESCAPED_UNICODE);
    }

    // === Resumo Geral de Estoque ===
    public function resumoGeralEstoque() {
        $dados = $this->model->resumoGeralEstoque();
        
        echo json_encode([
            'status' => 'sucesso',
            'relatorio' => 'resumo_geral_estoque',
            'dados' => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    // === Movimentações por Usuário ===
    public function movimentacoesPorUsuario() {
        $data_inicio = $_GET['data_inicio'] ?? null;
        $data_fim = $_GET['data_fim'] ?? null;
        
        $dados = $this->model->movimentacoesPorUsuario($data_inicio, $data_fim);
        
        echo json_encode([
            'status' => 'sucesso',
            'relatorio' => 'movimentacoes_por_usuario',
            'parametros' => [
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim
            ],
            'dados' => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    // === Produtos por Marca ===
    public function produtosPorMarca() {
        $dados = $this->model->produtosPorMarca();
        
        echo json_encode([
            'status' => 'sucesso',
            'relatorio' => 'produtos_por_marca',
            'dados' => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    // === Receita por Período (Consolidado) ===
    public function receitaPorPeriodo() {
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $dados = $this->model->receitaPorPeriodo($data_inicio, $data_fim);
        
        echo json_encode([
            'status' => 'sucesso',
            'relatorio' => 'receita_por_periodo',
            'parametros' => [
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim
            ],
            'dados' => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    // === Dashboard Consolidado (Todos os resumos) ===
    public function dashboard() {
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $dashboard = [
            'resumo_estoque' => $this->model->resumoGeralEstoque(),
            'receita_periodo' => $this->model->receitaPorPeriodo($data_inicio, $data_fim),
            'produtos_mais_vendidos' => $this->model->produtosMaisVendidos(5, $data_inicio, $data_fim),
            'estoque_baixo' => $this->model->estoqueBaixo(5),
            'produtos_por_marca' => $this->model->produtosPorMarca()
        ];
        
        echo json_encode([
            'status' => 'sucesso',
            'relatorio' => 'dashboard_consolidado',
            'parametros' => [
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim
            ],
            'dados' => $dashboard
        ], JSON_UNESCAPED_UNICODE);
    }
}