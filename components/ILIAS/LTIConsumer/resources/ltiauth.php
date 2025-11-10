<?php
declare(strict_types=1);

require_once("../vendor/composer/vendor/autoload.php");

use Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
} else {
    $data = $_GET;
    if (isset($_GET['client_id'])) {
        unset($_GET['client_id']);
    }
}

ilInitialisation::initILIAS();
global $DIC;
$scope        = $data['scope']         ?? '';
$responseType = $data['response_type'] ?? '';
$redirectUri  = $data['redirect_uri']  ?? '';
$clientId     = $data['client_id']     ?? '';
$state        = $data['state']         ?? '';
$nonce        = $data['nonce']         ?? '';
$ltiHintRaw   = $data['lti_message_hint'] ?? '';
$loginHint    = $data['login_hint']    ?? '';

$isDlMode = false;
$hint = null;
$provider_id = 0;
$refId = 0;

if (
    $scope === 'openid' &&
    $responseType === 'id_token' &&
    $redirectUri !== '' &&
    $clientId !== ''
) {
    $provider_id = ilLTIConsumeProvider::getProviderIdFromClientId($clientId);
    $provider = ilLTIConsumeProvider::getInstance($provider_id);


    if($provider->getContentItemUrl() == $redirectUri) {
        $isDlMode = true;
        $hint = json_decode($ltiHintRaw ?? '', true);
        $ownerId = ilObjectFactory::getInstanceByRefId(224)->getOwner();
        $childRefId = ilObjLTIConsumer::getRefIdOfConsumerByDeploymentId((string)$hint['deployment_id']);
        $refId = $DIC->repositoryTree()->getParentId($childRefId);
    }

}
if ($isDlMode) {
    $now = time();
    $ctrl = $DIC->ctrl();

    $iframe_url = ilObjLTIConsumer::getPlattformId() . '/ltidlreturn.php?provider_id=' . $provider_id
        . '&ref_id=' . $refId
        . '&new_type=lti';

    $iss = ilObjLTIConsumer::getPlattformId();
    $sub = $loginHint !== '' ? $loginHint : ilCmiXapiUser::getIdentAsId($this->getProvider()->getPrivacyIdent(), $DIC->user());
    $payload = [
        'iss'   => $iss,
        'aud'   => $clientId,
        'iat'   => $now,
        'exp'   => $now + 600,
        'nonce' => $nonce ?: bin2hex(random_bytes(8)),
        'sub'   => $sub,

        'https://purl.imsglobal.org/spec/lti/claim/roles' => [
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Administrator',
            'http://purl.imsglobal.org/vocab/lis/v2/membership#ContentDeveloper',
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor',
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner',
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Mentor',
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Manager',
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Member',
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Officer',
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor',
        ],
        'https://purl.imsglobal.org/spec/lti/claim/message_type' => 'LtiDeepLinkingRequest',
        'https://purl.imsglobal.org/spec/lti/claim/version'      => '1.3.0',

    ];

    if (isset($hint['deployment_id'])) {
        $payload['https://purl.imsglobal.org/spec/lti/claim/deployment_id'] = (string) $hint['deployment_id'];
    }

    $payload['https://purl.imsglobal.org/spec/lti-dl/claim/deep_linking_settings'] = [
        'deep_link_return_url' => $iframe_url,
        'accept_types' => ['ltiResourceLink'],
        'accept_presentation_document_targets' => ['iframe', 'window'],
    ];

    $payload['https://purl.imsglobal.org/spec/lti/claim/tool_platform'] = [
        'name' => 'ILIAS',
        'version' => ILIAS_VERSION_NUMERIC ?? 'unknown',
        'product_family_code' => 'ilias',
    ];


    $pk  = ilObjLTIConsumer::getPrivateKey();
    $jwt = JWT::encode($payload, $pk['key'], 'RS256', $pk['kid'], ['kid' => $pk['kid']]);

    $redirSafe = htmlspecialchars($redirectUri, ENT_QUOTES);
    $stateSafe = htmlspecialchars($state, ENT_QUOTES);
    $jwtSafe   = htmlspecialchars($jwt, ENT_QUOTES);

    echo <<<HTML
<!doctype html>
<html><body onload="document.forms[0].submit()">
  <form action="{$redirSafe}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="id_token" value="{$jwtSafe}">
    <input type="hidden" name="state" value="{$stateSafe}">
    <noscript><button type="submit">Continue</button></noscript>
  </form>
</body></html>
HTML;
    exit;
}

$ltiMessageHint = $ltiHintRaw;
if (empty($ltiMessageHint)) {
    $DIC->http()->saveResponse(
        $DIC->http()->response()->withStatus(400)
    );
    $DIC->http()->sendResponse();
    $DIC->http()->close();
    exit;
}

$parts = explode(":", $ltiMessageHint);
$isContentSelection = false;
$ref_id = '';
$il_client_id = '';
$redirect_uri = '';
if (count($parts) === 2) {
    [$ref_id, $il_client_id] = $parts;
} else if (count($parts) === 3 && !filter_var($parts[2], FILTER_VALIDATE_URL)) {
    [$ref_id, $il_client_id, $token] = $parts;
} else {
    $isContentSelection = true;
    [$ref_id, $il_client_id, $redirect_uri] = $parts;
}

ilSession::set('lti13_login_data', $data);

if ($isContentSelection) {
    $url = "../../../" . base64_decode($redirect_uri);
} else {
    $url = "../../../goto.php?target=lti_" . $ref_id . "&client_id=" . $il_client_id;
}
$DIC->http()->saveResponse(
    $DIC->http()->response()
        ->withStatus(302)
        ->withAddedHeader('Location', $url)
);
$DIC->http()->sendResponse();
$DIC->http()->close();
exit;
