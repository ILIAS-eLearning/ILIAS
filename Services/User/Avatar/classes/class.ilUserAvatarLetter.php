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
     * @var array
     */
    protected static $colors = [
        "#1abc9c", "#16a085", "#f1c40f",
        "#f39c12", "#2ecc71", "#27ae60",
        "#e67e22", "#d35400", "#3498db",
        "#2980b9", "#e74c3c", "#c0392b",
        "#9b59b6", "#8e44ad", "#bdc3c7",
        "#34495e", "#2c3e50", "#95a5a6",
        "#7f8c8d", "#ec87bf", "#d870ad",
        "#f69785", "#9ba37e", "#b49255",
        "#b49255", "#a94136"
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
