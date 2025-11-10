<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/composer/vendor/autoload.php';

use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

ilContext::init(ilContext::CONTEXT_SCORM);
ilInitialisation::initILIAS();
global $DIC;
$jwt_raw = $_POST['JWT'] ?? $_POST['id_token'] ?? null;
$provider_id = (int)($_GET['provider_id'] ?? 0);
$ref_id      = (int)($_GET['ref_id'] ?? 0);

if (!$jwt_raw || !$provider_id) {
    echo "missing data"; exit;
}

$provider = ilLTIConsumeProvider::getInstance($provider_id);

if ($provider->getKeyType() === 'RSA_KEY') {
    $key  = $provider->getPublicKey();
    $keys = new Key($key, 'RS256');
    $data = JWT::decode($jwt_raw, $keys);
} else {
    $jwks  = file_get_contents($provider->getPublicKeyset());
    $keyset= json_decode($jwks, true);
    $keys  = JWK::parseKeySet($keyset);
    $data  = JWT::decode($jwt_raw, $keys);
}

foreach ($data->{'https://purl.imsglobal.org/spec/lti-dl/claim/content_items'} as $item) {
    $title = $item->title ?? 'LTI resource';
    $desc  = $item->description ?? '';

    $gui = new ilObjLTIConsumerGUI(0, ilObject2GUI::REPOSITORY_NODE_ID, $ref_id);
    $newObj = $gui->createNewObject('lti', $title, $desc);

    $newObj->setProviderId($provider->getId());
    $newObj->setProvider($provider);

    if (isset($item->custom) && is_object($item->custom)) {
        $customParams = [];
        foreach ($item->custom as $k => $v) {
            $customParams[] = $k . '=' . $v;
        }
        if ($customParams) {
            $newObj->setCustomParams(implode(';', $customParams));
        }
    }

    $newObj->save();
    $gui->initMetadata($newObj);
}
$obj = ilObjectFactory::getInstanceByRefId($ref_id);
$target = $obj->getType() . '_' . $ref_id;

$link = 'goto.php?target=' . $target;
$tpl = $DIC->ui()->mainTemplate();
$tpl->setOnScreenMessage('success', "Created 1 LTI resource(s).", true); // false => show now

echo <<<HTML
<!doctype html>
<html><body>
<script>
let url = "$link";
if (window.parent && window.parent !== window) {
    if (window.parent.onLtiDeepLinkDone) {
        window.parent.onLtiDeepLinkDone(url);
    }
}
</script>
<p>Content linked.</p>
</body></html>
HTML;
exit;
