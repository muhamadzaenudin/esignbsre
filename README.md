# E-Sign BSRE PHP
Package untuk penggunaan API E-Sign dari BSSN dengan bahasa PHP

## Penggunaan
### 1. Installasi
```bash
composer require muhamadzaenudin/esignbsre
```

### 2. Konfigurasi

```bash
 <?php
  require 'vendor/autoload.php';

  $baseUrl = 'api-bsre.bssn.go.id';
  $username = 'username';
  $password = 'password';
  $nik = '1234567812345678';
  $passphrase = 'passphrase';

  $configServer = [
      'base_url' => $baseUrl,
      'username' => $username,
      'password' => $password,
  ];

  $configSign = [
      'nik' => $nik,
      'passphrase' => $passphrase,
      'page' => 1,
      'linkQR' => 'https://api-bsre.bssn.go.id/',
      'xAxis' => '410.21347882534775',
      'yAxis' => '141.94238021638333',
      'width' => '552.1558590417311',
      'height' => '191.14907202472952',
      'imageTTD' =>  'image.png',
      'tag_koordinat' => '#',
      'reason' => 'Dokumen ini ditandatangani secara elektronik',
      'location' => 'Jakarta Indonesia',
      'file' =>  __DIR__ . '/example.pdf',
      'filename' => date('YmdHis') . '_example_signed',
      'saveTo' => __DIR__ . '/',
  ];

  $configVerify = [
      'signed_file' =>  __DIR__ . '/20250722142558_example_signed.pdf',
  ];

  $esign = new Muhamadzaenudin\Esignbsre\Esign($configServer);

  // cek nik user
  $response = $esign->statusUser($nik);

  // sign
  $response = $esign
      ->setType('invisible')
      ->sign($configSign);

  // verify
  $response = $esign
      ->verify($configVerify);

  // respon dalam bentuk json
  echo $response->toJson();

```
    

