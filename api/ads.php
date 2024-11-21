<?php
require_once '../db/database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getAd($db, $_GET['id']);
        } else {
            getAds($db);
        }
        break;

    case 'POST':
        createAd($db);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}

function getAds($db) {
    $stmt = $db->query('SELECT * FROM ads ORDER BY created_at DESC');
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($ads);
}

function getAd($db, $id) {
    $stmt = $db->prepare('SELECT * FROM ads WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ad) {
        echo json_encode($ad);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Ad not found']);
    }
}

function createAd($db) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!validateAd($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }

    $stmt = $db->prepare('INSERT INTO ads (title, description, photos) VALUES (:title, :description, :photos)');
    $stmt->execute([
        'title' => $input['title'],
        'description' => $input['description'],
        'photos' => json_encode($input['photos']),
    ]);

    http_response_code(201);
    echo json_encode(['message' => 'Ad created successfully']);
}

function validateAd($input) {
    if (
        empty($input['title']) || strlen($input['title']) > 200 ||
        empty($input['description']) || strlen($input['description']) > 1000 ||
        empty($input['photos']) || !is_array($input['photos']) || count($input['photos']) > 3
    ) {
        return false;
    }
    return true;
}