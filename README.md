# 🎵 Audio Download Site

シンプルな音源ダウンロードサイト（PHP + MySQL）

## 機能

### 管理画面（/admin/）
- ✅ ログイン機能（パスワードハッシュ化）
- ✅ 新規音源アップロード（MP3のみ、最大10MB）
- ✅ タイトル・説明・公開フラグ管理
- ✅ 音源一覧表示・検索機能
- ✅ 編集機能（ファイル再アップロード可能）
- ✅ 削除機能（確認ダイアログ付き、ファイルも物理削除）

### 一般公開ページ（/)
- ✅ 公開音源一覧表示
- ✅ ブラウザ内オーディオプレイヤー
- ✅ ダウンロード機能（強制DL、ダウンロード数カウント）

## セキュリティ

- ✅ SQLインジェクション対策（プリペアドステートメント使用）
- ✅ CSRF対策（トークン検証）
- ✅ ファイルアップロード検証（拡張子、MIME、サイズ）
- ✅ ファイル名ランダム化（セキュリティ向上）
- ✅ uploads/ フォルダへの直接アクセス制限（.htaccess）
- ✅ パスワードハッシュ化（password_hash）
- ✅ セッション管理・タイムアウト

## 技術スタック

- HTML5 + CSS3 + Vanilla JavaScript
- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5（レスポンシブデザイン）

## セットアップ手順

### 1. ファイル配置
```
C:\xampp\htdocs\portfolio-php\
```

### 2. データベース作成
`http://localhost/portfolio-php/setup.php` にアクセスして、表示される SQL を MyPHPAdmin で実行

### 3. ログイン
- URL: `http://localhost/portfolio-php/admin/`
- ユーザーID: `admin`
- パスワード: `admin123`

### 4. セットアップファイル削除
本番環境では `setup.php` を削除してください。

## ファイル構成

```
portfolio-php/
├── config.php              # DB接続設定
├── functions.php           # 共通関数群
├── index.php               # 一般公開ページ
├── download.php            # ダウンロード処理
├── setup.php               # 初期セットアップ（開発時のみ）
├── database.sql            # DB作成スクリプト
├── admin/
│   ├── header.php
│   ├── footer.php
│   ├── index.php           # ログイン画面
│   ├── login.php           # ログイン処理
│   ├── logout.php          # ログアウト処理
│   ├── dashboard.php       # 管理画面メイン
│   ├── upload.php          # アップロード画面
│   ├── edit.php            # 編集画面
│   └── delete.php          # 削除処理
├── public/
│   ├── header.php          # 一般ページヘッダー
│   └── footer.php          # 一般ページフッター
├── uploads/
│   └── audio/              # MP3ファイル保存ディレクトリ
│       └── .htaccess       # アクセス制限
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── README.md
```

## API エンドポイント

### ダウンロード
```
GET /download.php?id={sound_id}
```

## 今後の拡張機能

- [ ] カテゴリ機能
- [ ] ユーザー登録・ダウンロード認証
- [ ] 再生数・ダウンロード数ランキング
- [ ] 広告機能
- [ ] API (REST)
- [ ] ログ管理

## ライセンス

MIT License

## 作成者

2026年4月1日
