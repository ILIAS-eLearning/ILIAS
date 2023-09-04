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
    protected int $currentitemnumber;
    protected string $content;

    public function __construct()
    {
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

    public function setCurrentItemNumber(int $currentitemnumber): void
    {
        $this->currentitemnumber = $currentitemnumber;
    }

    final public function getCurrentItemNumber(): int
    {
        return $this->currentitemnumber;
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }

    final public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function fillDataSection(): void
    {
        $this->tpl->setVariable('BLOCK_ROW', $this->getContent());
    }

    public function fillFooter(): void
    {
        $lng = $this->lng;

        if (is_array($this->data)) {
            $this->max_count = count($this->data);
        }

        if ($this->getEnableNumInfo()) {
            $numinfo = '(' . $this->getCurrentItemNumber() . ' ' .
                strtolower($lng->txt('of')) . ' ' . $this->max_count . ')';

            if ($this->max_count > 0) {
                $this->tpl->setVariable('NUMINFO', $numinfo);
            }
        }
    }

    public function fillPreviousNext(): void
    {
    }
}
