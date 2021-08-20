<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserAvatarLetter
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilUserAvatarLetter extends ilUserAvatarBase
{
    /**
     * all variants of letter avatar background colors (MUST be 26), note for a11y reason, they must be in contrast 3x1 to white (foreground color)
     * @var array
     */

    protected static $colors = [
        "#0e6252", "#107360", "#aa890a", "#c87e0a", "#176437", "#196f3d", "#bf6516", "#a04000", "#1d6fa5", "#1b557a",
        "#bf2718", "#81261d", "#713b87", "#522764", "#78848c", "#34495e", "#2c3e50", "#566566", "#90175a", "#9e2b6e",
        "#d22f10", "#666d4e", "#715a32", "#83693a", "#963a30", "#e74c3c"
    ];

    /**
     * @return string
     * @throws ilTemplateException
     */
    public function getUrl() : string
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
