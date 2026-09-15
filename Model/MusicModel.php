<?php

namespace Model;

use Exception;
use Model\Connection;
use PDO;
use PDOException;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Musicas",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "nome", type: "string"),
        new OA\Property(property: "artista", type: "string"),
        new OA\Property(property: "album", type: "string"),
        new OA\Property(property: "ano", type: "integer"),
        new OA\Property(property: "genero", type: "string"),
        new OA\Property(property: "duracao", type: "string"),
    ]
)]

#[OA\Schema(
    schema: "MusicaInput",
    required: ["titulo", "artista"],
    properties: [
        new OA\Property(property: "titulo", type: "string", example: "Imagine"),
        new OA\Property(property: "artista",type: "string", example: "Jonh Lennon"),
        new OA\Property(property: "album", type: "string", example: "Imagine"),
        new OA\Property(property: "ano", type: "integer", example: "1971"),
        new OA\Property(property: "genero", type: "string", example: "Rock")
    ]
)]

#[OA\Schema(
    schema: "MusicUpdateInput",
    properties: [
        new OA\Property(property: "titulo", type: "string", example: "Imagine"),
        new OA\Property(property: "artista", type: "string",example: "Jonh Lennon"),
        new OA\Property(property: "album", type: "string", example: "Imagine"),
        new OA\Property(property: "ano", type: "integer", example: 1971),
        new OA\Property(property: "genero", type: "string", example: "Rock")
    ]
)]

class MusicModel
{
    private PDO $db;
    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function createMusic(string $titulo, string $artista, ?string $album, ?int $ano, ?string $genero): int {
        try {
            $sql = "INSERT INTO musicas (titulo, artista, album, ano, genero) VALUES (:titulo, :artista, :album, :ano, :genero)";
            $stmt = $this->db->prepare($sql);

            $stmt = $this->bindValue(":titulo", $titulo, PDO::PARAM_STR);
            $stmt = $this->bindValue(":artista", $artista, PDO::PARAM_STR);
            $stmt = $this->bindValue(":album", $album, PDO::PARAM_STR);
            $stmt = $this->bindValue(":ano", $ano, PDO::PARAM_INT);
            $stmt = $this->bindValue(":genero", $genero, PDO::PARAM_STR);

            $stmt->execute();

            return (int) $this->db->lastInsertId();
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Error creating music: ");
        }
    }

    public function readMusic(int $id): ?array 
    {
        try {
            $sql = "SELECT id, titulo, artista, album, ano, genero FROM musicas WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Error reading music: ");
        }
    }
}
