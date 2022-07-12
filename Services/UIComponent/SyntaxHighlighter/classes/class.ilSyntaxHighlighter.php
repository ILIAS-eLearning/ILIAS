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
 * Syntax highlighter wrapper class
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @deprecated 11
 */
class ilSyntaxHighlighter
{
    public const JAVA = "java";
    public const PHP = "php";
    public const C = "c";
    public const CPP = "cpp";
    public const HTML = "html4strict";
    public const XML = "xml";
    public const VISUAL_BASIC = "vb";
    public const LATEX = "latex";
    public const DELPHI = "delphi";

    /**
     * @var string[]
     */
    protected static array $langs = array(
        self::JAVA => "Java",
        self::PHP => "PHP",
        self::C => "C",
        self::CPP => "C++",
        self::HTML => "HTML",
        self::XML => "XML",
        self::VISUAL_BASIC => "Visual Basic",
        self::DELPHI => "Delphi"
    );

    /**
     * @var string[]
     */
    protected static array $v51_map = array(
        "php3" => "php",
        "java122" => "java",
        "html" => "html4strict"
    );

    protected string $lang;

    protected function __construct(string $a_lang)
    {
        $this->lang = $a_lang;
    }

    public static function getInstance(string $a_lang) : self
    {
        return new self($a_lang);
    }

    /**
     * Get supported languages (keys are internal values, values are for representation)
     * @return string[]
     */
    public static function getSupportedLanguages() : array
    {
        return self::$langs;
    }

    /**
     * Is language supported?
     */
    public static function isSupported(string $a_lang) : bool
    {
        return isset(self::$langs[$a_lang]);
    }

    /**
     * Get new language id (for an old one)
     */
    public static function getNewLanguageId(string $a_old_lang_id) : string
    {
        return self::$v51_map[$a_old_lang_id] ?? $a_old_lang_id;
    }


    /**
     * Get supported languages (keys are ILIAS <= 5.1 internal values, values are for representation)
     */
    public static function getSupportedLanguagesV51() : array
    {
        $langs = array();
        $map = array_flip(self::$v51_map);
        foreach (self::$langs as $k => $v) {
            if (isset($map[$k])) {
                $k = $map[$k];
            }
            $langs[$k] = $v;
        }
        return $langs;
    }

    public function highlight(string $a_code) : string
    {
        include_once("./libs/composer/vendor/geshi/geshi/src/geshi.php");
        $geshi = new Geshi(html_entity_decode($a_code), $this->lang);

        //var_dump($geshi->get_supported_languages()); exit;

        //$geshi->set_header_type(GESHI_HEADER_NONE); // does not work as expected, see below
        $a_code = $geshi->parse_code();

        // remove geshi pre tag (setting GESHI_HEADER_NONE gives us undesired br tags)
        $a_code = substr($a_code, strpos($a_code, ">") + 1);
        $a_code = substr($a_code, 0, strrpos($a_code, "<"));

        return $a_code;
    }
}
