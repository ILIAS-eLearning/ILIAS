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

namespace ILIAS\Export;

use ilPropertyFormGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class AbstractPrintViewProvider implements PrintViewProvider
{
    public const PRINT = "print";
    public const OFFLINE = "offline";

    /**
     * @var bool
     */
    protected $offline = false;

    /**
     * Set output mode
     * @param string $a_val self::PRINT|self::OFFLINE
     */
    public function setOffline(bool $offline)
    {
        $this->offline = $offline;
    }

    /**
     * Get output mode
     * @return string self::PRINT|self::OFFLINE
     */
    protected function getOutputMode(): string
    {
        return $this->offline
            ? self::OFFLINE
            : self::PRINT;
    }

    public function getOnSubmitCode(): string
    {
        return "";
    }

    public function autoPageBreak(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSelectionForm(): ?ilPropertyFormGUI
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPages(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTemplateInjectors(): array
    {
        return [];
    }
}
