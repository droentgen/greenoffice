<?php
/** Upload one image for the news body editor; answers JSON {url} or {error}. */
require __DIR__ . '/../../app/bootstrap.php';
require_login(['editor']);
header('Content-Type: application/json; charset=utf-8');
if (!is_post()) {
    http_response_code(405);
    exit(json_encode(['error' => 'ต้องส่งแบบ POST']));
}
csrf_check();
try {
    $path = save_upload('image', 'news', ['jpg', 'jpeg', 'png', 'webp']);
    if (!$path) {
        throw new RuntimeException('กรุณาเลือกรูปภาพ');
    }
    echo json_encode(['url' => upload_url($path)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $ex) {
    http_response_code(400);
    echo json_encode(['error' => $ex->getMessage()], JSON_UNESCAPED_UNICODE);
}
