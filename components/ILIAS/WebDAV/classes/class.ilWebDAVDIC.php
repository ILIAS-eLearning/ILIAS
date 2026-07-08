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

use Pimple\Container;
use ILIAS\DI\Container as ILIASContainer;
use ILIAS\WebDAV\Mount\Repository as MountRepository;
use ILIAS\WebDAV\Mount\RepositoryDB as MountRepositoryDB;
use ILIAS\WebDAV\Mount\UploadGUI as MountUploadGUI;

/**
 * @deprecated
 */
class ilWebDAVDIC extends Container
{
    public function initWithoutDIC(): void
    {
        global $DIC;
        $this->init($DIC);
    }

    public function init(ILIASContainer $DIC): void
    {
        $this['mountinstructions.repository'] = static fn($c): MountRepository
            => new MountRepositoryDB($DIC->database());

        $this['mountinstructions.uploadgui'] = static fn($c): MountUploadGUI => new MountUploadGUI(
            $DIC->ui()->mainTemplate(),
            $DIC->user(),
            $DIC->ctrl(),
            $DIC->language(),
            $DIC->rbac()->system(),
            $DIC['ilErr'],
            $DIC->toolbar(),
            $DIC->http(),
            $DIC->refinery(),
            $DIC->ui(),
            $DIC->filesystem(),
            $DIC->upload(),
            $c['mountinstructions.repository']
        );
    }

    public function mountinstructions_upload(): MountUploadGUI
    {
        return $this['mountinstructions.uploadgui'];
    }
}
