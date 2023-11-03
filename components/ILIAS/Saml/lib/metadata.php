<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

// ilias-patch: begin
use SAML2\Constants;
use SimpleSAML\Auth\Source;
use SimpleSAML\Configuration;
use SimpleSAML\Error\AuthSource;
use SimpleSAML\Locale\Translate;
use SimpleSAML\Metadata\SAMLBuilder;
use SimpleSAML\Metadata\Signer;
use SimpleSAML\Module\saml\Auth\Source\SP;
use SimpleSAML\Store;
use SimpleSAML\Store\SQL;
use SimpleSAML\Utils\Auth;
use SimpleSAML\Utils\Config\Metadata;
use SimpleSAML\Utils\Crypto;
use SimpleSAML\XHTML\Template;
use Symfony\Component\VarExporter\VarExporter;

chdir(__DIR__);

$ilias_main_directory = './';
$cookie_path = dirname($_SERVER['PHP_SELF']);

$i = 0;
while (!is_file($ilias_main_directory . 'ilias.ini.php') && $i < 20) {
    $ilias_main_directory .= '../';
    ++$i;

    $cookie_path = dirname($cookie_path);
}
chdir($ilias_main_directory);

if (!is_file(getcwd() . '/ilias.ini.php')) {
    die('Please ensure ILIAS is installed!');
}

$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

if (isset($_GET["client_id"])) {
    if ($cookie_path === "\\") {
        $cookie_path = '/';
    }

    setcookie('ilClientId', $_GET['client_id'], 0, $cookie_path, '');
    $_COOKIE['ilClientId'] = $_GET['client_id'];
}
define('IL_COOKIE_PATH', $cookie_path);

require_once 'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SAML);

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

$iliasHttpPath = ILIAS_HTTP_PATH;

require_once 'Services/Saml/classes/class.ilSamlAuthFactory.php';
$factory = new ilSamlAuthFactory();
$auth = $factory->auth();

// The source code below is copied from the SimpleSAMLphp library and modified regarding the HTTP path
// ilias-patch: end
if (!array_key_exists('PATH_INFO', $_SERVER)) {
    global $DIC;
    $DIC->logger()->root()->warning('Missing "PATH_INFO" variable. This could be a false positive log entry, but you have to ensure a valid "PATH_INFO" setting for your HTTP server.');
}

$config = Configuration::getInstance();
if ($config->getBoolean('admin.protectmetadata', false)) {
    Auth::requireAdmin();
}
// ilias-patch: begin
//$sourceId = substr($_SERVER['PATH_INFO'], 1);
$sourceId = $auth->getAuthId();
// ilias-patch: end
$source = Source::getById($sourceId);
if ($source === null) {
    throw new AuthSource($sourceId, 'Could not find authentication source.');
}

if (!($source instanceof SP)) {
    throw new AuthSource(
        $sourceId,
        'The authentication source is not a SAML Service Provider.'
    );
}

$entityId = $source->getEntityId();
$spconfig = $source->getMetadata();
$store = Store::getInstance();

$metaArray20 = [];

$slosvcdefault = [
    Constants::BINDING_HTTP_REDIRECT,
    Constants::BINDING_SOAP,
];

$slob = $spconfig->getArray('SingleLogoutServiceBinding', $slosvcdefault);
// ilias-patch: begin
$slol = $iliasHttpPath . '/saml2-logout.php/' . $sourceId . '/' . CLIENT_ID;
// ilias-patch: end

foreach ($slob as $binding) {
    if ($binding == Constants::BINDING_SOAP && !($store instanceof SQL)) {
        // we cannot properly support SOAP logout
        continue;
    }
    $metaArray20['SingleLogoutService'][] = [
        'Binding' => $binding,
        'Location' => $spconfig->getString('SingleLogoutServiceLocation', $slol),
    ];
}

$assertionsconsumerservicesdefault = [
    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
    'urn:oasis:names:tc:SAML:1.0:profiles:browser-post',
    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
    'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01',
];

if ($spconfig->getString('ProtocolBinding', '') == 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser') {
    $assertionsconsumerservicesdefault[] = 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser';
}

$assertionsconsumerservices = $spconfig->getArray('acs.Bindings', $assertionsconsumerservicesdefault);

$index = 0;
$eps = [];
$supported_protocols = [];
foreach ($assertionsconsumerservices as $services) {
    $acsArray = ['index' => $index];
    switch ($services) {
        case 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST':
            $acsArray['Binding'] = Constants::BINDING_HTTP_POST;
            // ilias-patch: begin
            $acsArray['Location'] = $iliasHttpPath . "/saml2-acs.php/{$sourceId}/" . CLIENT_ID;
            // ilias-patch: end
            if (!in_array(Constants::NS_SAMLP, $supported_protocols, true)) {
                $supported_protocols[] = Constants::NS_SAMLP;
            }
            break;
        case 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post':
            $acsArray['Binding'] = 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post';
            // ilias-patch: begin
            $acsArray['Location'] = $iliasHttpPath . "/saml1-acs.php/{$sourceId}/" . CLIENT_ID;
            // ilias-patch: end
            if (!in_array('urn:oasis:names:tc:SAML:1.1:protocol', $supported_protocols, true)) {
                $supported_protocols[] = 'urn:oasis:names:tc:SAML:1.1:protocol';
            }
            break;
        case 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact':
            $acsArray['Binding'] = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact';
            // ilias-patch: begin
            $acsArray['Location'] = $iliasHttpPath . "/saml2-acs.php/{$sourceId}/" . CLIENT_ID;
            // ilias-patch: end
            if (!in_array(Constants::NS_SAMLP, $supported_protocols, true)) {
                $supported_protocols[] = Constants::NS_SAMLP;
            }
            break;
        case 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01':
            $acsArray['Binding'] = 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01';
            // ilias-patch: begin
            $acsArray['Location'] = $iliasHttpPath . "/saml1-acs.php/{$sourceId}/artifact/" . CLIENT_ID;
            // ilias-patch: end
            if (!in_array('urn:oasis:names:tc:SAML:1.1:protocol', $supported_protocols, true)) {
                $supported_protocols[] = 'urn:oasis:names:tc:SAML:1.1:protocol';
            }
            break;
        case 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser':
            $acsArray['Binding'] = 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser';
            // ilias-patch: begin
            $acsArray['Location'] = $iliasHttpPath . "/saml2-acs.php/{$sourceId}/" . CLIENT_ID;
            // ilias-patch: end
            $acsArray['hoksso:ProtocolBinding'] = Constants::BINDING_HTTP_REDIRECT;
            if (!in_array(Constants::NS_SAMLP, $supported_protocols, true)) {
                $supported_protocols[] = Constants::NS_SAMLP;
            }
            break;
    }
    $eps[] = $acsArray;
    $index++;
}

$metaArray20['AssertionConsumerService'] = $eps;

$keys = [];
$certInfo = Crypto::loadPublicKey($spconfig, false, 'new_');
if ($certInfo !== null && array_key_exists('certData', $certInfo)) {
    $hasNewCert = true;

    $certData = $certInfo['certData'];

    $keys[] = [
        'type' => 'X509Certificate',
        'signing' => true,
        'encryption' => true,
        'X509Certificate' => $certInfo['certData'],
    ];
} else {
    $hasNewCert = false;
}

$certInfo = Crypto::loadPublicKey($spconfig);
if ($certInfo !== null && array_key_exists('certData', $certInfo)) {
    $certData = $certInfo['certData'];

    $keys[] = [
        'type' => 'X509Certificate',
        'signing' => true,
        'encryption' => ($hasNewCert ? false : true),
        'X509Certificate' => $certInfo['certData'],
    ];
} else {
    $certData = null;
}

$format = $spconfig->getValue('NameIDPolicy', null);
if ($format !== null) {
    if (is_array($format)) {
        $metaArray20['NameIDFormat'] = Configuration::loadFromArray($format)->getString(
            'Format',
            Constants::NAMEID_TRANSIENT
        );
    } elseif (is_string($format)) {
        $metaArray20['NameIDFormat'] = $format;
    }
}

$name = $spconfig->getLocalizedString('name', null);
$attributes = $spconfig->getArray('attributes', []);

if ($name !== null && !empty($attributes)) {
    $metaArray20['name'] = $name;
    $metaArray20['attributes'] = $attributes;
    $metaArray20['attributes.required'] = $spconfig->getArray('attributes.required', []);

    if (empty($metaArray20['attributes.required'])) {
        unset($metaArray20['attributes.required']);
    }

    $description = $spconfig->getArray('description', null);
    if ($description !== null) {
        $metaArray20['description'] = $description;
    }

    $nameFormat = $spconfig->getString('attributes.NameFormat', null);
    if ($nameFormat !== null) {
        $metaArray20['attributes.NameFormat'] = $nameFormat;
    }

    if ($spconfig->hasValue('attributes.index')) {
        $metaArray20['attributes.index'] = $spconfig->getInteger('attributes.index', 0);
    }

    if ($spconfig->hasValue('attributes.isDefault')) {
        $metaArray20['attributes.isDefault'] = $spconfig->getBoolean('attributes.isDefault', false);
    }
}

// add organization info
$orgName = $spconfig->getLocalizedString('OrganizationName', null);
if ($orgName !== null) {
    $metaArray20['OrganizationName'] = $orgName;

    $metaArray20['OrganizationDisplayName'] = $spconfig->getLocalizedString('OrganizationDisplayName', null);
    if ($metaArray20['OrganizationDisplayName'] === null) {
        $metaArray20['OrganizationDisplayName'] = $orgName;
    }

    $metaArray20['OrganizationURL'] = $spconfig->getLocalizedString('OrganizationURL', null);
    if ($metaArray20['OrganizationURL'] === null) {
        throw new \SimpleSAML\Error\Exception('If OrganizationName is set, OrganizationURL must also be set.');
    }
}

if ($spconfig->hasValue('contacts')) {
    $contacts = $spconfig->getArray('contacts');
    foreach ($contacts as $contact) {
        $metaArray20['contacts'][] = Metadata::getContact($contact);
    }
}

// add technical contact
$email = $config->getString('technicalcontact_email', 'na@example.org');
if ($email && $email !== 'na@example.org') {
    $techcontact = [
        'emailAddress' => $email,
        'name' => $config->getString('technicalcontact_name', null),
        'contactType' => 'technical'
    ];
    $metaArray20['contacts'][] = \SimpleSAML\Utils\Config\Metadata::getContact($techcontact);
}

// add certificate
if (count($keys) === 1) {
    $metaArray20['certData'] = $keys[0]['X509Certificate'];
} elseif (count($keys) > 1) {
    $metaArray20['keys'] = $keys;
}

// add EntityAttributes extension
if ($spconfig->hasValue('EntityAttributes')) {
    $metaArray20['EntityAttributes'] = $spconfig->getArray('EntityAttributes');
}

// add UIInfo extension
if ($spconfig->hasValue('UIInfo')) {
    $metaArray20['UIInfo'] = $spconfig->getArray('UIInfo');
}

// add RegistrationInfo extension
if ($spconfig->hasValue('RegistrationInfo')) {
    $metaArray20['RegistrationInfo'] = $spconfig->getArray('RegistrationInfo');
}

// add signature options
if ($spconfig->hasValue('WantAssertionsSigned')) {
    $metaArray20['saml20.sign.assertion'] = $spconfig->getBoolean('WantAssertionsSigned');
}
if ($spconfig->hasValue('redirect.sign')) {
    $metaArray20['redirect.validate'] = $spconfig->getBoolean('redirect.sign');
} elseif ($spconfig->hasValue('sign.authnrequest')) {
    $metaArray20['validate.authnrequest'] = $spconfig->getBoolean('sign.authnrequest');
}

$metaArray20['metadata-set'] = 'saml20-sp-remote';
$metaArray20['entityid'] = $entityId;

$metaBuilder = new SAMLBuilder($entityId);
$metaBuilder->addMetadataSP20($metaArray20, $supported_protocols);
$metaBuilder->addOrganizationInfo($metaArray20);

$xml = $metaBuilder->getEntityDescriptorText();

unset($metaArray20['UIInfo'], $metaArray20['metadata-set'], $metaArray20['entityid']);

// sanitize the attributes array to remove friendly names
if (isset($metaArray20['attributes']) && is_array($metaArray20['attributes'])) {
    $metaArray20['attributes'] = array_values($metaArray20['attributes']);
}

// sign the metadata if enabled
$xml = Signer::sign($xml, $spconfig->toArray(), 'SAML 2 SP');

if (array_key_exists('output', $_REQUEST) && $_REQUEST['output'] == 'xhtml') {
    $t = new Template($config, 'metadata.php', 'admin');

    $t->data['clipboard.js'] = true;
    $t->data['header'] = 'saml20-sp'; // TODO: Replace with headerString in 2.0
    $t->data['headerString'] = Translate::noop('metadata_saml20-sp');
    $t->data['metadata'] = htmlspecialchars($xml);
    $t->data['metadataflat'] = '$metadata[' . var_export($entityId, true) . '] = ' . VarExporter::export($metaArray20) . ';';
    // ilias-patch: begin
    $t->data['metaurl'] = $iliasHttpPath . "/metadata.php{$sourceId}/" . CLIENT_ID;
    // ilias-patch: end
    $t->show();
} else {
    header('Content-Type: application/samlmetadata+xml');
    // ilias-patch: begin
    $ascii_filename = ilFileUtils::getASCIIFilename($sourceId);
    header("Content-Disposition:attachment; filename=\"" . $ascii_filename . "\"");
    // ilias-patch: end
    echo($xml);
}
