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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRSSButtonGUI
{
    public const ICON_RSS = "rss";
    public const ICON_RSS_AUDIO = "rss audio";
    public const ICON_RSS_VIDEO = "rss video";
    public const ICON_ICAL = "ical";
    public const ICON_ITUNES = "itunes";
    public const ICON_ITUNES_AUDIO = "itunes audio";
    public const ICON_ITUNES_VIDEO = "itunes video";

    /**
     * Get icon html
     *
     * @param string $a_type icons type ICON_RSS | ICON_ICAL
     * @param string $a_href href
     * @return string icon html
     */
    public static function get(
        string $a_type,
        string $a_href = ""
    ) : string {
        $tpl = new ilTemplate("tpl.rss_icon.html", true, true, "Services/News");

        if ($a_href !== "") {
            $tpl->setCurrentBlock("a_start");
            $tpl->setVariable("HREF", $a_href);
            $tpl->parseCurrentBlock();
            $tpl->touchBlock("a_end");
        }

        $text = "";

        switch ($a_type) {
            case self::ICON_RSS:
                $text = "RSS";
                break;

            case self::ICON_RSS_AUDIO:
                $text = "RSS Audio";
                break;

            case self::ICON_RSS_VIDEO:
                $text = "RSS Video";
                break;

            case self::ICON_ICAL:
                $text = "iCal";
                break;

            case self::ICON_ITUNES:
                $text = "iTunes";
                break;

            case self::ICON_ITUNES_AUDIO:
                $text = "iTunes Audio";
                break;

            case self::ICON_ITUNES_VIDEO:
                $text = "iTunes Video";
                break;
        }

        $tpl->setVariable("TEXT", $text);

        return $tpl->get();
    }
}
