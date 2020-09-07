<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Syntax highlighter wrapper class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup
 */
class ilSyntaxHighlighter
{
    const JAVA = "java";
    const PHP = "php";
    const C = "c";
    const CPP = "cpp";
    const HTML = "html4strict";
    const XML = "xml";
    const VISUAL_BASIC = "vb";
    const LATEX = "latex";
    const DELPHI = "delphi";

    /**
     * @var string[]
     */
    protected static $langs = array(
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
    protected static $v51_map = array(
        "php3" => "php",
        "java122" => "java",
        "html" => "html4strict"
    );

    /**
     * @var string language code
     */
    protected $lang;

    /**
     * Constructor
     *
     * @param string $a_lang language constant
     * @return ilSyntaxHighlighter
     */
    protected function __construct($a_lang)
    {
        $this->lang = $a_lang;
    }

    /**
     * Get instance
     *
     * @param string $a_lang language constant
     * @return ilSyntaxHighlighter
     */
    public static function getInstance($a_lang)
    {
        return new self($a_lang);
    }

    /**
     * Get supported languages (keys are internal values, values are for representation)
     *
     * @param
     * @return string[]
     */
    public static function getSupportedLanguages()
    {
        return self::$langs;
    }

    /**
     * Is language supported?
     *
     * @param string $a_lang
     * @return bool
     */
    public static function isSupported($a_lang)
    {
        return isset(self::$langs[$a_lang]);
    }

    /**
     * Get new language id (for an old one)
     *
     * @param string $a_old_lang_id
     * @return string
     */
    public static function getNewLanguageId($a_old_lang_id)
    {
        if (isset(self::$v51_map[$a_old_lang_id])) {
            return self::$v51_map[$a_old_lang_id];
        }
        return $a_old_lang_id;
    }


    /**
     * Get supported languages (keys are ILIAS <= 5.1 internal values, values are for representation)
     *
     * @param
     * @return string[]
     */
    public static function getSupportedLanguagesV51()
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

    /**
     * Highlight code
     *
     * @param string $a_code
     * @return string highlighted code
     */
    public function highlight($a_code)
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
