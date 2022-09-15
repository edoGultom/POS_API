## Runing app

- php init, pilih mode, dev atau production
- setup koneksi database di `config/db.php` baru jalankan cli `./yii migrate `
- running app `./yii serve`

## Documentation

untuk documentation penggunaan api, silahkan buka postman dan lakukan import file dari root project dengan nama `doc-yii2-api-micro.postman_collection.json`
langkah pertama lakukan request token, baru lakukan request api dengan catatan access token yang di dapat tadi di masukkan sebagai headers:authorization `Bearer access_token`
