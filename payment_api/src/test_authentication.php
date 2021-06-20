<?php

require __DIR__ . '/../vendor/autoload.php';

\SatispayGBusiness\Api::setSandbox(true);

$authData = json_decode(file_get_contents("..config/satispay_authentication.json"));

\SatispayGBusiness\Api::setPublicKey($authData->public_key);
\SatispayGBusiness\Api::setPrivateKey($authData->private_key);
\SatispayGBusiness\Api::setKeyId($authData->key_id);

echo $authData->private_key."\n";

$body = "{
  \"flow\": \"MATCH_CODE\",
  \"amount_unit\": 100,
  \"currency\": \"EUR\"
}";

$digest = base64_encode(hash("sha256", $body, true));
echo $digest."\n";

$string = "(request-target): post /wally-services/protocol/tests/signature
host: staging.authservices.satispay.com
date: Mon, 18 Mar 2019 15:10:24 +0000
digest: SHA-256=$digest";

echo $string."\n";

openssl_sign($string, $signatureRaw, $authData->private_key, OPENSSL_ALGO_SHA256);
$signature = base64_encode($signatureRaw);
echo $signature."\n";

$authorizationHeader = "Signature keyId=\"$authData->key_id\", algorithm=\"rsa-sha256\", headers=\"(request-target) host date digest\", signature=\"$signature\"";
echo $authorizationHeader."\n";
