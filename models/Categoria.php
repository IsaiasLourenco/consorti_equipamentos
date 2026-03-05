<?php

declare(strict_types=1);

class Categoria
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lista todas as marcas (categorias)
     */
    public function listarMarcas(): array
    {
        $sql = "
            SELECT id, nome
            FROM categorias
            WHERE tipo = 'marca'
            ORDER BY nome ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
