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
 *
 * @deprecated 10
 */
class ilGlyphGUI
{
    public const UP = "up";
    public const DOWN = "down";
    public const ADD = "add";
    public const REMOVE = "remove";
    public const PREVIOUS = "previous";
    public const NEXT = "next";
    public const CALENDAR = "calendar";
    public const CLOSE = "close";
    public const ATTACHMENT = "attachment";
    public const CARET = "caret";
    public const DRAG = "drag";
    public const SEARCH = "search";
    public const FILTER = "filter";
    public const NO_TEXT = "**notext**";
    public const INFO = "info";
    public const EXCLAMATION = "exclamation";

    protected static array $map = array(
        "up" => array("class" => "glyphicon glyphicon-chevron-up", "txt" => "up"),
        "down" => array("class" => "glyphicon glyphicon-chevron-down", "txt" => "down"),
        "add" => array("class" => "glyphicon glyphicon-plus", "txt" => "add"),
        "remove" => array("class" => "glyphicon glyphicon-minus", "txt" => "remove"),
        "previous" => array("class" => "glyphicon glyphicon-chevron-left", "txt" => "previous"),
        "next" => array("class" => "glyphicon glyphicon-chevron-right", "txt" => "next"),
        "calendar" => array("class" => "glyphicon glyphicon-calendar", "txt" => "calendar"),
        "close" => array("class" => "glyphicon glyphicon-remove", "txt" => "close"),
        "attachment" => array("class" => "glyphicon glyphicon-paperclip", "txt" => "attachment"),
        "caret" => array("class" => "", "txt" => ""),
        "drag" => array("class" => "glyphicon glyphicon-share-alt", "txt" => "drag"),
        "search" => array("class" => "glyphicon glyphicon-search", "txt" => "search"),
        "filter" => array("class" => "glyphicon glyphicon-filter", "txt" => "filter"),
        "exclamation" => array("class" => "glyphicon glyphicon-exclamation-sign ilAlert", "txt" => "exclamation"),
        "info" => array("class" => "glyphicon glyphicon-info-sign", "txt" => "info")
    );

    public static function get(
        string $a_glyph,
        string $a_text = ""
    ) : string {
        global $DIC;

        $lng = $DIC->language();

        $text = ($a_text === "")
            ? $lng->txt(self::$map[$a_glyph]["txt"])
            : (($a_text === self::NO_TEXT)
                ? ""
                : $a_text);
        switch ($a_glyph) {
            case self::CARET:
                $html = '<span class="caret"></span>';
                break;

            default:
                $html = '<span class="sr-only">' . $text .
                    '</span><span class="' . self::$map[$a_glyph]["class"] . '"></span>';
                break;

        }
        return $html;
    }
}
