<?php
declare(strict_types=1);
require_once("../vendor/composer/vendor/autoload.php");
use Firebase\JWT\JWT;

ilContext::init(ilContext::CONTEXT_LTI_PROVIDER);
ilInitialisation::initILIAS();

$dl = ilSession::get('lti_dl_ctx');
//dump($dl);exit();
if (!$dl || empty($dl['deep_link_return_url']) || empty($dl['deployment_id']) || empty($dl['iss'])) {
    dump($dl);exit();
    http_response_code(400); echo "Deep linking context missing"; exit;
}

// Get the ref_id to return (from POST override OR from saved default)
$ref_id = isset($_POST['ref_id']) ? (int)$_POST['ref_id'] : null;
$title  = trim($_POST['title'] ?? '') ?: ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));

// Build a *launchable* URL for this object (the same entry you use for normal launches)
$launchUrl = ILIAS_HTTP_PATH
    . '/lti.php?client_id=' . urlencode(CLIENT_ID)
    . '&ref_id=' . $ref_id;
// Optionally, force embedded size or pass custom params
$content_items = [[
    "type"  => "ltiResourceLink",
    "title" => $title,
    "url"   => $launchUrl,
    "custom" => [
        "ilias_ref_id" => (string)$ref_id,
    ],
]];
$clientId = $dl["consumer_key"];
// Build DL Response payload
$now = time();
//dump($dl, $content_items);exit();
$payload = [
    "iss" => $clientId,                // your tool client_id (string)
    "aud" => $dl['deployment_id'],                                     // platform issuer from request
    "iat" => $now,
    "exp" => $now + 600,
    "nonce" => $dl['nonce'] ?? bin2hex(random_bytes(8)),

    "https://purl.imsglobal.org/spec/lti/claim/message_type" => "LtiDeepLinkingResponse",
    "https://purl.imsglobal.org/spec/lti/claim/version" => "1.3.0",
    "https://purl.imsglobal.org/spec/lti/claim/deployment_id" => (string)$dl['deployment_id'],
    "https://purl.imsglobal.org/spec/lti-dl/claim/content_items" => $content_items,
];

if (!empty($dl['data'])) {
    $payload["https://purl.imsglobal.org/spec/lti-dl/claim/data"] = $dl['data']; // echo back
}

// Sign with your existing key (kid must match your JWKS)
$privateKey = ilObjLTIConsumer::getPrivateKey(); // ['key'=>PEM, 'kid'=>'...']
$jwt = JWT::encode($payload, $privateKey['key'], 'RS256', $privateKey['kid']);

// Auto-POST to the platform with field name "JWT"
$return = htmlspecialchars($dl['deep_link_return_url'], ENT_QUOTES);
echo <<<HTML
<!doctype html>
<html><body onload="document.forms[0].submit()">
  <form action="{$return}" method="POST">
    <input type="hidden" name="JWT" value="{$jwt}">
    <noscript><button type="submit">Return to LMS</button></noscript>
  </form>
</body></html>
HTML;
