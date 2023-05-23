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

namespace ILIAS\Repository\Symbol;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class SymbolAdapterGUI
{
    protected const TYPE_GLYPH = 0;
    protected \ILIAS\DI\UIServices $ui;
    protected int $type = self::TYPE_GLYPH;
    protected string $gl_type = "";
    protected string $href = "";

    public function __construct(
    ) {
        global $DIC;

        $this->ui = $DIC->ui();
    }

    public function glyph(
        string $gl_type,
        string $href = ""
    ): self {
        $this->gl_type = $gl_type;
        $this->href = $href;
        $this->type = self::TYPE_GLYPH;
        return $this;
    }

    protected function getSymbol(): \ILIAS\UI\Component\Symbol\Symbol
    {
        $gl = $this->gl_type;
        $s = $this->ui->factory()->symbol()->glyph()->$gl(
            $this->href
        );
        return $s;
    }

    public function render(): string
    {
        $s = $this->ui->renderer()->render($this->getSymbol());
        // workaround to get rid of a tags
        if ($this->href === "") {
            $s = str_replace("</a>", "", substr($s, strpos($s, "<span")));
        }
        return $s;
    }
}
