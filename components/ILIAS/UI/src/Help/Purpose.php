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

namespace ILIAS\UI\Help;

/**
 * A purpose describes the intended use for a certain help text.
 *
 * Retrieving help texts is done via the `HelpTextRetriever`. One dimension to
 * select a help text is the intended "topics" the text is about, the other is
 * a "purpose". Think of the "New Object Button", which might have topics "button"
 * "object" and "creation". A text with the purpose "tooltip" should be short:
 * "Click here to create new learning objects here." while a text with a purpose
 * "explanation" might talk in length about the ILIAS object system.
 *
 * So "purposes" allow the UI framework to communicate which type of help text it
 * is looking for.
 *
 * This class here really is an enum and should become one once we drop support
 * for PHP versions that do not support enums.
 *
 * It should be extended via PR, preferably with the according functionality
 * in the renderer of the UI framework.
 *
 * It can not be extended by consumers, as this is a way for the UI framework to
 * communicate its requirements about help texts to some help system. Hence we
 * use a closed vocabulary and the class is final.
 */
final class Purpose
{
    public const PURPOSE_TOOLTIP = 2;

    protected int $purpose;

    public function __construct(
        int $purpose
    ) {
        if (!in_array($purpose, [self::PURPOSE_TOOLTIP])) {
            throw new \InvalidArgumentException("Invalid purpose: $purpose");
        }
        $this->purpose = $purpose;
    }

    public function isTooltip(): bool
    {
        return $this->purpose === self::PURPOSE_TOOLTIP;
    }

    public static function Tooltip(): self
    {
        return new self(self::PURPOSE_TOOLTIP);
    }
}
