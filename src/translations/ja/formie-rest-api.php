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
    'Interface' => 'インターフェース',
    'Logs' => 'ログ',
    'Test' => 'テスト',

    // Permissions
    'Manage settings' => '設定を管理する',
    'Manage API keys' => 'API キーを管理',
    'Create API keys' => 'API キーを作成',
    'Edit API keys' => 'API キーを編集',
    'Revoke API keys' => 'API キーを失効',
    'View system logs' => 'システムログを表示する',
    'Download system logs' => 'システムログをダウンロードする',

    // Common
    'Name' => '名前',
    'Status' => 'ステータス',
    'Actions' => 'アクション',
    'All' => 'すべて',
    'Enable' => '有効にする',
    'Disable' => '無効にする',
    'Enabled' => '有効',
    'Disabled' => '無効',
    'Edit' => '編集',
    'Save' => '保存する',
    'Save and continue editing' => '保存して編集を続ける',
    'Set status' => 'ステータスを設定する',
    'Never' => 'なし',
    'Created at' => '作成日時',
    'Updated at' => '更新日時',

    // Controller messages
    "Couldn't save settings." => '設定を保存できませんでした。',
    'Settings saved.' => '設定を保存しました。',
    'Selected API key is not configured.' => '選択された API キーが設定されていません。',
    'API key created' => 'API キーを作成しました',
    'API key saved' => 'API キーを保存しました',
    'API key revoked' => 'API キーを失効しました',
    'Couldn’t save API key' => 'API キーを保存できませんでした',
    'Couldn’t revoke API key' => 'API キーを失効できませんでした',
    'API key not found' => 'API キーが見つかりません',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 件の API キーを失効しました} other{# 件の API キーを失効しました}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 件の API キーを有効にしました} other{# 件の API キーを有効にしました}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 件の API キーを無効にしました} other{# 件の API キーを無効にしました}}',
    'Couldn’t enable API keys' => 'API キーを有効にできませんでした',
    'Couldn’t disable API keys' => 'API キーを無効にできませんでした',
    'Couldn’t revoke API keys' => 'API キーを失効できませんでした',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => '有効なキーは、すべてのフォームまたは少なくとも 1 つの特定のフォームを許可する必要があります。',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'IP ホワイトリストのエントリが無効です : "{entry}"。単一の IP または CIDR 範囲を指定してください ( 例 : 203.0.113.5 または 192.168.1.0/24 )。',

    // Settings: General
    'General Settings' => '一般設定',

    // Settings: Interface
    'Interface Settings' => 'インターフェース設定',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'API キーはまだ作成されていません。コンシューマーごとにキーを作成して REST API へのアクセスを管理してください。',

    // Index page
    'Allowed forms' => '許可されたフォーム',
    'Signing' => '署名',
    'Expires' => '有効期限',
    'Last used' => '最終使用',
    'Expired' => '期限切れ',
    'No API keys yet.' => 'API キーはまだありません。',
    'Search API keys...' => 'API キーを検索...',
    'API key' => 'API キー',
    'API keys' => 'API キー',
    'All Forms' => 'すべてのフォーム',
    'form' => 'フォーム',
    'forms' => 'フォーム',
    'No forms allowed — this key cannot be used until you add some.' => '許可されたフォームがありません — フォームを追加するまでこのキーは使用できません。',
    'Revoke' => '失効',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'この API キーを失効してもよろしいですか？使用中の呼び出し元はただちにアクセスを失います。',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => '1 件の API キーを失効してもよろしいですか？使用中の呼び出し元はただちにアクセスを失います。この操作は元に戻せません。',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => '{count} 件の API キーを失効してもよろしいですか？使用中の呼び出し元はただちにアクセスを失います。この操作は元に戻せません。',
    'Prefix' => 'プレフィックス',
    'None' => 'なし',

    // Edit page
    'New API Key' => '新規 API キー',
    'Edit API Key' => 'API キーを編集',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'リストでこのキーを識別するための説明的なラベルです — 通常、このキーが属するコンシューマー名です。呼び出し元には公開されません。',
    'All forms (current and future)' => 'すべてのフォーム（現在および今後のもの）',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'オンの場合、このキーはすべてのフォームを読み取れます — このキーの作成後に作成されたフォームも含みます。オフの場合、以下で特定のフォームを選択してください。',
    'Specific forms' => '特定のフォーム',
    'Tick each form this key can read.' => 'このキーが読み取れる各フォームにチェックを入れてください。',
    'No forms exist yet. Create a form before this key can be useful.' => 'フォームがまだ存在しません。このキーが有効になる前にフォームを作成してください。',
    'IP whitelist' => 'IP ホワイトリスト',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => '1 行に 1 エントリ。単一の IP (<code>203.0.113.5</code>) または CIDR 範囲 (<code>192.168.1.0/24</code>)、IPv4 または IPv6 を指定してください。すべての IP を許可するには空白のままにしてください。',
    'Require signing' => '署名を必須にする',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'オンの場合、すべてのリクエストはこのキーの署名シークレットを使って計算した有効な HMAC-SHA256 署名を含む必要があります。',
    'Read submissions' => '送信データを読み取る',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'オフの場合、このキーはフォームエンドポイントのみに制限され、送信データを読み取れません。',
    'Rate limit' => 'レート制限',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'リクエストレートを 1 時間あたりのリクエスト数で制限します。デフォルト値 (100/時間) を使用するには空白のままにしてください。',
    'Valid until' => '有効期限',
    'Optional expiry datetime. Leave empty for no expiry.' => 'オプションの有効期限の日時。有効期限を設定しない場合は空白のままにしてください。',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => '無効化するとキーは削除せずに停止します。失効（削除）するとキーは完全に削除されます。',
    'Copy this API key now — it will never be shown again.' => 'この API キーを今すぐコピーしてください — このキーは二度と表示されません。',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => '{pluginName} はハッシュのみを保存します。この値を失うと、新しいキーを作成する必要があります。',
    'Copy this signing secret now — it will never be shown again.' => 'この署名シークレットを今すぐコピーしてください — 二度と表示されません。',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => '呼び出し元はこれを使って各リクエストに署名します (HMAC-SHA256)。API キーとともに安全なチャネルを通じて渡してください。',

    // Test page
    'Test API' => 'API テスト',
    'Test API Endpoints' => 'API エンドポイントのテスト',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => '設定済みのキーのいずれかを使用してローカル API にリクエストを送信し、レスポンスを確認します。',
    'Developer resources' => '開発者向けリソース',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Formie REST API を Craft の外部でテストするために、Postman コレクションと環境をダウンロードします。',
    'Download Postman collection' => 'Postman コレクションをダウンロードする',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'API キーが設定されていません。.env ファイルに FORMIE_API_KEY (および任意で FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) を設定するか、<code>php craft formie-rest-api/security/generate-key</code> ( DDEV の場合は <code>ddev craft formie-rest-api/security/generate-key</code> ) を実行してください。',
    'API Key' => 'API キー',
    'Which configured key to send.' => '送信する設定済みキーを指定します。',
    'Pasted key' => '貼り付けたキー',
    'Paste an API key to test.' => 'テストする API キーを貼り付けてください。',
    'Paste the full key (fra_...). Used for this test only — never stored.' => '完全なキー (fra_...) を貼り付けてください。このテストにのみ使用され、保存されることはありません。',
    'Signing secret' => '署名シークレット',
    'Leave empty if the key does not require signing.' => 'キーが署名を必要としない場合は空のままにしてください。',
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
    'fields (optional)' => 'fields ( 任意 )',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'テストを実行',
    'Result' => '結果',
    'Status:' => 'ステータス :',
    'Time:' => '所要時間 :',
    'Equivalent curl' => '同等の curl コマンド',
    'Response headers' => 'レスポンスヘッダー',
    'Response body' => 'レスポンスボディ',
    'Running...' => '実行中...',
    'Error:' => 'エラー :',
    'Unknown error' => '不明なエラー',
];
