<?php
declare(strict_types=1);
require_once("../vendor/composer/vendor/autoload.php");
use Firebase\JWT\JWT;

ilContext::init(ilContext::CONTEXT_LTI_PROVIDER);
ilInitialisation::initILIAS();

$dl = ilSession::get('lti_dl_ctx');

if (!$dl || empty($dl['deep_link_return_url']) || empty($dl['deployment_id']) || empty($dl['iss'])) {
    http_response_code(400); echo "Deep linking context missing"; exit;
}


$ref_ids = isset($_POST['ref_ids']) ? $_POST['ref_ids'] : null;

$launchUrl = ILIAS_HTTP_PATH
    . '/lti.php?client_id=' . urlencode(CLIENT_ID);

$content_items = [];

foreach ($ref_ids as $ref_id) {
    $obj = ilObjectFactory::getInstanceByRefId((int)$ref_id);
    $title  = trim($_POST['title'] ?? '') ?: $obj->getTitle();
    $description = trim($_POST['description'] ?? '') ?: $obj->getDescription();
    $content_items[] = [
        "type"  => "ltiResourceLink",
        "title" => $title,
        "description" => $description,
        "url"   => $launchUrl,
        "custom" => [
            "ilias_ref_id" => (string)$ref_id,
            "id" => (string)$ref_id
        ],
    ];
}
$clientId = $dl["consumer_key"];
$now = time();
$payload = [
    "iss" => $clientId,
    "aud" => [$dl['platform_id']],
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
$privateKey = ilObjLTIConsumer::getPrivateKey(); // ['key'=>PEM, 'kid'=>'...']
$jwt = JWT::encode($payload, $privateKey['key'], 'RS256', $privateKey['kid']);
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
