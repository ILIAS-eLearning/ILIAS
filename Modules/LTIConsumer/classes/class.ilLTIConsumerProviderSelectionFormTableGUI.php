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

/**
 * Class ilLTIConsumerProviderSelectionFormGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerProviderSelectionFormTableGUI extends ilPropertyFormGUI
{
    /**
     * @var ilLTIConsumerProviderTableGUI
     */
    protected ilLTIConsumerProviderTableGUI $table;

    /**
     * ilLTIConsumerProviderSelectionFormGUI constructor.
     */
    public function __construct(string $newType, ilObjLTIConsumerGUI $parentGui, string $parentCmd, string $applyFilterCmd, string $resetFilterCmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->table = new ilLTIConsumerProviderTableGUI($parentGui, $parentCmd);

        $this->table->setFilterCommand($applyFilterCmd);
        $this->table->setResetCommand($resetFilterCmd);

        $this->table->setSelectProviderCmd('save');
        $this->table->setOwnProviderColumnEnabled(true);

        $this->table->setDefaultFilterVisiblity(true);
        $this->table->setDisableFilterHiding(true);

        $this->table->init();

        $this->setTitle($DIC->language()->txt($newType . '_select_provider'));
    }

    public function setTitle(string $a_title): void
    {
        $this->table->setTitle($a_title);
    }

    public function getTitle(): string
    {
        return $this->table->getTitle();
    }

    public function getHTML(): string
    {
        return $this->table->getHTML();
    }

    public function applyFilter(): void
    {
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
    }

    public function resetFilter(): void
    {
        $this->table->resetFilter();
        $this->table->resetOffset();
    }

    /**
     * @return string|bool
     */
    public function getFilter(string $a_field)
    {
        $field = $this->table->getFilterItemByPostVar($a_field);

        if ($field instanceof ilCheckboxInputGUI) {
            return $field->getChecked();
        }

        return $field->getValue();
    }

    public function setData(array $data): void
    {
        $this->table->setData($data);
    }
}
