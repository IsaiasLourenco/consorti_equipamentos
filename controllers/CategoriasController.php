<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Categoria.php';

class CategoriasController
{
    private Categoria $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new Categoria($pdo);
    }

    private function resposta(array $dados, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * MARCAS (categorias tipo = marca)
     */
    public function marcas(): void
    {
        try {
            $lista = $this->model->listarMarcas();

            $this->resposta([
                'status' => 'ok',
                'total'  => count($lista),
                'marcas' => $lista
            ]);
        } catch (Throwable $e) {
            $this->resposta([
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ], 500);
        }
    }
}
