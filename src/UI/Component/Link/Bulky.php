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

namespace ILIAS\UI\Component\Link;

use ILIAS\UI\Component\Symbol;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Bulky Link - a visually enriched link that looks like a button but behaves like a link.
 */
interface Bulky extends Link, JavaScriptBindable
{
    /**
     * Get the label of the link
     */
    public function getLabel(): string;

    /**
     * Get the Icon or Glyph the Link was created with.
     */
    public function getSymbol(): Symbol\Symbol;
}
