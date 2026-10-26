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

use ILIAS\WOPI\Handler\RequestHandler;
use ILIAS\WOPI\Lock\LockService;
use ILIAS\WOPI\Lock\LockDBRepository;
use ILIAS\WOPI\Handler\ProofValidator;
use ILIAS\WOPI\Discovery\ProofKeyRepository;
use ILIAS\WOPI\Discovery\ProofKeyRefresh;
use ILIAS\WOPI\Discovery\Crawler;
use ILIAS\WOPI\Discovery\ProofKeys;
use ILIAS\Data\URI;

// this is the base file for WOPI requests. It responds to the following requests:
// - CheckFileInfo
// - GetFile
// - PutFile
// - Lock
// - Unlock
// INIT ILIAS
require_once("../../vendor/composer/vendor/autoload.php");
require_once("../../artifacts/bootstrap_default.php");

entry_point("ILIAS Legacy Initialisation Adapter");

// handle all requests behind /wopi/index.php/
global $DIC;
$ilias_url = new URI(ILIAS_HTTP_PATH);
$settings = $DIC->settings();
$proof_keys = new ProofKeyRepository($settings);
$handler = new RequestHandler(
    $DIC->http(),
    $DIC->resourceStorage(),
    $DIC['file_delivery.data_signer'],
    new LockService(new LockDBRepository($DIC->database())),
    new ProofValidator($proof_keys->get()),
    $ilias_url->getSchema() . '://' . $ilias_url->getAuthority(),
    $settings->get('wopi_activated', '0') === '1',
    (int) $settings->get('saving_interval', '0'),
    $DIC['ui.upload_limit_resolver']->getPhpUploadLimitInBytes(),
    \Closure::fromCallable(new ProofKeyRefresh(
        $proof_keys,
        $settings,
        static function () use ($settings): ?ProofKeys {
            $discovery_url = $settings->get('wopi_discovery_url');
            $crawler = new Crawler();
            if ($discovery_url === null || !$crawler->validate(new URI($discovery_url))) {
                return null;
            }
            return $crawler->getProofKeys();
        }
    )),
    $DIC->logger()->wopi()
);
$handler->handleRequest();
