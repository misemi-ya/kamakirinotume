<?php
// --- ▼▼▼ 設定項目 ▼▼▼ ---

// 1. Googleから取得した「シークレットキー」をここに貼り付ける
$secret_key = '6LcBOqwrAAAAAJvu92FyIrUFLHxfVfCeQA8v8Pyw';

// 2. 認証成功後に移動させたいページのURLを指定する
$next_page_url = 'https://kamakiri-server.f5.si/server-web/index.html';

// --- ▲▲▲ 設定はここまで ▲▲▲ ---


// --- ▼▼▼ これより下は原則変更不要 ▼▼▼ ---

// サーバーからの返事をJSON形式にすると宣言
header('Content-Type: application/json');

// POSTリクエスト以外は処理を中断
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// フロントエンドから'token'が送られてきていない場合は処理を中断
if (!isset($_POST['token'])) {
    echo json_encode(['success' => false, 'message' => 'reCAPTCHA token not found.']);
    exit;
}

$token = $_POST['token'];

// GoogleのAPIに問い合わせるための準備
$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = [
    'secret'   => $secret_key,
    'response' => $token,
    'remoteip' => $_SERVER['REMOTE_ADDR'] // ユーザーのIPアドレス
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];
$context  = stream_context_create($options);
// GoogleのAPIにリクエストを送信し、結果を受け取る
$response_json = file_get_contents($url, false, $context);

if ($response_json === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to communicate with Google reCAPTCHA server.']);
    exit;
}

// 受け取ったJSON形式の結果をPHPで扱えるように変換
$result = json_decode($response_json);

// 結果を判断して、フロントエンドに返す情報を組み立てる
if ($result->success) {
    // reCAPTCHAの認証に成功した場合
    echo json_encode([
        'success' => true, 
        'redirectUrl' => https://kamakiri-server.f5.si/server-web/index.html // 成功したので、移動先のURLを伝える
    ]);
} else {
    // reCAPTCHAの認証に失敗した場合
    echo json_encode([
        'success' => false, 
        'message' => 'reCAPTCHA verification failed.'
    ]);
}
?>
