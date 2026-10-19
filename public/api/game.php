<?php
/**
 * POST /api/game.php  { "answers": [2,1,0,2] }  → saves one Green Check-in result,
 * returns the updated participation numbers.
 */
require __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_post()) {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}
$token = $_SERVER['HTTP_X_CSRF'] ?? '';
if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'error' => 'csrf']);
    exit;
}

$body = json_decode(file_get_contents('php://input') ?: '', true);
$a = $body['answers'] ?? null;
$valid = is_array($a) && count($a) === 4;
if ($valid) {
    foreach ($a as $v) {
        if (!is_int($v) || $v < 0 || $v > 2) {
            $valid = false;
        }
    }
}
if (!$valid) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'invalid']);
    exit;
}

// One saved result per visitor session every 10 minutes (keeps the numbers honest).
$last = $_SESSION['game_saved_at'] ?? 0;
if (time() - $last > 600) {
    q('INSERT INTO game_responses (a_elec, a_plastic, a_paper, a_commute, score) VALUES (?,?,?,?,?)', [$a[0], $a[1], $a[2], $a[3], array_sum($a)]);
    $_SESSION['game_saved_at'] = time();
}

$stats = game_stats();
echo json_encode(['ok' => true, 'players' => $stats['players'], 'topics' => $stats['topics']], JSON_UNESCAPED_UNICODE);
