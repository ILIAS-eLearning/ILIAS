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

class ilDclDetailedViewDefinition extends ilPageObject
{
    public const PARENT_TYPE = 'dclf';
    protected int $table_id;

    /**
     * Get parent type
     */
    public function getParentType(): string
    {
        return self::PARENT_TYPE;
    }

    /**
     * @return ilDclBaseFieldModel[]
     */
    public function getAvailablePlaceholders(): array
    {
        $tableview = new ilDclTableView($this->getId());
        return ilDclCache::getTableCache($tableview->getTableId())->getFields();
    }

    public static function exists(int $id): bool
    {
        return parent::_exists(self::PARENT_TYPE, $id);
    }

    public function isActive(): bool
    {
        foreach ($this->getAllPCIds() as $id) {
            if ($this->getContentObjectForPcId($id)->isEnabled()) {
                return parent::_lookupActive($this->getId(), self::PARENT_TYPE);
            }
        }
        return false;
    }
}
