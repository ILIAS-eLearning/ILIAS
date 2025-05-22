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

namespace ILIAS\File\Capabilities\Check;

use ILIAS\File\Capabilities\Capability;
use ILIAS\File\Capabilities\Capabilities;
use ILIAS\File\Capabilities\Context;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Download extends BaseCheck implements Check
{
    public $static_url;
    protected ?\ilObjFileInfo $info = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function canUnlock(): Capabilities
    {
        return Capabilities::DOWNLOAD;
    }

    public function maybeUnlock(
        Capability $capability,
        CheckHelpers $helpers,
        \ilObjFileInfo $info,
        Context $context,
    ): Capability {
        $this->info = $info;
        return $capability->withUnlocked($this->hasPermission($helpers, $context, ...$capability->getPermissions()));
    }

    public function maybeBuildURI(Capability $capability, CheckHelpers $helpers, Context $context): Capability
    {
        if (!$capability->isUnlocked()) {
            return $capability;
        }

        return $capability->withURI(
            $helpers->fromTarget(
                $helpers->ctrl->getLinkTargetByClass(
                    [
                        $this->baseClass($context),
                        \ilObjFileGUI::class
                    ],
                    Capabilities::DOWNLOAD->value
                )
            )
        );
    }

}
