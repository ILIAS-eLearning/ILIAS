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
     */
    public function getUrl()
    {
        // general idea, see https://gist.github.com/vctrfrnndz/fab6f839aaed0de566b0
        $color = self::$colors[$this->usrId % count(self::$colors)];
        $tpl = new \ilTemplate('tpl.letter_avatar.svg', true, true, 'Services/User');
        $tpl->setVariable('COLOR', $color);
        $tpl->setVariable('SHORT', $this->name);
        $data_src = 'data:image/svg+xml,' . rawurlencode($tpl->get());

        return $data_src;
    }
}
