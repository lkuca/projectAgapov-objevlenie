<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
$dataFile = '../data/jsonifail.json';

// Загрузка текущих данных
function loadAds() {
    global $dataFile;
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, json_encode([]));
    }
    return json_decode(file_get_contents($dataFile), true);
}

// Сохранение данных
function saveAds($ads) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($ads, JSON_PRETTY_PRINT));
}

// Определение метода
$method = $_SERVER['REQUEST_METHOD'];
$ads = loadAds();

switch ($method) {
    case 'GET':

        // Проверяем, если параметр 'id' передан
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            // Ищем объявление с нужным id
            foreach ($ads as $ad) {
                if ($ad['id'] == $id) {
                    echo json_encode($ad);
                    exit;
                }
            }
            // Если не нашли объявление с таким id
            http_response_code(404);
            echo json_encode(['error' => 'Ad not found']);
            exit;
        } else {
            // Сортировка, если переданы параметры sort_by и order
            if (isset($_GET['sort_by']) && isset($_GET['order'])) {
                $sortBy = $_GET['sort_by'];
                $order = $_GET['order'] === 'asc' ? SORT_ASC : SORT_DESC;

                // Сортируем объявления
                usort($ads, function ($a, $b) use ($sortBy, $order) {
                    if (!isset($a[$sortBy], $b[$sortBy])) {
                        return 0; // Если поле отсутствует
                    }

                    // Если сортируем по дате
                    if ($sortBy === 'created_at') {
                        $dateA = strtotime($a[$sortBy]);
                        $dateB = strtotime($b[$sortBy]);

                        if ($order === SORT_ASC) {
                            return $dateA < $dateB ? -1 : ($dateA > $dateB ? 1 : 0);
                        } else {
                            return $dateA > $dateB ? -1 : ($dateA < $dateB ? 1 : 0);
                        }
                    }

                    // Обычная сортировка для чисел и строк
                    if ($a[$sortBy] == $b[$sortBy]) {
                        return 0;
                    }

                    if ($order === SORT_ASC) {
                        return $a[$sortBy] < $b[$sortBy] ? -1 : 1;
                    } else {
                        return $a[$sortBy] > $b[$sortBy] ? -1 : 1;
                    }
                });
            }

            // Возвращаем все объявления (уже отсортированные, если применялась сортировка)
            echo json_encode($ads);
        }
        break;


    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['title'], $input['description'], $input['photos'], $input['price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        // Валидация данных
        $title = trim($input['title']);
        $description = trim($input['description']);
        $photos = $input['photos'];
        $price = $input['price'];

        if (strlen($title) === 0 || strlen($title) > 200) {
            http_response_code(400);
            echo json_encode(['error' => 'Title must be between 1 and 200 characters']);
            exit;
        }

        if (strlen($description) === 0 || strlen($description) > 1000) {
            http_response_code(400);
            echo json_encode(['error' => 'Description must be between 1 and 1000 characters']);
            exit;
        }

        if (!is_array($photos) || count($photos) === 0 || count($photos) > 3) {
            http_response_code(400);
            echo json_encode(['error' => 'You must provide between 1 and 3 photo links']);
            exit;
        }

        foreach ($photos as $photo) {
            if (!filter_var($photo, FILTER_VALIDATE_URL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Each photo link must be a valid URL']);
                exit;
            }
        }

        if (!is_numeric($price) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Price must be a positive number']);
            exit;
        }


        // Добавление объявления
        $newAd = [
            'id' => count($ads) + 1,
            'title' => $input['title'],
            'description' => $input['description'],
            'photos' => $input['photos'],
            'price' => $input['price'],
            'created_at' => date('Y-m-d H:i:s') // добавляем текущую дату
        ];
        $ads[] = $newAd;
        saveAds($ads);
        echo json_encode($newAd);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>