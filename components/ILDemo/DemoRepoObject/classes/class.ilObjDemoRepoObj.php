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

class ilObjDemoRepoObj extends ilObjectPlugin
{
    public const TYPE = 'xdmo';

    public function __construct(int $a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }

    public function initType(): void
    {
        $this->setType(self::TYPE);
    }

    public function doCreate(bool $clone_mode = false): void
    {
    }

    public function doRead(): void
    {
    }

    public function doUpdate(): void
    {
    }

    public function doDelete(): void
    {
    }

    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null): void
    {
    }

    public function txtClosure(): Closure
    {
        return function (string $code) {
            return $this->txt($code);
        };
    }

    public function pluginTxt(string $code): string
    {
        return parent::txt($code);
    }

    public function getDirectory(): string
    {
        return $this->plugin->getDirectory();
    }

}
