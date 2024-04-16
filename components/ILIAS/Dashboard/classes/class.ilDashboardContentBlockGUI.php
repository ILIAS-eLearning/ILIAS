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

class ilDashboardContentBlockGUI extends ilBlockGUI
{
    public const BLOCK_TYPE = 'dashcontent';

    protected int $current_item_number;
    protected string $content;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();

        parent::__construct();

        $this->setEnableNumInfo(false);
        $this->setLimit(99999);
        $this->setPresentation(self::PRES_MAIN_LEG);
        $this->allow_moving = false;
    }

    final public function getBlockType(): string
    {
        return self::BLOCK_TYPE;
    }

    final public function setCurrentItemNumber(int $a_currentitemnumber): void
    {
        $this->current_item_number = $a_currentitemnumber;
    }

    final public function getCurrentItemNumber(): int
    {
        return $this->current_item_number;
    }

    final protected function isRepositoryObject(): bool
    {
        return false;
    }

    final public function getContent(): string
    {
        return $this->content;
    }

    final protected function getLegacyContent(): string
    {
        return $this->content;
    }

    final public function setContent(string $a_content): void
    {
        $this->content = $a_content;
    }
}
