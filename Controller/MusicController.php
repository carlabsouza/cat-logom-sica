<?php

namespace Controller;

use Model\MusicModel;
use Exception;

use OpenApi\Attributes as OA;

require_once __DIR__ . "/../Model/MusicModel.php";

#[OA\Info(
    version: "1.0.0",
    title: "API Rest de Músicas"
)]

#[OA\Server(
    url: "http://localhost:8000",
    description: "Servidor de desenvolvimento local da API"
)]

class MusicController
{
    public function __construct(private MusicModel $musicModel)
    {
    }

    public function ProcessRequest(string $method, ?string $id): void
    {
        header("Content-Type: application/json; charset=UTF-8");
        
        if ($id === null) {

            match ($method) {
                "GET" => $this->index(),
                "POST" => $this->create(),
                default => $this->methodNotAllowed(["GET", "POST"])
            };

            return;
        }

        match ($method) {
            "GET" => $this->show((int) $id),
            "PATCH" => $this->update((int) $id),
            "DELETE" => $this->delete((int) $id),
            default => $this->methodNotAllowed(["GET", "PATCH", "DELETE"])
        };
    }

    #[OA\Get(
        path: "/musicas",
        summary: "Lista todas as músicas",
        tags: ["Músicas"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Requisição concluída com sucesso",
                content: new OA\JsonContent(ref: "#/components/schemas/musicas")
            ),
            new Oa\Response(
                response: 404,
                description: "Erro ao listar músicas",
            )
        ]
    )]
    private function index(): void
    {
        try {

            $musicas = $this->musicModel->readAllMusics();
            http_response_code(200);
            echo json_encode($musicas);

        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode([
                "error" => $error->getMessage()
            ]);
        }
    }

    #[OA\Post(
    path: "/musicas",
    summary: "Cadastrar uma música",
    tags: ["Músicas"],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: "#/components/schemas/MusicaInput"
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Música cadastrada com sucesso"
        ),
        new OA\Response(
            response: 422,
            description: "Título e artista são obrigatórios"
        )
    ]
)]
    private function create(): void
    {
        try {

            $data = $this->readInput();

            if (empty($data["titulo"]) || empty($data["artista"])) {
                http_response_code(422);
                echo json_encode([
                    "error" => "Título e artista são obrigatórios"
                ]);

                return;
            }

            $id = $this->musicModel->createMusic(
                $data["titulo"],
                $data["artista"],
                $data["album"] ?? null,
                isset($data["ano"]) ? (int) $data["ano"] : null,
                $data["genero"] ?? null
            );

            $musica = $this->musicModel->readMusic($id);
            http_response_code(201);
            echo json_encode($musica);

        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode([
                "error" => $error->getMessage()
            ]);
        }
    }

    #[OA\Get(
        path: "/musicas/{id}",
        summary: "Obter informações de uma música",
        tags: ["Músicas"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
            ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Informações da música obtidas com sucesso",
                content: new OA\JsonContent(ref: "#/components/schemas/musicas")
            ),
            new OA\Response(
                response: 404,
                description: "Música não encontrada"
            ),
            new OA\Response(response: 500, description: "Erro interno do servidor")
        ]
    )]
    private function show(int $id): void
    {
        try {

            $musica = $this->musicModel->readMusic($id);

            if ($musica === null) {
                http_response_code(404);
                echo json_encode([
                    "error" => "Música não encontrada!"
                ]);

                return;
            }

            http_response_code(200);
            echo json_encode($musica);

        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode([
                "error" => $error->getMessage()
            ]);
        }
    }

    #[OA\Patch(
        path: "/musicas/{id}",
        summary: "Atualizar uma música",
        tags: ["Músicas"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Música atualizada com sucesso",
                    content: new OA\JsonContent(ref: "#/components/schemas/musicas")
                ),
                new OA\Response(
                    response: 404,
                    description: "Música não encontrada"
                ),
                new OA\Response(
                    response: 422,
                    description: "Tentativa de atualização com dados inválidos",
                    content: new oA\JsonContent(ref: "#/components/schemas/Error422")
                ),
                new OA\Response(response: 500, description: "Erro interno do servidor")
            ]
    )]
    private function update(int $id): void
    {
        try {

            $musica = $this->musicModel->readMusic($id);

            if ($musica === null) {
                http_response_code(404);
                echo json_encode([
                    "error" => "Música não encontrada!"
                ]);

                return;
            }

            $data = $this->readInput();

            $titulo = $data["titulo"] ?? $musica["titulo"];
            $artista = $data["artista"] ?? $musica["artista"];
            $album = $data["album"] ?? $musica["album"];
            $ano = isset($data["ano"]) ? (int) $data["ano"] : $musica["ano"];
            $genero = $data["genero"] ?? $musica["genero"];

            if (empty($titulo) || empty($artista)) {
                http_response_code(422);
                echo json_encode([
                    "error" => "Título e artista são obrigatórios"
                ]);

                return;
            }

            $this->musicModel->updateMusic($id, $titulo, $artista, $album, $ano, $genero);

            $updated = $this->musicModel->readMusic($id);
            http_response_code(200);
            echo json_encode($updated);

        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode([
                "error" => $error->getMessage()
            ]);
        }
    }

    #[OA\Delete(
        path: "/musicas/{id}",
        summary: "Excluir uma música",
        tags: ["Músicas"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
            ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Música excluída com sucesso"
            ),
            new OA\Response(
                response: 404,
                description: "Música não encontrada"
            ),
            new OA\Response(response: 500, description: "Erro interno do servidor")
        ]
    )]
    private function delete(int $id): void
    {
        try {

            $musica = $this->musicModel->readMusic($id);

            if ($musica === null) {
                http_response_code(404);
                echo json_encode([
                    "error" => "Música não encontrada!"
                ]);

                return;
            }

            $this->musicModel->deleteMusic($id);
            http_response_code(204);

        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode([
                "error" => $error->getMessage()
            ]);
        }
    }

    private function readInput(): array
    {
        $body = file_get_contents("php://input");
        $data = json_decode($body, true);
        return is_array($data) ? $data : [];
    }

    private function methodNotAllowed(array $allowed): void
    {
        header("Allow: " . implode(", ", $allowed));
        http_response_code(405);
        echo json_encode([
            "error" => "Método não permitido"
        ]);
    }
}

