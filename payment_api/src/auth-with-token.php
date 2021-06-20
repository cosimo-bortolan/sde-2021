<?php

require __DIR__ . '/../vendor/autoload.php';

\SatispayGBusiness\Api::setSandbox(true);

$authentication = \SatispayGBusiness\Api::authenticateWithToken("NJNGYP");

$publicKey = $authentication->publicKey;
$privateKey = $authentication->privateKey;
$keyId = $authentication->keyId;

file_put_contents("../config/satispay_authentication.json", json_encode([
  "public_key" => $publicKey,
  "private_key" => $privateKey,
  "key_id" => $keyId
], JSON_PRETTY_PRINT));
