<?php

session_start();

require_once "vendor/autoload.php";

use Model\MusicModel;
use Controller\MusicController;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[2] ?? null;

$id = $parts[3] ?? null;

header("Content-Type: application/json; charset=UTF-8");

if ($resource !== "musicas") {
    http_response_code(404);
    echo json_encode(["error" => "Rota desconhecida!"]);
    exit;
}

try {
    $musicModel = new MusicModel();
    $musicController = new MusicController($musicModel);

    $musicController->ProcessRequest(
        $_SERVER['REQUEST_METHOD'],
        $id
    );

}
  catch (\Throwable $error) {
    http_response_code(500);
    echo json_encode([
        "error" => $error->getMessage()
    ]);
}


