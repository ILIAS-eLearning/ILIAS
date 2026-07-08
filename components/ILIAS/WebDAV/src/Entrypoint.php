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

declare(strict_types=1);

namespace ILIAS\WebDAV;

use Sabre\DAV\Browser\Plugin;
use ILIAS\WebDAV\Lock\GeneralLock;
use ILIAS\WebDAV\Lock\LocksRepositoryDB;
use ILIAS\WebDAV\Mount\RepositoryDB;
use ILIAS\WebDAV\Mount\UriBuilder;
use ILIAS\WebDAV\Mount\ObjectInstructions;
use ILIAS\WebDAV\Mount\ObjectlessInstructions;
use ILIAS\WebDAV\Mount\InstructionsGUI;
use ILIAS\WebDAV\Entity\Factory;
use ILIAS\WebDAV\Request\RequestTranslation;
use ILIAS\WebDAV\Auth\BasicAuthentication;
use ILIAS\WebDAV\Log\Log;
use ILIAS\WebDAV;
use Sabre\DAV\Server;
use Sabre\DAV\TemporaryFileFilterPlugin;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\GlobalHttpState;

class Entrypoint implements \ILIAS\Component\EntryPoint
{
    public function __construct(
        private Factory $factory,
        private RequestTranslation $request_translation,
        private SecretKeyRotation $secret_key_rotation,
        private Config $config
    ) {
    }

    public function getName(): string
    {
        return WebDAV::class;
    }

    public function enter(): int
    {
        entry_point('ILIAS Legacy Initialisation Adapter');

        global $DIC; // TODO remove Service Locator

        $logger = $DIC->logger()->webdav();
        $current_user = $DIC->user();
        $auth_session = $DIC['ilAuthSession'];
        $filesystem = $DIC->filesystem()->temp();
        $database = $DIC->database();
        $request = $DIC->http()->request();
        $language = $DIC->language();
        $ui = $DIC->ui();
        $http = $DIC->http();

        $this->request_translation->setup();

        if(!$this->config->isActive()) {
            return 0;
        }

        if ($this->request_translation->showMountPoint()) {
            $this->handleMountInstruction(
                $database,
                $request,
                $current_user,
                $language,
                $ui,
                $http
            );

            return 0;
        }

        $server = new Server(
            $this->factory->getMountPoint(),
        );

        $server->setBaseUri($this->request_translation->getBasePath());

        $server->addPlugin(
            new BasicAuthentication(
                $this->secret_key_rotation,
                $logger,
                $current_user,
                $auth_session,
                $filesystem,
            )
        );

        $server->addPlugin(
            new GeneralLock(
                $this->factory,
                new LocksRepositoryDB(
                    $database
                )
            )
        );

        $server->addPlugin(
            new TemporaryFileFilterPlugin(sys_get_temp_dir())
        );

        if ($this->config->enableDebugging()) {
            $server->addPlugin(
                new Plugin(true)
            );
        }

        $server->addPlugin(
            new Log(
                $this->config->enableDebugging()
            )
        );

        $server->start();

        $this->request_translation->close();

        return 0;
    }

    protected function handleMountInstruction(
        \ilDBInterface $database,
        ServerRequestInterface $request,
        \ilObjUser $current_user,
        \ilLanguage $language,
        UIServices $ui,
        GlobalHttpState $http
    ): void {
        $repo = new RepositoryDB($database);
        $uri_builder = new UriBuilder(
            $request,
            $this->config
        );
        $path_value = $this->request_translation->getRequestedPathAsArray()[0] ?? '';

        $settings = new \ilSetting();
        if (str_starts_with($path_value, 'ref_')) {
            $instructions = new ObjectInstructions(
                $repo,
                $uri_builder,
                $settings,
                $current_user->getLanguage(),
                (int) substr($path_value, 4)
            );
        } elseif (strlen($path_value) === 2) {
            $instructions = new ObjectlessInstructions(
                $repo,
                $uri_builder,
                $settings,
                $path_value
            );
        } else {
            throw new \InvalidArgumentException("Invalid path for mount-instructions: '$path_value'");
        }

        $gui = new InstructionsGUI(
            $instructions,
            $language,
            $ui,
            $http
        );
        $gui->renderMountInstructionsContent();
    }

}
