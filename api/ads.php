<?php
require __DIR__ . '/../db/database.php';

header('Content-Type: application/json');

$db = getDatabaseConnection();

// Определяем метод API
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('?', $_SERVER['REQUEST_URI'], 2)[0];

switch ($method) {
    case 'GET':
        if ($path === '/ads') {
            getAds($db);
        } elseif (preg_match('/^\/ads\/(\d+)$/', $path, $matches)) {
            getAd($db, $matches[1]);
        }
        break;

    case 'POST':
        if ($path === '/ads') {
            createAd($db);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function getAds($db) {
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc';
    $offset = ($page - 1) * 10;

    $sortField = 'created_at';
    $sortOrder = 'DESC';
    if ($sort === 'price_asc') {
        $sortField = 'price';
        $sortOrder = 'ASC';
    } elseif ($sort === 'price_desc') {
        $sortField = 'price';
        $sortOrder = 'DESC';
    } elseif ($sort === 'created_at_asc') {
        $sortField = 'created_at';
        $sortOrder = 'ASC';
    }

    $stmt = $db->prepare("SELECT id, title, price, SUBSTRING_INDEX(photos, ',', 1) AS main_photo FROM ads ORDER BY $sortField $sortOrder LIMIT 10 OFFSET ?");
    $stmt->execute([$offset]);

    echo json_encode($stmt->fetchAll());
}

function getAd($db, $id) {
    $fields = isset($_GET['fields']) ? explode(',', $_GET['fields']) : [];
    $includeDescription = in_array('description', $fields);
    $includeAllPhotos = in_array('photos', $fields);

    $stmt = $db->prepare("SELECT id, title, price, SUBSTRING_INDEX(photos, ',', 1) AS main_photo FROM ads WHERE id = ?");
    $stmt->execute([$id]);
    $ad = $stmt->fetch();

    if (!$ad) {
        http_response_code(404);
        echo json_encode(['error' => 'Ad not found']);
        return;
    }

    if ($includeDescription) {
        $ad['description'] = $db->query("SELECT description FROM ads WHERE id = $id")->fetchColumn();
    }

    if ($includeAllPhotos) {
        $ad['photos'] = explode(',', $db->query("SELECT photos FROM ads WHERE id = $id")->fetchColumn());
    }

    echo json_encode($ad);
}

function createAd($db) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['title'], $data['price'], $data['photos'], $data['description'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }

    if (strlen($data['title']) > 200 || strlen($data['description']) > 1000 || count(explode(',', $data['photos'])) > 3) {
        http_response_code(400);
        echo json_encode(['error' => 'Validation error']);
        return;
    }

    $stmt = $db->prepare("INSERT INTO ads (title, description, photos, price, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$data['title'], $data['description'], implode(',', $data['photos']), $data['price']]);

    echo json_encode(['id' => $db->lastInsertId(), 'status' => 'success']);
}