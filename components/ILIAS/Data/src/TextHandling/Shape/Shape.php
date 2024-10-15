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

namespace Data\src\TextHandling\Shape;

use Data\src\TextHandling\Text\HTML;
use Data\src\TextHandling\Text\PlainText;
use Data\src\TextHandling\Text\Text;
use Data\src\TextHandling\Markup\Markup;

abstract class Shape
{
    /**
     * @throws \InvalidArgumentException if $text does not match format.
     */
    abstract public function toHTML($text): HTML;

    /**
     * @throws \InvalidArgumentException if $text does not match format.
     */
    abstract public function toPlainText($text): PlainText;
    abstract public function getMarkup(): Markup;
    abstract public function fromString(string $text): Text;
    abstract public function isRawStringCompliant(string $text): bool;
}
