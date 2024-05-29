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

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Symbol\Glyph\Glyph;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Markdown extends Textarea
{
    public const ACTION_HEADING = "ACTION_HEADING";
    public const ACTION_LINK = "ACTION_LINK";
    public const ACTION_BOLD = "ACTION_BOLD";
    public const ACTION_ITALIC = "ACTION_ITALIC";
    public const ACTION_ORDERED_LIST = "ACTION_ORDERED_LIST";
    public const ACTION_UNORDERED_LIST = "ACTION_UNORDERED_LIST";

    public function withAdditionalAction(Glyph $icon, string $action_name): static;

    /** @param string[] $action_names */
    public function withAllowedActions(array $action_names): static;
}
