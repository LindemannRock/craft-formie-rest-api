<?php
/**
 * Formie REST API translation file (Japanese)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'API キーの管理、エンドポイントの保護、プラグイン設定エリアからの Formie データ応答のテストを行います。',
    'Open Formie REST API' => 'Formie REST API を開く',
    // Navigation
    'Settings' => '設定',
    'Plugins' => 'プラグイン',
    'General' => '一般',
    'Test' => 'テスト',

    // Permissions
    'Manage settings' => '設定を管理',

    // Controller messages
    "Couldn't save settings." => '設定を保存できませんでした。',
    'Settings saved.' => '設定を保存しました。',

    // Settings: General
    'General Settings' => '一般設定',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'この値は <code>config/formie-rest-api.php</code> の <code>pluginName</code> 設定により上書きされています。',

    // Settings: Configuration warning
    'COPIED' => 'コピー済み',
    'COPY' => 'コピー',
    'Configuration Required' => '設定が必要です',
    'DDEV:' => 'DDEV :',
    'Generate separate keys per environment — never copy production keys to staging or development.' => '環境ごとに別のキーを生成してください — 本番環境のキーをステージングや開発環境にコピーしないでください。',
    'No API keys configured.' => 'API キーが設定されていません。',
    'Run one of these commands in your terminal:' => 'ターミナルで以下のいずれかのコマンドを実行してください :',
    'Standard:' => '標準 :',
    'The plugin will reject every request until at least one key is set.' => '少なくとも 1 つのキーが設定されるまで、プラグインはすべてのリクエストを拒否します。',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'これにより {keys} および対応する署名シークレットが {file} ファイルに書き込まれます。',
    'Warning:' => '警告 :',
    'error' => 'エラー',

    // Test page
    'Test API' => 'API テスト',
    'Test API Endpoints' => 'API エンドポイントのテスト',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => '設定済みのキーのいずれかを使用してローカル API にリクエストを送信し、レスポンスを確認します。',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'API キーが設定されていません。.env ファイルに FORMIE_API_KEY (および任意で FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) を設定するか、<code>ddev craft formie-rest-api/security/generate-key</code> を実行してください。',
    'API Key' => 'API キー',
    'Which configured key to send.' => '送信する設定済みキーを指定します。',
    'Endpoint' => 'エンドポイント',
    'Which REST endpoint to call.' => '呼び出す REST エンドポイントを指定します。',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'フォームまたは送信の数値 ID。',
    'Form handle' => 'フォームハンドル',
    'Form handle (the slug, not the title).' => 'フォームハンドル ( タイトルではなくスラッグ )。',
    'formHandle (optional)' => 'formHandle ( 任意 )',
    'Filter submissions to one form.' => '送信を 1 つのフォームに絞り込みます。',
    'dateFrom (optional)' => 'dateFrom ( 任意 )',
    'dateTo (optional)' => 'dateTo ( 任意 )',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'テストを実行',
    'Result' => '結果',
    'Status:' => 'ステータス :',
    'Time:' => '所要時間 :',
    'Equivalent curl' => '同等の curl コマンド',
    'Response headers' => 'レスポンスヘッダー',
    'Response body' => 'レスポンスボディ',
];
