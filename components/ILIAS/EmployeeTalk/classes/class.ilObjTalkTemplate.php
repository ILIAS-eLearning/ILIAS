<?php

declare(strict_types=1);

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

final class ilObjTalkTemplate extends ilContainer
{
    public const TYPE = 'talt';

    public function __construct(int $id = 0, bool $a_call_by_reference = true)
    {
        $this->setType(self::TYPE);
        parent::__construct($id, $a_call_by_reference);
    }

    public function create(): int
    {
        parent::create();
        $this->_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::CUSTOM_METADATA, '1');
        return $this->getId();
    }

    public static function _exists(int $id, bool $reference = false, ?string $type = null): bool
    {
        return parent::_exists($id, $reference, self::TYPE);
    }
}
