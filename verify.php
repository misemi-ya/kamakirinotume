<?php
// ヘッダーを設定して、JSON形式で応答することを伝える
header('Content-Type: application/json');

// --- 設定項目 ---
// ステップ1で取得した「シークレットキー」をここに設定する
$secret_key = '6LfZOawrAAAAAJXEs4Hq522LZ_vK9LWBGkkTzISk';
// ----------------

// フロントエンドから送られてきたトークンを取得
// 実際にはPOSTリクエストで受け取るのが一般的
// $token = $_POST['token']; 
// このデモでは仮のトークンを使います（実際にはフロントから受け取ってください）
$token = '（フロントエンドから送られてきたトークン）'; // この行は実際の実装では削除

// GoogleのAPIに検証をリクエスト
$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = array(
    'secret'   => $secret_key,
    'response' => $token,
    'remoteip' => $_SERVER['REMOTE_ADDR'] // ユーザーのIPアドレス
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);

$context  = stream_context_create($options);
$response_json = file_get_contents($url, false, $context);

// Googleからの応答をデコード
$result = json_decode($response_json);

// 結果をフロントエンドに返す
if ($result->success) {
    // 成功した場合
    // スコアがしきい値（例: 0.5）以上かを確認
    if ($result->score >= 0.5) {
        // 人間らしいと判定
        echo json_encode(['status' => 'success', 'message' => '認証成功！あなたは人間です。', 'score' => $result->score]);
    } else {
        // ボットの可能性が高いと判定
        echo json_encode(['status' => 'failure', 'message' => 'ボットの可能性があります。', 'score' => $result->score]);
    }
} else {
    // 認証自体に失敗した場合 (トークンが不正など)
    echo json_encode(['status' => 'error', 'message' => 'reCAPTCHAの認証に失敗しました。', 'errors' => $result->{'error-codes'}]);
}

?>