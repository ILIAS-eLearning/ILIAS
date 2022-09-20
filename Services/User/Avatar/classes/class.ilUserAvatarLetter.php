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
 * Class ilUserAvatarLetter
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilUserAvatarLetter extends ilUserAvatarBase
{
    /**
     * All variants of letter avatar background colors (MUST be 26), note for a11y reason, they must be in contrast 3x1 to white (foreground color)
     * @var string[]
     */
    protected static array $colors = [
        "#0e6252", "#107360", "#aa890a", "#c87e0a", "#176437", "#196f3d", "#bf6516", "#a04000", "#1d6fa5", "#1b557a",
        "#bf2718", "#81261d", "#713b87", "#522764", "#78848c", "#34495e", "#2c3e50", "#566566", "#90175a", "#9e2b6e",
        "#d22f10", "#666d4e", "#715a32", "#83693a", "#963a30", "#e74c3c"
    ];

    public function getUrl(): string
    {
        static $amount_of_colors;
        if (!isset($amount_of_colors)) {
            $amount_of_colors = count(self::$colors);
        }
        // general idea, see https://gist.github.com/vctrfrnndz/fab6f839aaed0de566b0
        $color = self::$colors[crc32($this->name) % $amount_of_colors];
        $tpl = new \ilTemplate('tpl.letter_avatar.svg', true, true, 'Services/User');
        $tpl->setVariable('COLOR', $color);
        $tpl->setVariable('SHORT', $this->name);

        return 'data:image/svg+xml,' . rawurlencode($tpl->get());
    }
}
