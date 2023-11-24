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
 * Class ilObjStyleSheet
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjStyleSheet extends ilObject
{
    protected bool $is_3_10_skin = false;
    protected string $export_sub_dir = "";
    protected array $chars_by_type = [];
    protected array $end_styles = [];
    protected array $chars = [];
    protected bool $up_to_date = false;
    public array $style = [];
    protected array $hidden_chars = [];
    protected array $style_class = [];

    protected int $scope = 0;

    public static array $num_unit = array("px", "em", "ex", "%", "pt", "pc", "in", "mm", "cm");
    public static array $num_unit_no_perc = array("px", "em", "ex", "pt", "pc", "in", "mm", "cm");

    // css parameters and their attribute values, input type and group
    public static array $parameter = array(
        "font-size" => array(
                        "values" => array("xx-small", "x-small", "small", "medium", "large", "x-large", "xx-large", "smaller", "larger"),
                        "input" => "fontsize",
                        "group" => "text"),
        "font-family" => array(
                        "values" => array(),
                        "input" => "text",
                        "group" => "text"),
        "font-style" => array(
                        "values" => array("italic", "oblique", "normal"),
                        "input" => "select",
                        "group" => "text"),
        "font-weight" => array(
                        "values" => array("bold", "normal", "bolder", "lighter"),
                        "input" => "select",
                        "group" => "text"),
        "font-variant" => array(
                        "values" => array("small-caps", "normal"),
                        "input" => "select",
                        "group" => "text"),
        "word-spacing" => array(
                        "values" => array(),
                        "input" => "numeric_no_perc",
                        "group" => "text"),
        "letter-spacing" => array(
                        "values" => array(),
                        "input" => "numeric_no_perc",
                        "group" => "text"),
        "text-decoration" => array(
                        "values" => array("underline", "overline", "line-through", "blink", "none"),
                        "input" => "select",
                        "group" => "text"),
        "text-transform" => array(
                        "values" => array("capitalize", "uppercase", "lowercase", "none"),
                        "input" => "select",
                        "group" => "text"),
        "color" => array(
                        "values" => array(),
                        "input" => "color",
                        "group" => "text"),
        "text-indent" => array(
                        "values" => array(),
                        "input" => "numeric",
                        "group" => "text"),
        "line-height" => array(
                        "values" => array(),
                        "input" => "numeric",
                        "group" => "text"),
        "vertical-align" => array(
                        "values" => array("top", "middle", "bottom", "baseline", "sub", "super",
                            "text-top", "text-bottom"),
                        "input" => "select",
                        "group" => "text"),
        "text-align" => array(
                        "values" => array("left", "center", "right", "justify"),
                        "input" => "select",
                        "group" => "text"),
        "white-space" => array(
                        "values" => array("normal", "pre", "nowrap"),
                        "input" => "select",
                        "group" => "text"),
        "margin" => array(
                        "values" => array(),
                        "input" => "trbl_numeric",
                        "subpar" => array("margin", "margin-top", "margin-right",
                            "margin-bottom", "margin-left"),
                        "group" => "margin_and_padding"),
        "padding" => array(
                        "values" => array(),
                        "input" => "trbl_numeric",
                        "subpar" => array("padding", "padding-top", "padding-right",
                            "padding-bottom", "padding-left"),
                        "group" => "margin_and_padding"),
        "border-width" => array(
                        "values" => array("thin", "medium", "thick"),
                        "input" => "border_width",
                        "subpar" => array("border-width", "border-top-width", "border-right-width",
                            "border-bottom-width", "border-left-width"),
                        "group" => "border"),
        "border-color" => array(
                        "values" => array(),
                        "input" => "trbl_color",
                        "subpar" => array("border-color", "border-top-color", "border-right-color",
                            "border-bottom-color", "border-left-color"),
                        "group" => "border"),
        "border-style" => array(
                        "values" => array("none", "hidden", "dotted", "dashed", "solid", "double",
                            "groove", "ridge", "inset", "outset"),
                        "input" => "border_style",
                        "subpar" => array("border-style", "border-top-style", "border-right-style",
                            "border-bottom-style", "border-left-style"),
                        "group" => "border"),

        "background-color" => array(
                        "values" => array(),
                        "input" => "color",
                        "group" => "background"),
        "background-image" => array(
                        "values" => array(),
                        "input" => "background_image",
                        "group" => "background"),
        "background-repeat" => array(
                        "values" => array("repeat", "repeat-x", "repeat-y", "no-repeat"),
                        "input" => "select",
                        "group" => "background"),
        "background-attachment" => array(
                        "values" => array("fixed", "scroll"),
                        "input" => "select",
                        "group" => "background"),
        "background-position" => array(
                        "values" => array("horizontal" => array("left", "center", "right"),
                            "vertical" => array("top", "center", "bottom")),
                        "input" => "background_position",
                        "group" => "background"),

        "position" => array(
                        "values" => array("absolute", "fixed", "relative", "static"),
                        "input" => "select",
                        "group" => "positioning"),
        "top" => array(
                        "values" => array(),
                        "input" => "numeric",
                        "group" => "positioning"),
        "bottom" => array(
                        "values" => array(),
                        "input" => "numeric",
                        "group" => "positioning"),
        "left" => array(
                        "values" => array(),
                        "input" => "numeric",
                        "group" => "positioning"),
        "right" => array(
                        "values" => array(),
                        "input" => "numeric",
                        "group" => "positioning"),
        "width" => array(
                        "values" => array(),
                        "input" => "numeric",
                        "group" => "positioning"),
        "height" => array(
                        "values" => array(),
                        "input" => "numeric",
                        "group" => "positioning"),
        "min-height" => array(
                        "values" => array(),
                        "input" => "numeric",
                        "group" => "positioning"),
        "float" => array(
                        "values" => array("left", "right", "none"),
                        "input" => "select",
                        "group" => "positioning"),
        "overflow" => array(
                        "values" => array("visible", "hidden", "scroll", "auto"),
                        "input" => "select",
                        "group" => "positioning"),
        "opacity" => array(
                        "values" => array(),
                        "input" => "percentage",
                        "group" => "special"),
        "transform" => array(
                        "values" => array("rotate(90deg)", "rotate(180deg)", "rotate(270deg)"),
                        "input" => "select",
                        "group" => "special"),
        "transform-origin" => array(
                        "values" => array(	"horizontal" => array("left", "center", "right"),
                                            "vertical" => array("top", "center", "bottom")),
                        "input" => "background_position",
                        "group" => "special"),
        "cursor" => array(
                        "values" => array("auto", "default", "crosshair", "pointer", "move",
                            "n-resize", "ne-resize", "e-resize", "se-resize", "s-resize", "sw-resize",
                            "w-resize", "nw-resize", "text", "wait", "help"),
                        "input" => "select",
                        "group" => "special"),
        "clear" => array(
                        "values" => array("both","left","right","none"),
                        "input" => "select",
                        "group" => "special"),

        "list-style-type.ol" => array(
                        "values" => array("decimal","lower-roman","upper-roman",
                            "lower-alpha", "upper-alpha", "lower-greek", "hebrew",
                            "decimal-leading-zero", "cjk-ideographic", "hiragana",
                            "katakana", "hiragana-iroha", "katakana-iroha", "none"),
                        "input" => "select",
                        "group" => "ol"),
        "list-style-type.ul" => array(
                        "values" => array("disc","circle","square",
                            "none"),
                        "input" => "select",
                        "group" => "ul"),
        "list-style-image.ul" => array(
                        "values" => array(),
                        "input" => "background_image",
                        "group" => "ul"),
        "list-style-position.ol" => array(
                        "values" => array("inside","outside"),
                        "input" => "select",
                        "group" => "ol"),
        "list-style-position.ul" => array(
                        "values" => array("inside","outside"),
                        "input" => "select",
                        "group" => "ul"
                        ),
        "border-collapse" => array(
                        "values" => array("collapse","separate"),
                        "input" => "select",
                        "group" => "table"
                        ),
        "caption-side" => array(
                        "values" => array("top","bottom","left","right"),
                        "input" => "select",
                        "group" => "table"
                        )
        );

    // filter groups of properties that should only be
    // displayed with matching tag (group -> tags)
    public static array $filtered_groups =
            array("ol" => array("ol"), "ul" => array("ul"),
                "table" => array("table"), "positioning" => array("h1", "h2", "h3", "div", "img", "table", "a", "figure"));

    // style types and their super type
    public static array $style_super_types = array(
        "text_block" => array("text_block", "heading1", "heading2", "heading3", "code_block"),
        "text_inline" => array("text_inline", "sub", "sup", "code_inline"),
        "section" => array("section"),
        "link" => array("link"),
        "table" => array("table", "table_cell", "table_caption"),
        "list" => array("list_o", "list_u", "list_item"),
        "flist" => array("flist_cont", "flist_head", "flist", "flist_li", "flist_a"),
        "media" => array("media_cont", "media_caption", "iim", "marker"),
        "tabs" => array("va_cntr", "va_icntr", "va_ihead", "va_iheada", "va_ihcap", "va_icont",
            "ha_cntr", "ha_icntr", "ha_ihead", "ha_iheada", "ha_ihcap", "ha_icont", "ca_cntr", "ca_icntr", "ca_ihead", "ca_icont"),
        "question" => array("question", "qtitle", "qanswer", "qinput", "qlinput", "qsubmit", "qfeedr", "qfeedw",
            "qimg", "qordul", "qordli", "qimgd", "qetitem", "qetcorr", "qover"),
        "page" => array("page_frame", "page_cont", "page_title", "page_fn",
            "page_tnav", "page_bnav", "page_lnav", "page_rnav", "page_lnavlink", "page_rnavlink",
            "page_lnavimage", "page_rnavimage")
        );

    // these types are expandable, i.e. the user can define new style classes
    public static array $expandable_types = array(
            "text_block",
            "text_inline", "section", "media_cont", "media_caption", "table", "table_cell", "flist_li", "table_caption",
                "list_o", "list_u",
                "va_cntr", "va_icntr", "va_ihead", "va_iheada", "va_ihcap", "va_icont",
                "ha_cntr", "ha_icntr", "ha_ihead", "ha_iheada", "ha_ihcap", "ha_icont",
                "ca_cntr", "ca_icntr", "ca_ihead", "ca_icont"
        );

    // these types can be hidden in the content editor
    public static array $hideable_types = array(
            "table", "table_cell"
        );

    // tag that are used by style types
    public static array $assigned_tags = array(
        "text_block" => "div",
        "heading1" => "h1",
        "heading2" => "h2",
        "heading3" => "h3",
        "code_block" => "pre",
        "text_inline" => "span",
        "code_inline" => "code",
        "sup" => "sup",
        "sub" => "sub",
        "section" => "div",
        "link" => "a",
        "table" => "table",
        "table_cell" => "td",
        "table_caption" => "caption",
        "media_cont" => "figure",
        "media_caption" => "div",
        "iim" => "div",
        "marker" => "a",
        "list_o" => "ol",
        "list_u" => "ul",
        "list_item" => "li",
        "flist_cont" => "div",
        "flist_head" => "div",
        "flist" => "ul",
        "flist_li" => "li",
        "flist_a" => "a",
        "question" => "div",
        "qtitle" => "div",
        "qanswer" => "div",
        "qimg" => "img",
        "qimgd" => "a",
        "qordul" => "ul",
        "qordli" => "li",
        "qetitem" => "a",
        "qetcorr" => "span",
        "qinput" => "input",
        "qlinput" => "textarea",
        "qsubmit" => "input",
        "qfeedr" => "div",
        "qfeedw" => "div",
        "qover" => "div",
        "page_frame" => "div",
        "page_cont" => "div",
        "page_fn" => "div",
        "page" => "div",
        "page_tnav" => "div",
        "page_bnav" => "div",
        "page_lnav" => "div",
        "page_rnav" => "div",
        "page_lnavlink" => "a",
        "page_rnavlink" => "a",
        "page_lnavimage" => "img",
        "page_rnavimage" => "img",
        "page_title" => "h1",
        "va_cntr" => "div",
        "va_icntr" => "div",
        "va_icont" => "div",
        "va_ihead" => "div",
        "va_iheada" => "div",
        "va_ihcap" => "div",
        "ha_cntr" => "div",
        "ha_icntr" => "div",
        "ha_icont" => "div",
        "ha_iheada" => "div",
        "ha_ihcap" => "div",
        "ha_ihead" => "div",
        "ca_cntr" => "div",
        "ca_icntr" => "div",
        "ca_ihead" => "div",
        "ca_icont" => "div"
        );

    // pseudo classes
    public static array $pseudo_classes =
        array("a" => array("hover"), "div" => array("hover"), "img" => array("hover"));

    // core styles these styles MUST exists -> see also basic_style/style.xml
    public static array $core_styles = array(
            array("type" => "text_block", "class" => "Standard"),
            array("type" => "text_block", "class" => "List"),
            array("type" => "text_block", "class" => "TableContent"),
            array("type" => "code_block", "class" => "Code"),
            array("type" => "heading1", "class" => "Headline1"),
            array("type" => "heading2", "class" => "Headline2"),
            array("type" => "heading3", "class" => "Headline3"),
            array("type" => "text_inline", "class" => "Comment"),
            array("type" => "text_inline", "class" => "Emph"),
            array("type" => "text_inline", "class" => "Quotation"),
            array("type" => "text_inline", "class" => "Strong"),
            array("type" => "text_inline", "class" => "Accent"),
            array("type" => "text_inline", "class" => "Important"),
            array("type" => "code_inline", "class" => "CodeInline"),
            array("type" => "sup", "class" => "Sup"),
            array("type" => "sub", "class" => "Sub"),
            array("type" => "link", "class" => "IntLink"),
            array("type" => "link", "class" => "ExtLink"),
            array("type" => "link", "class" => "FootnoteLink"),
            array("type" => "link", "class" => "FileLink"),
            array("type" => "link", "class" => "GlossaryLink"),
            array("type" => "media_cont", "class" => "MediaContainer"),
            array("type" => "media_cont", "class" => "MediaContainerMax50"),
            array("type" => "media_cont", "class" => "MediaContainerFull100"),
            array("type" => "table", "class" => "StandardTable"),
            array("type" => "media_caption", "class" => "MediaCaption"),
            array("type" => "iim", "class" => "ContentPopup"),
            array("type" => "marker", "class" => "Marker"),
            array("type" => "page_frame", "class" => "PageFrame"),
            array("type" => "page_cont", "class" => "PageContainer"),
            array("type" => "page", "class" => "Page"),
            array("type" => "page_tnav", "class" => "TopNavigation"),
            array("type" => "page_bnav", "class" => "BottomNavigation"),
            array("type" => "page_lnav", "class" => "LeftNavigation"),
            array("type" => "page_rnav", "class" => "RightNavigation"),
            array("type" => "page_lnavlink", "class" => "LeftNavigationLink"),
            array("type" => "page_rnavlink", "class" => "RightNavigationLink"),
            array("type" => "page_lnavimage", "class" => "LeftNavigationImage"),
            array("type" => "page_rnavimage", "class" => "RightNavigationImage"),
            array("type" => "page_fn", "class" => "Footnote"),
            array("type" => "page_title", "class" => "PageTitle"),
            array("type" => "list_o", "class" => "NumberedList"),
            array("type" => "list_u", "class" => "BulletedList"),
            array("type" => "list_item", "class" => "StandardListItem"),
            array("type" => "question", "class" => "Standard"),
            array("type" => "question", "class" => "SingleChoice"),
            array("type" => "question", "class" => "MultipleChoice"),
            array("type" => "question", "class" => "TextQuestion"),
            array("type" => "question", "class" => "OrderingQuestion"),
            array("type" => "question", "class" => "MatchingQuestion"),
            array("type" => "question", "class" => "ImagemapQuestion"),
            array("type" => "question", "class" => "ErrorText"),
            array("type" => "question", "class" => "TextSubset"),
            array("type" => "question", "class" => "ClozeTest"),
            array("type" => "qtitle", "class" => "Title"),
            array("type" => "qanswer", "class" => "Answer"),
            array("type" => "qimg", "class" => "QuestionImage"),
            array("type" => "qimgd", "class" => "ImageDetailsLink"),
            array("type" => "qordul", "class" => "OrderList"),
            array("type" => "qordli", "class" => "OrderListItem"),
            array("type" => "qordul", "class" => "OrderListHorizontal"),
            array("type" => "qordli", "class" => "OrderListItemHorizontal"),
            array("type" => "qetitem", "class" => "ErrorTextItem"),
            array("type" => "qetitem", "class" => "ErrorTextSelected"),
            array("type" => "qetcorr", "class" => "ErrorTextCorrected"),
            array("type" => "qinput", "class" => "TextInput"),
            array("type" => "qlinput", "class" => "LongTextInput"),
            array("type" => "qsubmit", "class" => "Submit"),
            array("type" => "qfeedr", "class" => "FeedbackRight"),
            array("type" => "qfeedw", "class" => "FeedbackWrong"),
            array("type" => "qover", "class" => "Correct"),
            array("type" => "qover", "class" => "Inorrect"),
            array("type" => "qover", "class" => "StatusMessage"),
            array("type" => "qover", "class" => "WrongAnswersMessage"),
            array("type" => "flist_cont", "class" => "FileListContainer"),
            array("type" => "flist_head", "class" => "FileListHeading"),
            array("type" => "flist", "class" => "FileList"),
            array("type" => "flist_li", "class" => "FileListItem"),
            array("type" => "flist_a", "class" => "FileListItemLink")
        );

    public static array $templates = array(
        "table" => array(
            "table" => "table",
            "caption" => "table_caption",
            "row_head" => "table_cell",
            "row_foot" => "table_cell",
            "col_head" => "table_cell",
            "col_foot" => "table_cell",
            "odd_row" => "table_cell",
            "even_row" => "table_cell",
            "odd_col" => "table_cell",
            "even_col" => "table_cell"),
        "vaccordion" => array(
            "va_cntr" => "va_cntr",
            "va_icntr" => "va_icntr",
            "va_ihead" => "va_ihead",
            "va_iheada" => "va_iheada",
            "va_ihcap" => "va_ihcap",
            "va_icont" => "va_icont"
            ),
        "haccordion" => array(
            "ha_cntr" => "ha_cntr",
            "ha_icntr" => "ha_icntr",
            "ha_ihead" => "ha_ihead",
            "ha_iheada" => "ha_iheada",
            "ha_ihcap" => "ha_ihcap",
            "ha_icont" => "ha_icont"
        ),
        "carousel" => array(
            "ca_cntr" => "ca_cntr",
            "ca_icntr" => "ca_icntr",
            "ca_ihead" => "ca_ihead",
            "ca_icont" => "ca_icont"
            )
        );

    // basic style xml file, image directory and dom
    protected static string $basic_style_file = "./libs/ilias/Style/basic_style/style.xml";
    protected static string $basic_style_zip = "./libs/ilias/Style/basic_style/style.zip";
    protected static string $basic_style_image_dir = "./libs/ilias/Style/basic_style/images";
    protected static ?DOMDocument $basic_style_dom = null;

    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = false
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->type = "sty";
        $this->style = array();
        $this->ilias = $DIC["ilias"];

        if ($a_call_by_reference) {
            $this->ilias->raiseError("Can't instantiate style object via reference id.", $this->ilias->error_obj->FATAL);
        }
        parent::__construct($a_id, false);
    }

    public static function getBasicZipPath(): string
    {
        return self::$basic_style_zip;
    }

    /**
     * Set style up to date (false + update will trigger css generation next time)
     */
    public function setUpToDate(bool $a_up_to_date = true): void
    {
        $this->up_to_date = $a_up_to_date;
    }

    public function getUpToDate(): bool
    {
        return $this->up_to_date;
    }

    public function setScope(int $a_scope): void
    {
        $this->scope = $a_scope;
    }

    public function getScope(): int
    {
        return $this->scope;
    }

    public static function _writeUpToDate(
        int $a_id,
        bool $a_up_to_date
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "UPDATE style_data SET uptodate = " .
            $ilDB->quote((int) $a_up_to_date, "integer") .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $ilDB->manipulate($q);
    }

    public static function writeOwner($obj_id, $style_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = "UPDATE style_data SET owner_obj = " .
            $ilDB->quote((int) $obj_id, "integer") .
            " WHERE id = " . $ilDB->quote($style_id, "integer");
        $ilDB->manipulate($q);
    }

    public static function _lookupUpToDate(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT uptodate FROM style_data " .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $res = $ilDB->query($q);
        $sty = $ilDB->fetchAssoc($res);

        return (bool) $sty["uptodate"];
    }

    /**
     * Write standard flag
     */
    public static function _writeStandard(
        int $a_id,
        bool $a_std
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "UPDATE style_data SET standard = " .
            $ilDB->quote((int) $a_std, "integer") .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $ilDB->manipulate($q);
    }

    public static function _writeScope(int $a_id, int $a_scope): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "UPDATE style_data SET category = " .
            $ilDB->quote($a_scope, "integer") .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $ilDB->manipulate($q);
    }

    /**
     * Lookup standard flag
     */
    public static function _lookupStandard(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM style_data " .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $res = $ilDB->query($q);
        $sty = $ilDB->fetchAssoc($res);

        return (bool) ($sty["standard"] ?? false);
    }

    public static function _writeActive(int $a_id, bool $a_active): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "UPDATE style_data SET active = " .
            $ilDB->quote((int) $a_active, "integer") .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $ilDB->manipulate($q);
    }

    /**
     * Lookup active flag
     */
    public static function _lookupActive(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM style_data " .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $res = $ilDB->query($q);
        $sty = $ilDB->fetchAssoc($res);

        return (bool) $sty["active"];
    }

    /**
     * Get standard styles
     * @return array<int,string>
     */
    public static function _getStandardStyles(
        bool $a_exclude_default_style = false,
        bool $a_include_deactivated = false,
        int $a_scope = 0
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();
        $tree = $DIC->repositoryTree();

        $default_style = $ilSetting->get("default_content_style_id");

        $and_str = "";
        if (!$a_include_deactivated) {
            $and_str = " AND active = 1";
        }

        $q = "SELECT * FROM style_data " .
            " WHERE standard = 1" . $and_str;
        $res = $ilDB->query($q);
        $styles = array();
        while ($sty = $ilDB->fetchAssoc($res)) {
            if (!$a_exclude_default_style || $default_style != $sty["id"]) {
                // check scope
                if ($a_scope > 0 && $sty["category"] > 0) {
                    if ($tree->isInTree((int) $sty["category"]) &&
                        $tree->isInTree($a_scope)) {
                        $path = $tree->getPathId($a_scope);
                        if (!in_array((int) $sty["category"], $path)) {
                            continue;
                        }
                    }
                }
                $styles[(int) $sty["id"]] = ilObject::_lookupTitle((int) $sty["id"]);
            }
        }

        return $styles;
    }


    /**
     * Get all clonable styles (active standard styles and individual learning
     * module styles with write permission).
     * @return array<int,string>
     */
    public static function _getClonableContentStyles(): array
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilDB = $DIC->database();

        $clonable_styles = array();

        $q = "SELECT * FROM style_data";
        $style_set = $ilDB->query($q);
        while ($style_rec = $ilDB->fetchAssoc($style_set)) {
            $clonable = false;
            if ($style_rec["standard"] == 1) {
                if ($style_rec["active"] == 1) {
                    $clonable = true;
                }
            } else {
                $obj_ids = ilObjContentObject::_lookupContObjIdByStyleId((int) $style_rec["id"]);
                if (count($obj_ids) == 0) {
                    $obj_ids = self::lookupObjectForStyle((int) $style_rec["id"]);
                }
                foreach ($obj_ids as $id) {
                    $ref = ilObject::_getAllReferences((int) $id);
                    foreach ($ref as $ref_id) {
                        if ($ilAccess->checkAccess("write", "", $ref_id)) {
                            $clonable = true;
                        }
                    }
                }
            }
            if ($clonable) {
                $clonable_styles[(int) $style_rec["id"]] =
                    ilObject::_lookupTitle((int) $style_rec["id"]);
            }
        }

        asort($clonable_styles);

        return $clonable_styles;
    }

    public static function _getBasicStyleDom(): DOMDocument
    {
        if (!is_object(self::$basic_style_dom)) {
            self::$basic_style_dom = new DOMDocument();
            self::$basic_style_dom->load(self::$basic_style_file);
        }

        return self::$basic_style_dom;
    }

    public static function getBasicImageDir(): string
    {
        return self::$basic_style_image_dir;
    }


    /**
     * Create a new style
     */
    public function create(
        int $a_from_style = 0,
        bool $a_import_mode = false
    ): int {
        global $DIC;

        $ilDB = $this->db;

        $id = parent::create();

        $service = $DIC->contentStyle()
            ->internal();
        $access_manager = $service->domain()->access(
            0,
            $DIC->user()->getId()
        );
        $access_manager->enableWrite(true);
        $color_manager = $service->domain()->color($this->getId(), $access_manager);

        if ($a_from_style == 0) {
            if (!$a_import_mode) {
                // copy styles from basic style
                $this->createFromXMLFile(self::$basic_style_file, true);

                // copy images from basic style
                $this->createImagesDirectory();

                // cross filesystem (lib -> web) rCopy
                ilFileUtils::rCopy(
                    self::$basic_style_image_dir,
                    $this->getImagesDirectory()
                );
            } else {
                // add style_data record
                $q = "INSERT INTO style_data (id, uptodate, category) VALUES " .
                    "(" . $ilDB->quote($this->getId(), "integer") . ", 0," .
                    $ilDB->quote($this->getScope(), "integer") . ")";
                $ilDB->manipulate($q);
                ilObjStyleSheet::_createImagesDirectory($this->getId());
            }
        } else {
            // get style parameter records
            $def = array();
            $q = "SELECT * FROM style_parameter WHERE style_id = " .
                $ilDB->quote($a_from_style, "integer");
            $par_set = $ilDB->query($q);
            while ($par_rec = $ilDB->fetchAssoc($par_set)) {
                $def[] = array("tag" => $par_rec["tag"], "class" => $par_rec["class"],
                    "parameter" => $par_rec["parameter"], "value" => $par_rec["value"],
                    "type" => $par_rec["type"], "mq_id" => $par_rec["mq_id"], "custom" => $par_rec["custom"]);
            }

            // get style characteristics records
            $chars = array();
            $q = "SELECT * FROM style_char WHERE style_id = " .
                $ilDB->quote($a_from_style, "integer");
            $par_set = $ilDB->query($q);
            while ($par_rec = $ilDB->fetchAssoc($par_set)) {
                $chars[] = array("type" => $par_rec["type"], "characteristic" => $par_rec["characteristic"]);
            }


            // copy media queries
            $from_style = new ilObjStyleSheet($a_from_style);
            $mqs = $from_style->getMediaQueries();
            $mq_mapping = array();
            foreach ($mqs as $mq) {
                $nid = $this->addMediaQuery($mq["mquery"]);
                $mq_mapping[$mq["id"]] = $nid;
            }

            // default style settings
            foreach ($def as $sty) {
                $id = $ilDB->nextId("style_parameter");
                $q = "INSERT INTO style_parameter (id, style_id, tag, class, parameter, value, type, mq_id, custom) VALUES " .
                    "(" .
                    $ilDB->quote($id, "integer") . "," .
                    $ilDB->quote($this->getId(), "integer") . "," .
                    $ilDB->quote($sty["tag"], "text") . "," .
                    $ilDB->quote($sty["class"], "text") . "," .
                    $ilDB->quote($sty["parameter"], "text") . "," .
                    $ilDB->quote($sty["value"], "text") . "," .
                    $ilDB->quote($sty["type"], "text") . "," .
                    $ilDB->quote((int) ($mq_mapping[$sty["mq_id"]] ?? 0), "integer") . "," .
                    $ilDB->quote($sty["custom"], "integer") .
                    ")";
                $ilDB->manipulate($q);
            }

            // insert style characteristics
            foreach ($chars as $char) {
                $q = "INSERT INTO style_char (style_id, type, characteristic) VALUES " .
                    "(" . $ilDB->quote($this->getId(), "integer") . "," .
                    $ilDB->quote($char["type"], "text") . "," .
                    $ilDB->quote($char["characteristic"], "text") . ")";
                $ilDB->manipulate($q);
            }

            // add style_data record
            $q = "INSERT INTO style_data (id, uptodate, category) VALUES " .
                "(" . $ilDB->quote($this->getId(), "integer") . ", 0," .
                $ilDB->quote($this->getScope(), "integer") . ")";
            $ilDB->manipulate($q);

            // copy images
            $this->createImagesDirectory();
            ilFileUtils::rCopy(
                $from_style->getImagesDirectory(),
                $this->getImagesDirectory()
            );

            // copy colors
            $colors = $from_style->getColors();
            foreach ($colors as $c) {
                $color_manager->addColor($c["name"], $c["code"]);
            }

            // copy templates
            $tcts = ilObjStyleSheet::_getTemplateClassTypes();
            foreach ($tcts as $tct => $v) {
                $templates = $from_style->getTemplates($tct);
                foreach ($templates as $t) {
                    $this->addTemplate($tct, $t["name"], $t["classes"]);
                }
            }
        }

        $this->read();
        if (!$a_import_mode) {
            $this->writeCSSFile();
        }

        return $id;
    }

    /**
     * Check whether characteristic exists
     */
    public function characteristicExists(
        string $a_char,
        string $a_style_type
    ): bool {
        $ilDB = $this->db;

        $set = $ilDB->queryF(
            "SELECT style_id FROM style_char WHERE style_id = %s AND characteristic = %s AND type = %s",
            array("integer", "text", "text"),
            array($this->getId(), $a_char, $a_style_type)
        );
        if ($ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function addCharacteristic(
        string $a_type,
        string $a_char,
        bool $a_hidden = false
    ): void {
        $ilDB = $this->db;

        // delete characteristic record
        $ilDB->manipulateF(
            "INSERT INTO style_char (style_id, type, characteristic, hide)" .
            " VALUES (%s,%s,%s,%s) ",
            array("integer", "text", "text", "integer"),
            array($this->getId(), $a_type, $a_char, $a_hidden)
        );

        $this->setUpToDate(false);
        $this->_writeUpToDate($this->getId(), false);
    }

    /**
     * Get characteristics
     */
    public function getCharacteristics(
        string $a_type = "",
        bool $a_no_hidden = false,
        bool $a_include_core = true
    ): array {
        $chars = array();

        if ($a_type == "") {
            $chars = $this->chars;
        }
        if (isset($this->chars_by_type[$a_type])) {
            foreach ($this->chars_by_type[$a_type] as $c) {
                if ($a_include_core || !self::isCoreStyle($a_type, $c)) {
                    $chars[] = $c;
                }
            }
        }

        if ($a_no_hidden) {
            foreach ($chars as $k => $char) {
                if ($a_type == "" && $this->hidden_chars[$char["type"] . ":" . $char["class"]]) {
                    unset($chars[$k]);
                } elseif ($this->hidden_chars[$a_type . ":" . $char]) {
                    unset($chars[$k]);
                }
            }
        }

        return $chars;
    }

    public function setCharacteristics(array $a_chars): void
    {
        $this->chars = $a_chars;
    }

    /**
     * Save characteristic hidden status
     */
    public function saveHideStatus(
        string $a_type,
        string $a_char,
        bool $a_hide
    ): void {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "UPDATE style_char SET " .
            " hide = " . $ilDB->quote((int) $a_hide, "integer") .
            " WHERE style_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            " type = " . $ilDB->quote($a_type, "text") . " AND " .
            " characteristic = " . $ilDB->quote($a_char, "text")
        );
    }

    /**
     * Get characteristic hidden status
     */
    public function getHideStatus(
        string $a_type,
        string $a_char
    ): bool {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT hide FROM  style_char " .
            " WHERE style_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            " type = " . $ilDB->quote($a_type, "text") . " AND " .
            " characteristic = " . $ilDB->quote($a_char, "text")
        );
        $rec = $ilDB->fetchAssoc($set);

        return (bool) ($rec["hide"] ?? false);
    }

    /**
     * clone style sheet (note: styles have no ref ids and return an object id)
     */
    public function ilClone(): int
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("style");

        $new_obj = new ilObjStyleSheet();
        $new_obj->setTitle($this->getTitle() . " (" . $lng->txt("sty_acopy") . ")");
        $new_obj->setType($this->getType());
        $new_obj->setDescription($this->getDescription());
        $new_obj->create($this->getId());

        $new_obj->writeStyleSetting(
            "disable_auto_margins",
            $this->lookupStyleSetting("disable_auto_margins")
        );

        return $new_obj->getId();
    }

    /**
     * Copy images to directory
     */
    public function copyImagesToDir(string $a_target): void
    {
        ilFileUtils::rCopy($this->getImagesDirectory(), $a_target);
    }

    /**
     * write style parameter to db
     *
     * todo check usages add mq_id
     *
     * @param	string		$a_tag		tag name		(tag.class, e.g. "div.Mnemonic")
     * @param	string		$a_par		tag parameter	(e.g. "margin-left")
     * @param	string		$a_type		style type		(e.g. "section")
     */
    public function addParameter(
        string $a_tag,
        string $a_par,
        string $a_type,
        int $a_mq_id = 0,
        bool $a_custom = false
    ): void {
        $ilDB = $this->db;

        $avail_params = $this->getAvailableParameters();
        $tag = explode(".", $a_tag);
        $value = $avail_params[$a_par][0];
        $id = $ilDB->nextId("style_parameter");
        $q = "INSERT INTO style_parameter (id,style_id, type, tag, class, parameter, value, mq_id, custom) VALUES " .
            "(" .
            $ilDB->quote($id, "integer") . "," .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($a_type, "text") . "," .
            $ilDB->quote($tag[0], "text") . "," .
            $ilDB->quote($tag[1], "text") . "," .
            $ilDB->quote($a_par, "text") . "," .
            $ilDB->quote($value, "text") . "," .
            $ilDB->quote($a_mq_id, "integer") . "," .
            $ilDB->quote($a_custom, "integer") .
            ")";
        $ilDB->manipulate($q);
        $this->read();
        $this->writeCSSFile();
    }

    /**
     * Create images directory
     * <data_dir>/sty/sty_<id>/images
     */
    public function createImagesDirectory(): void
    {
        ilObjStyleSheet::_createImagesDirectory($this->getId());
    }

    /**
     * Create images directory
     * <data_dir>/sty/sty_<id>/images
     */
    public static function _createImagesDirectory(
        int $a_style_id
    ): void {
        global $DIC;

        $ilErr = $DIC["ilErr"];

        $sty_data_dir = ilFileUtils::getWebspaceDir() . "/sty";
        if (!is_dir($sty_data_dir)) {
            ilFileUtils::makeDir($sty_data_dir);
        }
        if (!is_writable($sty_data_dir)) {
            $ilErr->raiseError("Style data directory (" . $sty_data_dir
                . ") not writeable.", $ilErr->FATAL);
        }

        $style_dir = $sty_data_dir . "/sty_" . $a_style_id;
        if (!is_dir($style_dir)) {
            ilFileUtils::makeDir($style_dir);
        }
        if (!is_dir($style_dir)) {
            $ilErr->raiseError("Creation of style directory failed (" .
                $style_dir . ").", $ilErr->FATAL);
        }

        // create images subdirectory
        $im_dir = $style_dir . "/images";
        if (!is_dir($im_dir)) {
            ilFileUtils::makeDir($im_dir);
        }
        if (!is_dir($im_dir)) {
            $ilErr->raiseError("Creation of Import Directory failed (" .
                $im_dir . ").", $ilErr->FATAL);
        }

        // create thumbnails directory
        $thumb_dir = $style_dir . "/images/thumbnails";
        ilFileUtils::makeDir($thumb_dir);
        if (!is_dir($thumb_dir)) {
            $ilErr->raiseError("Creation of Import Directory failed (" .
                $thumb_dir . ").", $ilErr->FATAL);
        }
    }

    public function getImagesDirectory(): string
    {
        return ilObjStyleSheet::_getImagesDirectory($this->getId());
    }

    public static function _getImagesDirectory(int $a_style_id): string
    {
        return ilFileUtils::getWebspaceDir() . "/sty/sty_" . $a_style_id .
            "/images";
    }

    public function getThumbnailsDirectory(): string
    {
        return $this->getImagesDirectory() .
            "/thumbnails";
    }

    /**
     * delete style parameter
     */
    public function deleteParameter(int $a_id): void
    {
        $ilDB = $this->db;

        $q = "DELETE FROM style_parameter WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $ilDB->query($q);
    }


    /**
     * Delete style parameter by tag/class/parameter
     * @param string $a_tag tag
     * @param string $a_class class
     * @param string $a_type type
     * @param int $a_mq_id media query id
     */
    public function deleteCustomStylePars(
        string $a_tag,
        string $a_class,
        string $a_type,
        int $a_mq_id = 0
    ): void {
        $ilDB = $this->db;

        $q = "DELETE FROM style_parameter WHERE " .
            " style_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            " tag = " . $ilDB->quote($a_tag, "text") . " AND " .
            " class = " . $ilDB->quote($a_class, "text") . " AND " .
            " mq_id = " . $ilDB->quote($a_mq_id, "integer") . " AND " .
            " custom = " . $ilDB->quote(1, "integer") . " AND " .
            " " . $ilDB->equals("type", $a_type, "text", true);

        $ilDB->manipulate($q);
    }

    /**
     * Delete style parameters of characteristic
     */
    public function deleteStyleParOfChar(
        string $a_type,
        string $a_class
    ): void {
        $ilDB = $this->db;

        $q = "DELETE FROM style_parameter WHERE " .
            " style_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            " class = " . $ilDB->quote($a_class, "text") . " AND " .
            " " . $ilDB->equals("type", $a_type, "text", true);

        $ilDB->manipulate($q);
    }


    public function delete(): bool
    {
        $ilDB = $this->db;

        // delete object
        parent::delete();

        // check whether this style is global default
        $def_style = $this->ilias->getSetting("default_content_style_id");
        if ($def_style == $this->getId()) {
            $this->ilias->deleteSetting("default_content_style_id");
        }

        // check whether this style is global fixed
        $fixed_style = $this->ilias->getSetting("fixed_content_style_id");
        if ($fixed_style == $this->getId()) {
            $this->ilias->deleteSetting("fixed_content_style_id");
        }

        // delete style parameter
        $q = "DELETE FROM style_parameter WHERE style_id = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        // delete style file
        $css_file_name = ilFileUtils::getWebspaceDir() . "/css/style_" . $this->getId() . ".css";
        if (is_file($css_file_name)) {
            unlink($css_file_name);
        }

        // delete media queries
        $ilDB->manipulate(
            "DELETE FROM sty_media_query WHERE " .
            " style_id = " . $ilDB->quote($this->getId(), "integer")
        );

        // delete entries in learning modules
        ilObjContentObject::_deleteStyleAssignments($this->getId());

        // delete style data record
        $q = "DELETE FROM style_data WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        return true;
    }


    /**
    * read style properties
    */
    public function read(): void
    {
        $ilDB = $this->db;

        parent::read();

        $q = "SELECT * FROM style_parameter WHERE style_id = " .
            $ilDB->quote($this->getId(), "integer") . " ORDER BY tag, class, type, mq_id ";
        $style_set = $ilDB->query($q);
        $ctag = "";
        $cclass = "";
        $ctype = "";
        $cmq_id = 0;
        $this->style = array();
        // workaround for bug #17586, see also http://stackoverflow.com/questions/3066356/multiple-css-classes-properties-overlapping-based-on-the-order-defined
        // e.g. ha_iheada must be written after ha_ihead, since they are acting on the same dom node
        // styles that must be added at the end
        $this->end_styles = array();
        $tag = null;
        while ($style_rec = $ilDB->fetchAssoc($style_set)) {
            if ($style_rec["tag"] != $ctag || $style_rec["class"] != $cclass
                || $style_rec["type"] != $ctype || $style_rec["mq_id"] != $cmq_id) {
                // add current tag array to style array
                if (is_array($tag)) {
                    if (in_array($ctype, array("ha_iheada", "va_iheada"))) {
                        $this->end_styles[] = $tag;
                    } else {
                        $this->style[] = $tag;
                    }
                }
                $tag = array();
            }
            $ctag = $style_rec["tag"];
            $cclass = $style_rec["class"];
            $ctype = $style_rec["type"];
            $cmq_id = $style_rec["mq_id"];
            $tag[] = $style_rec;
            // added $cmq_id
            $this->style_class[$ctype][$cclass][$cmq_id][$style_rec["parameter"]] = $style_rec["value"];
        }
        if (is_array($tag)) {
            $this->style[] = $tag;
        }
        foreach ($this->end_styles as $s) {
            $this->style[] = $s;
        }
        //var_dump($this->style_class);
        $q = "SELECT * FROM style_data WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $res = $ilDB->query($q);
        $sty = $ilDB->fetchAssoc($res);
        $this->setUpToDate((bool) $sty["uptodate"]);
        $this->setScope((int) $sty["category"]);

        // get style characteristics records
        $this->chars = array();
        $this->chars_by_type = array();
        $q = "SELECT * FROM style_char WHERE style_id = " .
            $ilDB->quote($this->getId(), "integer") .
            " ORDER BY type ASC, characteristic ASC";
        $par_set = $ilDB->query($q);
        while ($par_rec = $ilDB->fetchAssoc($par_set)) {
            $this->chars[] = array("type" => $par_rec["type"], "class" => $par_rec["characteristic"], "hide" => $par_rec["hide"]);
            $this->chars_by_type[$par_rec["type"]][] = $par_rec["characteristic"];
            if ($par_rec["hide"]) {
                $this->hidden_chars[$par_rec["type"] . ":" . $par_rec["characteristic"]] = true;
            }
        }
        //		var_dump($this->style); exit;
    }

    /**
     * write css file to webspace directory
     */
    public function writeCSSFile(
        string $a_target_file = "",
        string $a_image_dir = ""
    ): void {
        $style = $this->getStyle();

        if (!is_dir(ilFileUtils::getWebspaceDir() . "/css")) {
            ilFileUtils::makeDirParents(ilFileUtils::getWebspaceDir() . "/css");
        }

        if ($a_target_file == "") {
            $css_file_name = ilFileUtils::getWebspaceDir() . "/css/style_" . $this->getId() . ".css";
        } else {
            $css_file_name = $a_target_file;
        }
        $css_file = fopen($css_file_name, 'wb');

        $page_background = "";

        $mqs = array(array("mquery" => "", "id" => 0));
        foreach ($this->getMediaQueries() as $mq) {
            $mqs[] = $mq;
        }

        // iterate all media queries
        foreach ($mqs as $mq) {
            if ($mq["id"] > 0) {
                fwrite($css_file, "@media " . $mq["mquery"] . " {\n");
            }
            reset($style);
            foreach ($style as $tag) {
                if ($tag[0]["mq_id"] != $mq["id"]) {
                    continue;
                }
                fwrite($css_file, $tag[0]["tag"] . ".ilc_" . $tag[0]["type"] . "_" . $tag[0]["class"] . "\n");
                //				echo "<br>";
                //				var_dump($tag[0]["type"]);
                if ($tag[0]["tag"] == "td") {
                    fwrite($css_file, ",th" . ".ilc_" . $tag[0]["type"] . "_" . $tag[0]["class"] . "\n");
                }
                if (in_array($tag[0]["tag"], array("h1", "h2", "h3"))) {
                    fwrite($css_file, ",div.ilc_text_block_" . $tag[0]["class"] . "\n");
                    fwrite($css_file, ",html.il-no-tiny-bg body#tinymce.ilc_text_block_" . $tag[0]["class"] . "\n");
                }
                if ($tag[0]["type"] == "section") {	// sections can use a tags, if links are used
                    fwrite($css_file, ",a.ilc_" . $tag[0]["type"] . "_" . $tag[0]["class"] . "\n");
                }
                if ($tag[0]["type"] == "text_block") {
                    fwrite($css_file, ",html.il-no-tiny-bg body#tinymce.ilc_text_block_" . $tag[0]["class"] . "\n");
                }
                fwrite($css_file, "{\n");

                // collect table border attributes
                $t_border = array();

                foreach ($tag as $par) {
                    $cur_par = $par["parameter"];
                    $cur_val = $par["value"];

                    // replace named colors
                    if (is_int(strpos($cur_par, "color")) && substr(trim($cur_val), 0, 1) == "!") {
                        $cur_val = $this->getColorCodeForName(substr($cur_val, 1));
                    }

                    if ($tag[0]["type"] == "table" && is_int(strpos($par["parameter"], "border"))) {
                        $t_border[$cur_par] = $cur_val;
                    }

                    if (in_array($cur_par, array("background-image", "list-style-image"))) {
                        if (is_int(strpos($cur_val, "/"))) {	// external
                            $cur_val = "url(" . $cur_val . ")";
                        } else {		// internal
                            if ($a_image_dir == "") {
                                $cur_val = "url(../sty/sty_" . $this->getId() . "/images/" . $cur_val . ")";
                            } else {
                                $cur_val = "url(" . $a_image_dir . "/" . $cur_val . ")";
                            }
                        }
                    }

                    if ($cur_par == "opacity") {
                        $cur_val = ((int) $cur_val) / 100;
                    }

                    fwrite($css_file, "\t" . $cur_par . ": " . $cur_val . ";\n");

                    // IE6 fix for minimum height
                    /*
                    if ($cur_par == "min-height") {
                        fwrite($css_file, "\t" . "height" . ": " . "auto !important" . ";\n");
                        fwrite($css_file, "\t" . "height" . ": " . $cur_val . ";\n");
                    }*/

                    // opacity fix
                    if ($cur_par == "opacity") {
                        fwrite($css_file, "\t" . '-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=' . ($cur_val * 100) . ')"' . ";\n");
                        fwrite($css_file, "\t" . 'filter: alpha(opacity=' . ($cur_val * 100) . ')' . ";\n");
                        fwrite($css_file, "\t" . '-moz-opacity: ' . $cur_val . ";\n");
                    }

                    // transform fix
                    if ($cur_par == "transform") {
                        fwrite($css_file, "\t" . '-webkit-transform: ' . $cur_val . ";\n");
                        fwrite($css_file, "\t" . '-moz-transform: ' . $cur_val . ";\n");
                        fwrite($css_file, "\t" . '-ms-transform: ' . $cur_val . ";\n");
                    }

                    // transform-origin fix
                    if ($cur_par == "transform-origin") {
                        fwrite($css_file, "\t" . '-webkit-transform-origin: ' . $cur_val . ";\n");
                        fwrite($css_file, "\t" . '-moz-transform-origin: ' . $cur_val . ";\n");
                        fwrite($css_file, "\t" . '-ms-transform-origin: ' . $cur_val . ";\n");
                    }

                    // save page background
                    if ($tag[0]["tag"] == "div" && $tag[0]["class"] == "Page"
                        && $cur_par == "background-color") {
                        $page_background = $cur_val;
                    }
                }
                fwrite($css_file, "}\n");
                fwrite($css_file, "\n");

                // use table border attributes for th td as well
    /*			if ($tag[0]["type"] == "table")
                {
                    if (count($t_border) > 0)
                    {
                        fwrite ($css_file, $tag[0]["tag"].".ilc_".$tag[0]["type"]."_".$tag[0]["class"]." th,".
                            $tag[0]["tag"].".ilc_".$tag[0]["type"]."_".$tag[0]["class"]." td\n");
                        fwrite ($css_file, "{\n");
                        foreach ($t_border as $p => $v)
                        {
    //						fwrite ($css_file, "\t".$p.": ".$v.";\n");
                        }
                        fwrite ($css_file, "}\n");
                        fwrite ($css_file, "\n");
                    }
                }*/
            }

            if ($page_background != "") {
                fwrite($css_file, "td.ilc_Page\n");
                fwrite($css_file, "{\n");
                fwrite($css_file, "\t" . "background-color: " . $page_background . ";\n");
                fwrite($css_file, "}\n");
            }
            if ($mq["id"] > 0) {
                fwrite($css_file, "}\n");
            }
        }
        fclose($css_file);
        //	exit;
        $this->setUpToDate();
        $this->_writeUpToDate($this->getId(), true);
    }

    /**
     * Get effective Style Id
     *
     * @param int        style id that may be set in object
     * @param	string		object type
     */
    public static function getEffectiveContentStyleId(
        int $a_style_id
    ): int {
        global $DIC;

        $ilSetting = $DIC->settings();

        // check global fixed content style
        $fixed_style = $ilSetting->get("fixed_content_style_id");
        if ($fixed_style > 0) {
            $a_style_id = (int) $fixed_style;
        }

        // check global default style
        if ($a_style_id <= 0) {
            $a_style_id = (int) $ilSetting->get("default_content_style_id");
        }

        if ($a_style_id > 0 && ilObject::_lookupType($a_style_id) === "sty") {
            return $a_style_id;
        }

        return 0;
    }

    public function getParametersOfClass(
        string $a_type,
        string $a_class,
        int $a_mq_id = 0
    ): array {
        if (is_array($this->style_class[$a_type][$a_class][$a_mq_id])) {
            return $this->style_class[$a_type][$a_class][$a_mq_id];
        }
        return array();
    }

    /**
     * get content style path
     * static (to avoid full reading)
     */
    public static function getContentStylePath(
        int $a_style_id,
        bool $add_random = true,
        bool $add_token = true
    ): string {
        global $DIC;

        $ilSetting = $DIC->settings();

        $random = new \ilRandom();
        $rand = $random->int(1, 999999);


        // check global fixed content style
        $fixed_style = $ilSetting->get("fixed_content_style_id");
        if ($fixed_style > 0) {
            $a_style_id = (int) $fixed_style;
        }

        // check global default style
        if ($a_style_id <= 0) {
            $a_style_id = (int) $ilSetting->get("default_content_style_id");
        }

        if ($a_style_id > 0 && ilObject::_exists($a_style_id)) {
            // check whether file is up to date
            if (!ilObjStyleSheet::_lookupUpToDate($a_style_id)) {
                $style = new ilObjStyleSheet($a_style_id);
                $style->writeCSSFile();
            }

            $path = ilFileUtils::getWebspaceDir("output") . "/css/style_" . $a_style_id . ".css";
            if ($add_random) {
                $path .= "?dummy=$rand";
            }
            if ($add_token) {
                $path = ilWACSignedPath::signFile($path);
            }

            return $path;
        } else {		// todo: work this out
            return "./Services/COPage/css/content.css";
        }
    }

    public static function getContentPrintStyle(): string
    {
        return "./Services/COPage/css/print_content.css";
    }

    public static function getSyntaxStylePath(): string
    {
        return "./Services/COPage/css/syntaxhighlight.css";
    }

    public static function getPlaceHolderStylePath(): string
    {
        return "./Services/COPage/css/placeholder.css";
    }

    public function update(): bool
    {
        $ilDB = $this->db;

        parent::update();
        $this->read();				// this could be done better
        $this->writeCSSFile();

        $q = "UPDATE style_data " .
            "SET category = " . $ilDB->quote($this->getScope(), "integer") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        return true;
    }

    /**
     * update style parameter per id
     */
    public function updateStyleParameter(
        int $a_id,
        string $a_value
    ): void {
        $ilDB = $this->db;

        $q = "UPDATE style_parameter SET VALUE = " .
            $ilDB->quote($a_value, "text") . " WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $style_set = $ilDB->manipulate($q);
    }

    public function getStyle(): array
    {
        return $this->style;
    }

    public function setStyle(array $a_style): void
    {
        $this->style = $a_style;
    }

    public function handleXmlString(string $a_str): string
    {
        return str_replace("&", "&amp;", $a_str);
    }

    /**
     * get xml representation of style object
     * todo: add mq_id
     */
    public function getXML(): string
    {
        $xml = "<StyleSheet>\n";

        // title and description
        $xml .= "<Title>" . $this->handleXmlString($this->getTitle()) . "</Title>";
        $xml .= "<Description>" . $this->handleXmlString($this->getDescription()) . "</Description>\n";

        // style classes
        foreach ($this->chars as $char) {
            $xml .= "<Style Tag=\"" . ilObjStyleSheet::_determineTag($char["type"]) .
                "\" Type=\"" . $char["type"] . "\" Class=\"" . $char["class"] . "\">\n";
            foreach ($this->style as $style) {
                if ($style[0]["type"] == $char["type"] && $style[0]["class"] == $char["class"]) {
                    foreach ($style as $tag) {
                        $xml .= "<StyleParameter Name=\"" . $tag["parameter"] . "\" Value=\"" . $tag["value"] . "\" Custom=\"" . $tag["custom"] . "\" />\n";
                    }
                }
            }
            $xml .= "</Style>\n";
        }

        // colors
        foreach ($this->getColors() as $color) {
            $xml .= "<StyleColor Name=\"" . $color["name"] . "\" Code=\"" . $color["code"] . "\"/>\n";
        }

        // templates
        $tcts = ilObjStyleSheet::_getTemplateClassTypes();
        foreach ($tcts as $tct => $v) {
            $ts = $this->getTemplates($tct);

            foreach ($ts as $t) {
                $xml .= "<StyleTemplate Type=\"" . $tct . "\" Name=\"" . $t["name"] . "\">\n";
                foreach ($t["classes"] as $ct => $c) {
                    if ($c != "") {
                        $xml .= "<StyleTemplateClass ClassType=\"" . $ct . "\" Class=\"" . $c . "\"/>\n";
                    }
                }
                $xml .= "</StyleTemplate>\n";
            }
        }


        $xml .= "</StyleSheet>";
        //echo "<pre>".htmlentities($xml)."</pre>"; exit;
        return $xml;
    }

    public function createExportDirectory(): string
    {
        $sty_data_dir = ilFileUtils::getDataDir() . "/sty";
        ilFileUtils::makeDir($sty_data_dir);
        if (!is_writable($sty_data_dir)) {
            $this->ilias->raiseError("Style data directory (" . $sty_data_dir
                . ") not writeable.", $this->ilias->error_obj->FATAL);
        }

        $style_dir = $sty_data_dir . "/sty_" . $this->getId();
        ilFileUtils::makeDir($style_dir);
        if (!is_dir($style_dir)) {
            $this->ilias->raiseError("Creation of style directory failed (" .
                $style_dir . ").", $this->ilias->error_obj->FATAL);
        }

        // create export subdirectory
        $ex_dir = $style_dir . "/export";
        ilFileUtils::makeDir($ex_dir);
        if (!is_dir($ex_dir)) {
            $this->ilias->raiseError("Creation of Import Directory failed (" .
                $ex_dir . ").", $this->ilias->error_obj->FATAL);
        }

        return $ex_dir;
    }

    public function cleanExportDirectory(): void
    {
        $sty_data_dir = ilFileUtils::getDataDir() . "/sty";
        $style_dir = $sty_data_dir . "/sty_" . $this->getId();
        // create export subdirectory
        $ex_dir = $style_dir . "/export";

        if (is_dir($ex_dir)) {
            ilFileUtils::delDir($ex_dir, true);
        }
    }

    public function createExportSubDirectory()
    {
        $ex_dir = $this->createExportDirectory();
        $ex_sub_dir = $ex_dir . "/" . $this->getExportSubDir();
        ilFileUtils::makeDir($ex_sub_dir);
        if (!is_writable($ex_sub_dir)) {
            $this->ilias->raiseError("Style data directory (" . $ex_sub_dir
                . ") not writeable.", $this->ilias->error_obj->FATAL);
        }
        $ex_sub_images_dir = $ex_sub_dir . "/images";
        ilFileUtils::makeDir($ex_sub_images_dir);
        if (!is_writable($ex_sub_images_dir)) {
            $this->ilias->raiseError("Style data directory (" . $ex_sub_images_dir
                . ") not writeable.", $this->ilias->error_obj->FATAL);
        }
    }

    /**
     * Set local directory, that will be included within the zip file
     */
    public function setExportSubDir(string $a_dir): void
    {
        $this->export_sub_dir = $a_dir;
    }

    /**
     * The local directory, that will be included within the zip file
     */
    public function getExportSubDir(): string
    {
        if ($this->export_sub_dir == "") {
            return "sty_" . $this->getId();
        } else {
            return $this->export_sub_dir;
        }
    }

    /**
     * @return	string		local file name of export file
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function export(): string
    {
        $this->cleanExportDirectory();
        $ex_dir = $this->createExportDirectory();
        $this->createExportSubDirectory();
        $this->exportXML($ex_dir . "/" . $this->getExportSubDir());
        //echo "-".$this->getImagesDirectory()."-".$ex_dir."/".$this->getExportSubDir()."/images"."-";
        ilFileUtils::rCopy(
            $this->getImagesDirectory(),
            $ex_dir . "/" . $this->getExportSubDir() . "/images"
        );
        if (is_file($ex_dir . "/" . $this->getExportSubDir() . ".zip")) {
            unlink($ex_dir . "/" . $this->getExportSubDir() . ".zip");
        }
        ilFileUtils::zip(
            $ex_dir . "/" . $this->getExportSubDir(),
            $ex_dir . "/" . $this->getExportSubDir() . ".zip"
        );

        return $ex_dir . "/" . $this->getExportSubDir() . ".zip";
    }

    /**
     * export style xml file to directory
     */
    public function exportXML(string $a_dir): void
    {
        $file = $a_dir . "/style.xml";

        // open file
        if (!($fp = fopen($file, 'wb'))) {
            die("<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                    " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />");
        }

        // set file permissions
        chmod($file, 0770);

        // write xml data into the file
        fwrite($fp, $this->getXML());

        // close file
        fclose($fp);
    }

    public function createImportDirectory(): string
    {
        $sty_data_dir = ilFileUtils::getDataDir() . "/sty";
        ilFileUtils::makeDir($sty_data_dir);
        if (!is_writable($sty_data_dir)) {
            $this->ilias->raiseError("Style data directory (" . $sty_data_dir
                . ") not writeable.", $this->ilias->error_obj->FATAL);
        }

        $style_dir = $sty_data_dir . "/sty_" . $this->getId();
        ilFileUtils::makeDir($style_dir);
        if (!is_dir($style_dir)) {
            $this->ilias->raiseError("Creation of style directory failed (" .
                $style_dir . ").", $this->ilias->error_obj->FATAL);
        }

        // create import subdirectory
        $im_dir = $style_dir . "/import";
        ilFileUtils::makeDir($im_dir);
        if (!is_dir($im_dir)) {
            $this->ilias->raiseError("Creation of Import Directory failed (" .
                $im_dir . ").", $this->ilias->error_obj->FATAL);
        }

        return $im_dir;
    }

    /**
     * @param array|string $a_file
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilException
     */
    public function import($a_file): void
    {
        parent::create();

        $subdir = "";
        $im_dir = $this->createImportDirectory();

        // handle uploaded files
        if (is_array($a_file)) {
            ilFileUtils::moveUploadedFile(
                $a_file["tmp_name"],
                $a_file["name"],
                $im_dir . "/" . $a_file["name"]
            );
            $file_name = $a_file["name"];
        } else {	// handle not directly uploaded files
            $pi = pathinfo($a_file);
            $file_name = $pi["basename"];
            copy($a_file, $im_dir . "/" . $file_name);
        }
        $file = pathinfo($file_name);

        // unzip file
        if (strtolower($file["extension"]) == "zip") {
            ilFileUtils::unzip($im_dir . "/" . $file_name);
            $subdir = basename($file["basename"], "." . $file["extension"]);
            if (!is_dir($im_dir . "/" . $subdir)) {
                $subdir = "style";				// check style subdir
            }
            $xml_file = $im_dir . "/" . $subdir . "/style.xml";
        } else {	// handle xml file directly (old style)
            $xml_file = $im_dir . "/" . $file_name;
        }

        // load information from xml file
        //echo "-$xml_file-";
        $this->createFromXMLFile($xml_file, true);

        // copy images
        $this->createImagesDirectory();
        if (is_dir($im_dir . "/" . $subdir . "/images")) {
            ilFileUtils::rCopy(
                $im_dir . "/" . $subdir . "/images",
                $this->getImagesDirectory()
            );
        }

        ilObjStyleSheet::_addMissingStyleClassesToStyle($this->getId());
        $this->read();
        $this->writeCSSFile();
    }

    /**
     * create style from xml file
     * todo: add mq_id and custom
     */
    public function createFromXMLFile(
        string $a_file,
        bool $a_skip_parent_create = false
    ): void {
        $ilDB = $this->db;

        $this->is_3_10_skin = false;

        if (!$a_skip_parent_create) {
            parent::create();
        }
        $importParser = new ilStyleImportParser($a_file, $this);
        $importParser->startParsing();

        // store style parameter
        foreach ($this->style as $style) {
            foreach ($style as $tag) {
                $id = $ilDB->nextId("style_parameter");

                // migrate old table PageFrame/PageContainer to div
                if (in_array($tag["class"], array("PageFrame", "PageContainer")) &&
                    $tag["tag"] == "table") {
                    $tag["tag"] = "div";
                    if ($tag["parameter"] == "width" && $tag["value"] == "100%") {
                        continue;
                    }
                }

                $q = "INSERT INTO style_parameter (id,style_id, tag, class, parameter, type, value, custom) VALUES " .
                    "(" .
                    $ilDB->quote($id, "integer") . "," .
                    $ilDB->quote($this->getId(), "integer") . "," .
                    $ilDB->quote($tag["tag"], "text") . "," .
                    $ilDB->quote($tag["class"], "text") . "," .
                    $ilDB->quote($tag["parameter"], "text") . "," .
                    $ilDB->quote($tag["type"], "text") . "," .
                    $ilDB->quote($tag["value"], "text") . "," .
                    $ilDB->quote((bool) $tag["custom"], "integer") .
                    ")";
                $ilDB->manipulate($q);
            }
        }

        // store characteristics
        $this->is_3_10_skin = true;
        foreach ($this->chars as $char) {
            if ($char["type"] != "") {
                $s = substr($char["class"], strlen($char["class"]) - 6);
                if ($s != ":hover") {
                    $ilDB->replace(
                        "style_char",
                        array(
                            "style_id" => array("integer", $this->getId()),
                            "type" => array("text", $char["type"]),
                            "characteristic" => array("text", $char["class"])),
                        array("hide" => array("integer", 0))
                    );
                    $this->is_3_10_skin = false;
                }
            }
        }

        // add style_data record
        $q = "INSERT INTO style_data (id, uptodate) VALUES " .
            "(" . $ilDB->quote($this->getId(), "integer") . ", 0)";
        $ilDB->manipulate($q);

        $this->update();
        $this->read();

        if ($this->is_3_10_skin) {
            $this->do_3_10_Migration();
        }
        //$this->writeCSSFile();
    }

    /**
     * Get grouped parameter
     */
    public function getStyleParameterGroups(): array
    {
        $groups = array();

        foreach (self::$parameter as $parameter => $props) {
            $groups[$props["group"]][] = $parameter;
        }
        return $groups;
    }

    public static function _getStyleParameterInputType(string $par): string
    {
        $input = self::$parameter[$par]["input"];
        return $input;
    }

    public static function _getStyleParameterSubPar(string $par): string
    {
        $subpar = self::$parameter[$par]["subpar"];
        return $subpar;
    }

    public static function _getStyleParameters(
        string $a_tag = ""
    ): array {
        if ($a_tag == "") {
            return self::$parameter;
        }
        $par = array();
        foreach (self::$parameter as $k => $v) {
            if (isset(self::$filtered_groups[$v["group"]]) &&
                !in_array($a_tag, self::$filtered_groups[$v["group"]])) {
                continue;
            }
            $par[$k] = $v;
        }
        return $par;
    }

    public static function _getFilteredGroups(): array
    {
        return self::$filtered_groups;
    }

    public static function _getStyleParameterNumericUnits(
        bool $a_no_percentage = false
    ): array {
        if ($a_no_percentage) {
            return self::$num_unit_no_perc;
        }
        return self::$num_unit;
    }

    public static function _getStyleParameterValues(
        string $par
    ): array {
        return self::$parameter[$par]["values"];
    }

    public static function _getStyleSuperTypes(): array
    {
        return self::$style_super_types;
    }

    public static function _isExpandable(string $a_type): bool
    {
        return in_array($a_type, self::$expandable_types);
    }

    public static function _isHideable(string $a_type): bool
    {
        return in_array($a_type, self::$hideable_types);
    }

    public static function _getStyleSuperTypeForType(
        string $a_type
    ): string {
        foreach (self::$style_super_types as $s => $t) {
            if (in_array($a_type, $t)) {
                return $s;
            }
            if ($a_type == $s) {
                return $s;
            }
        }
        return "";
    }

    public static function _getCoreStyles(): array
    {
        $c_styles = array();
        foreach (self::$core_styles as $cstyle) {
            $c_styles[$cstyle["type"] . "." . ilObjStyleSheet::_determineTag($cstyle["type"]) . "." . $cstyle["class"]]
                = array("type" => $cstyle["type"],
                    "tag" => ilObjStyleSheet::_determineTag($cstyle["type"]),
                    "class" => $cstyle["class"]);
        }
        return $c_styles;
    }

    public static function isCoreStyle(
        string $a_type,
        string $a_class
    ): bool {
        foreach (self::$core_styles as $s) {
            if ($s["type"] == $a_type && $s["class"] == $a_class) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get template class types
     */
    public static function _getTemplateClassTypes(
        string $a_template_type = ""
    ): array {
        if ($a_template_type == "") {
            return self::$templates;
        }

        return self::$templates[$a_template_type];
    }

    public static function _getPseudoClasses(string $tag): array
    {
        return self::$pseudo_classes[$tag] ?? [];
    }

    // e.g. table, row_head > table_cell
    public function determineTemplateStyleClassType(
        string $t,
        string $k
    ): string {
        return self::$templates[$t][$k];
    }

    public static function _determineTag(string $a_type): string
    {
        return self::$assigned_tags[$a_type];
    }

    public static function getAvailableParameters(): array
    {
        $pars = array();
        foreach (self::$parameter as $p => $v) {
            $pars[$p] = $v["values"];
        }

        return $pars;
    }

    /**
     * Add missing style classes to all styles
     */
    public static function _addMissingStyleClassesToStyle(
        int $a_id
    ): void {
        $styles = array(array("id" => $a_id));
        ilObjStyleSheet::_addMissingStyleClassesToAllStyles($styles);
    }

    /**
     * Add missing style classes to all styles
     * todo: add mq_id and custom handling
     */
    public static function _addMissingStyleClassesToAllStyles(
        ?array $a_styles = null
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        if (is_null($a_styles)) {
            $styles = ilObject::_getObjectsDataForType("sty");
        } else {
            $styles = $a_styles;
        }
        $core_styles = ilObjStyleSheet::_getCoreStyles();
        $bdom = ilObjStyleSheet::_getBasicStyleDom();

        // get all core image files
        $core_images = array();
        $core_dir = self::$basic_style_image_dir;
        if (is_dir($core_dir)) {
            $dir = opendir($core_dir);
            while ($file = readdir($dir)) {
                if (substr($file, 0, 1) != "." && is_file($core_dir . "/" . $file)) {
                    $core_images[] = $file;
                }
            }
        }

        foreach ($styles as $style) {
            $id = $style["id"];

            foreach ($core_styles as $cs) {
                // check, whether core style class exists
                $set = $ilDB->queryF(
                    "SELECT * FROM style_char WHERE style_id = %s " .
                    "AND type = %s AND characteristic = %s",
                    array("integer", "text", "text"),
                    array($id, $cs["type"], $cs["class"])
                );

                // if not, add core style class
                if (!($rec = $ilDB->fetchAssoc($set))) {
                    $ilDB->manipulateF(
                        "INSERT INTO style_char (style_id, type, characteristic) " .
                        " VALUES (%s,%s,%s) ",
                        array("integer", "text", "text"),
                        array($id, $cs["type"], $cs["class"])
                    );

                    $xpath = new DOMXPath($bdom);
                    $par_nodes = $xpath->query("/StyleSheet/Style[@Tag = '" . $cs["tag"] . "' and @Type='" .
                        $cs["type"] . "' and @Class='" . $cs["class"] . "']/StyleParameter");
                    foreach ($par_nodes as $par_node) {
                        // check whether style parameter exists
                        $set = $ilDB->queryF(
                            "SELECT * FROM style_parameter WHERE style_id = %s " .
                            "AND type = %s AND class = %s AND tag = %s AND parameter = %s",
                            array("integer", "text", "text", "text", "text"),
                            array($id, $cs["type"], $cs["class"],
                            $cs["tag"], $par_node->getAttribute("Name"))
                        );

                        // if not, create style parameter
                        if (!($ilDB->fetchAssoc($set))) {
                            $spid = $ilDB->nextId("style_parameter");
                            $st = $ilDB->manipulateF(
                                "INSERT INTO style_parameter (id, style_id, type, class, tag, parameter, value) " .
                                " VALUES (%s,%s,%s,%s,%s,%s,%s)",
                                array("integer", "integer", "text", "text", "text", "text", "text"),
                                array($spid, $id, $cs["type"], $cs["class"], $cs["tag"],
                                $par_node->getAttribute("Name"), $par_node->getAttribute("Value"))
                            );
                        }
                    }
                }
            }

            // now check, whether some core image files are missing
            ilObjStyleSheet::_createImagesDirectory($id);
            $imdir = ilObjStyleSheet::_getImagesDirectory($id);
            foreach ($core_images as $cim) {
                if (!is_file($imdir . "/" . $cim)) {
                    copy($core_dir . "/" . $cim, $imdir . "/" . $cim);
                }
            }
        }
    }

    //
    // Color management
    //

    /**
     * Migrates 3.10 style to 3.11 style
     */
    public function do_3_10_Migration(): void
    {
        $ilDB = $this->db;

        $this->do_3_9_Migration($this->getId());

        $this->do_3_10_CharMigration($this->getId());

        // style_char: type for characteristic
        $st = $ilDB->prepareManip("UPDATE style_char SET type = ? WHERE characteristic = ?" .
            " AND style_id = ? ", array("text", "text", "integer"));
        $ilDB->execute($st, array("media_cont", "Media", $this->getId()));
        $ilDB->execute($st, array("media_caption", "MediaCaption", $this->getId()));
        $ilDB->execute($st, array("page_fn", "Footnote", $this->getId()));
        $ilDB->execute($st, array("page_nav", "LMNavigation", $this->getId()));
        $ilDB->execute($st, array("page_title", "PageTitle", $this->getId()));
        $ilDB->execute($st, array("page_cont", "Page", $this->getId()));

        // style_parameter: type for class
        $st = $ilDB->prepareManip("UPDATE style_parameter SET type = ? WHERE class = ?" .
            " AND style_id = ? ", array("text", "text", "integer"));
        $ilDB->execute($st, array("media_cont", "Media", $this->getId()));
        $ilDB->execute($st, array("media_caption", "MediaCaption", $this->getId()));
        $ilDB->execute($st, array("page_fn", "Footnote", $this->getId()));
        $ilDB->execute($st, array("page_nav", "LMNavigation", $this->getId()));
        $ilDB->execute($st, array("page_title", "PageTitle", $this->getId()));
        $ilDB->execute($st, array("table", "Page", $this->getId()));

        $st = $ilDB->prepareManip("UPDATE style_parameter SET tag = ? WHERE class = ?" .
            " AND style_id = ? ", array("text", "text", "integer"));
        $ilDB->execute($st, array("div", "MediaCaption", $this->getId()));

        // style_char: characteristic for characteristic
        $st = $ilDB->prepareManip("UPDATE style_char SET characteristic = ? WHERE characteristic = ?" .
            " AND style_id = ? ", array("text", "text", "integer"));
        $ilDB->execute($st, array("MediaContainer", "Media", $this->getId()));
        $ilDB->execute($st, array("PageContainer", "Page", $this->getId()));

        // style_parameter: class for class
        $st = $ilDB->prepareManip("UPDATE style_parameter SET class = ? WHERE class = ?" .
            " AND style_id = ? ", array("text", "text", "integer"));
        $ilDB->execute($st, array("MediaContainer", "Media", $this->getId()));
        $ilDB->execute($st, array("PageContainer", "Page", $this->getId()));

        // force rewriting of container style
        $st = $ilDB->prepareManip("DELETE FROM style_char WHERE type = ?" .
            " AND style_id = ? ", array("text", "integer"));
        $ilDB->execute($st, array("page_cont", $this->getId()));
        $st = $ilDB->prepareManip("DELETE FROM style_parameter WHERE type = ?" .
            " AND style_id = ? ", array("text", "integer"));
        $ilDB->execute($st, array("page_cont", $this->getId()));
    }

    /**
     * This is more or less a copy of Services/Migration/DBUpdate_1385/classes
     * ilStyleMigration->addMissingStyleCharacteristics()
     * Any changes here may also be interesting there.
     */
    public function do_3_10_CharMigration(int $a_id = 0): void
    {
        $ilDB = $this->db;

        $add_str = "";
        if ($a_id > 0) {
            $add_str = " AND style_id = " . $ilDB->quote($a_id, "integer");
        }

        $set = $ilDB->query($q = "SELECT DISTINCT style_id, tag, class FROM style_parameter WHERE " .
            $ilDB->equals("type", "", "text", true) . " " . $add_str);

        while ($rec = $ilDB->fetchAssoc($set)) {
            // derive types from tag
            $types = array();
            switch ($rec["tag"]) {
                case "div":
                case "p":
                    if (in_array($rec["class"], array("Headline3", "Headline1",
                        "Headline2", "TableContent", "List", "Standard", "Remark",
                        "Additional", "Mnemonic", "Citation", "Example"))) {
                        $types[] = "text_block";
                    }
                    if (in_array($rec["class"], array("Block", "Remark",
                        "Additional", "Mnemonic", "Example", "Excursus", "Special"))) {
                        $types[] = "section";
                    }
                    if (in_array($rec["class"], array("Page", "Footnote", "PageTitle", "LMNavigation"))) {
                        $types[] = "page";
                    }
                    break;

                case "td":
                    $types[] = "table_cell";
                    break;

                case "a":
                    if (in_array($rec["class"], array("ExtLink", "IntLink", "FootnoteLink"))) {
                        $types[] = "link";
                    }
                    break;

                case "span":
                    $types[] = "text_inline";
                    break;

                case "table":
                    $types[] = "table";
                    break;
            }

            // check if style_char set exists
            foreach ($types as $t) {
                // check if second type already exists
                $set4 = $ilDB->queryF(
                    "SELECT * FROM style_char " .
                    " WHERE style_id = %s AND type = %s AND characteristic = %s",
                    array("integer", "text", "text"),
                    array($rec["style_id"], $t, $rec["class"])
                );
                if ($rec4 = $ilDB->fetchAssoc($set4)) {
                    // ok
                } else {
                    //echo "<br>1-".$rec["style_id"]."-".$t."-".$rec["class"]."-";
                    $ilDB->manipulateF(
                        "INSERT INTO style_char " .
                        " (style_id, type, characteristic) VALUES " .
                        " (%s,%s,%s) ",
                        array("integer", "text", "text"),
                        array($rec["style_id"], $t, $rec["class"])
                    );
                }
            }

            // update types
            if ($rec["type"] == "") {
                if (count($types) > 0) {
                    $ilDB->manipulateF(
                        "UPDATE style_parameter SET type = %s " .
                        " WHERE style_id = %s AND class = %s AND " . $ilDB->equals("type", "", "text", true),
                        array("text", "integer", "text"),
                        array($types[0], $rec["style_id"], $rec["class"])
                    );
                    //echo "<br>3-".$types[0]."-".$rec["style_id"]."-".$rec["class"]."-";

                    // links extra handling
                    if ($types[0] == "link") {
                        $ilDB->manipulateF(
                            "UPDATE style_parameter SET type = %s " .
                            " WHERE style_id = %s AND (class = %s OR class = %s) AND " . $ilDB->equals("type", "", "text", true),
                            array("text", "integer", "text", "text"),
                            array($types[0], $rec["style_id"], $rec["class"] . ":visited",
                            $rec["class"] . ":hover")
                        );
                    }
                }

                if (count($types) == 2) {
                    // select all records of first type and add second type
                    // records if necessary.
                    $set2 = $ilDB->queryF(
                        "SELECT * FROM style_parameter " .
                        " WHERE style_id = %s AND class = %s AND type = %s",
                        array("integer", "text", "text"),
                        array($rec["style_id"], $rec["class"], $types[0])
                    );
                    while ($rec2 = $ilDB->fetchAssoc($set2)) {
                        // check if second type already exists
                        $set3 = $ilDB->queryF(
                            "SELECT * FROM style_parameter " .
                            " WHERE style_id = %s AND tag = %s AND class = %s AND type = %s AND parameter = %s",
                            array("integer", "text", "text", "text", "text"),
                            array($rec["style_id"], $rec["tag"], $rec["class"], $types[1], $rec["parameter"])
                        );
                        if ($rec3 = $ilDB->fetchAssoc($set3)) {
                            // ok
                        } else {
                            $nid = $ilDB->nextId("style_parameter");
                            $ilDB->manipulateF(
                                "INSERT INTO style_parameter " .
                                " (id, style_id, tag, class, parameter, value, type) VALUES " .
                                " (%s, %s,%s,%s,%s,%s,%s) ",
                                array("integer", "integer", "text", "text", "text", "text", "text"),
                                array($nid, $rec2["style_id"], $rec2["tag"], $rec2["class"],
                                    $rec2["parameter"], $rec2["value"], $types[1])
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Migrate old 3.9 styles
     */
    public function do_3_9_Migration(int $a_id): void
    {
        $ilDB = $this->db;

        $classes = array("Example", "Additional", "Citation", "Mnemonic", "Remark");
        $pars = array("margin-top", "margin-bottom");

        foreach ($classes as $curr_class) {
            foreach ($pars as $curr_par) {
                $res2 = $ilDB->queryF(
                    "SELECT id FROM style_parameter WHERE style_id = %s" .
                    " AND tag = %s AND class= %s AND parameter = %s",
                    array("integer", "text", "text", "text"),
                    array($a_id, "p", $curr_class, $curr_par)
                );
                if ($row2 = $ilDB->fetchAssoc($res2)) {
                    $ilDB->manipulateF(
                        "UPDATE style_parameter SET value= %s WHERE id = %s",
                        array("text", "integer"),
                        array("10px", $row2["id"])
                    );
                } else {
                    $nid = $ilDB->nextId("style_parameter");
                    $ilDB->manipulateF(
                        "INSERT INTO style_parameter " .
                        "(id, style_id, tag, class, parameter,value) VALUES (%s,%s,%s,%s,%s,%s)",
                        array("integer", "integer", "text", "text", "text", "text"),
                        array($nid, $a_id, "div", $curr_class, $curr_par, "10px")
                    );
                }
            }
        }

        $ilDB->manipulateF(
            "UPDATE style_parameter SET tag = %s WHERE tag = %s and style_id = %s",
            array("text", "text", "integer"),
            array("div", "p", $a_id)
        );
    }

    ////
    //// Colors
    ////

    /**
     * Get colors of style
     */
    public function getColors(): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM style_color WHERE " .
            "style_id = " . $ilDB->quote($this->getId(), "integer") . " " .
            "ORDER BY color_name");

        $colors = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $colors[] = array(
                "name" => $rec["color_name"],
                "code" => $rec["color_code"]
                );
        }

        return $colors;
    }

    /**
     * Remove a color
     */
    public function removeColor(string $a_name): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM style_color WHERE " .
            " style_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            " color_name = " . $ilDB->quote($a_name, "text"));
    }

    public function getColorCodeForName(string $a_name): string
    {
        $ilDB = $this->db;
        $a_i = "";
        $pos = strpos($a_name, "(");
        if ($pos > 0) {
            $a_i = substr($a_name, $pos + 1);
            $a_i = str_replace(")", "", $a_i);
            $a_name = substr($a_name, 0, $pos);
        }

        $set = $ilDB->query("SELECT color_code FROM style_color WHERE " .
            " style_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            " color_name = " . $ilDB->quote($a_name, "text"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            if ($a_i == "") {
                return "#" . $rec["color_code"];
            } else {
                return "#" . ilObjStyleSheet::_getColorFlavor(
                    $rec["color_code"],
                    (int) $a_i
                );
            }
        }
        return "";
    }

    /**
     * Get color flavor
     */
    public static function _getColorFlavor(
        string $a_rgb,
        int $a_i
    ): string {
        $rgb = ilObjStyleSheet::_explodeRGB($a_rgb, true);
        $hls = ilObjStyleSheet::_RGBToHLS($rgb);

        if ($a_i > 0) {
            $hls["l"] = $hls["l"] + ((255 - $hls["l"]) * ($a_i / 100));
        }
        if ($a_i < 0) {
            $hls["l"] = $hls["l"] - (($hls["l"]) * (-$a_i / 100));
        }

        $rgb = ilObjStyleSheet::_HLSToRGB($hls);

        foreach ($rgb as $k => $v) {
            $rgb[$k] = str_pad(dechex((int) $v), 2, "0", STR_PAD_LEFT);
        }

        return $rgb["r"] . $rgb["g"] . $rgb["b"];
    }

    /**
     * Explode an RGB string into an array
     */
    public static function _explodeRGB(
        string $a_rgb,
        bool $as_dec = false
    ): array {
        $r["r"] = substr($a_rgb, 0, 2);
        $r["g"] = substr($a_rgb, 2, 2);
        $r["b"] = substr($a_rgb, 4, 2);

        if ($as_dec) {
            $r["r"] = (int) hexdec($r["r"]);
            $r["g"] = (int) hexdec($r["g"]);
            $r["b"] = (int) hexdec($r["b"]);
        }

        return $r;
    }

    /**
     * RGB to HLS (both arrays, 0..255)
     */
    public static function _RGBToHLS(array $a_rgb): array
    {
        $r = $a_rgb["r"] / 255;
        $g = $a_rgb["g"] / 255;
        $b = $a_rgb["b"] / 255;
        $h = 0;

        // max / min
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        //lightness
        $l = ($max + $min) / 2;

        if ($max == $min) {
            $s = 0;
        } else {
            if ($l < 0.5) {
                $s = ($max - $min) / ($max + $min);
            } else {
                $s = ($max - $min) / (2.0 - $max - $min);
            }

            if ($r == $max) {
                $h = ($g - $b) / ($max - $min);
            } elseif ($g == $max) {
                $h = 2.0 + ($b - $r) / ($max - $min);
            } elseif ($b == $max) {
                $h = 4.0 + ($r - $g) / ($max - $min);
            }
        }

        $hls["h"] = round(($h / 6) * 255);
        $hls["l"] = round($l * 255);
        $hls["s"] = round($s * 255);

        return $hls;
    }

    /**
     * HLS to RGB (both arrays, 0..255)
     */
    public static function _HLSToRGB(array $a_hls): array
    {
        $h = $a_hls["h"] / 255;
        $l = $a_hls["l"] / 255;
        $s = $a_hls["s"] / 255;
        $temp3 = 0;

        $rgb["r"] = $rgb["g"] = $rgb["b"] = 0;

        //  If S=0, define R, G, and B all to L
        if ($s == 0) {
            $rgb["r"] = $rgb["g"] = $rgb["b"] = $l;
        } else {
            if ($l < 0.5) {
                $temp2 = $l * (1.0 + $s);
            } else {
                $temp2 = $l + $s - $l * $s;
            }

            $temp1 = 2.0 * $l - $temp2;


            # For each of R, G, B, compute another temporary value, temp3, as follows:
            foreach ($rgb as $k => $v) {
                switch ($k) {
                    case "r":
                        $temp3 = $h + 1.0 / 3.0;
                        break;

                    case "g":
                        $temp3 = $h;
                        break;

                    case "b":
                        $temp3 = $h - 1.0 / 3.0;
                        break;
                }
                if ($temp3 < 0) {
                    $temp3 = $temp3 + 1.0;
                }
                if ($temp3 > 1) {
                    $temp3 = $temp3 - 1.0;
                }

                if (6.0 * $temp3 < 1) {
                    $rgb[$k] = $temp1 + ($temp2 - $temp1) * 6.0 * $temp3;
                } elseif (2.0 * $temp3 < 1) {
                    $rgb[$k] = $temp2;
                } elseif (3.0 * $temp3 < 2) {
                    $rgb[$k] = $temp1 + ($temp2 - $temp1) * ((2.0 / 3.0) - $temp3) * 6.0;
                } else {
                    $rgb[$k] = $temp1;
                }
            }
        }

        $rgb["r"] = round($rgb["r"] * 255);
        $rgb["g"] = round($rgb["g"] * 255);
        $rgb["b"] = round($rgb["b"] * 255);

        return $rgb;
    }

    //
    // Media queries
    //

    ////
    //// Media Queries
    ////

    public function getMediaQueries(): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM sty_media_query WHERE " .
            "style_id = " . $ilDB->quote($this->getId(), "integer") . " " .
            "ORDER BY order_nr");

        $mq = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $mq[] = $rec;
        }

        return $mq;
    }

    public function addMediaQuery(
        string $a_mquery,
        int $order_nr = 0
    ): int {
        $ilDB = $this->db;

        $id = $ilDB->nextId("sty_media_query");
        if ($order_nr == 0) {
            $order_nr = $this->getMaxMQueryOrderNr() + 10;
        }

        $ilDB->manipulate("INSERT INTO sty_media_query (id, style_id, mquery, order_nr)" .
            " VALUES (" .
            $ilDB->quote($id, "integer") . "," .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($a_mquery, "text") . "," .
            $ilDB->quote($order_nr, "integer") .
            ")");

        return $id;
    }

    /**
     * Get maximum media query order nr
     */
    public function getMaxMQueryOrderNr(): int
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT max(order_nr) mnr FROM sty_media_query " .
            " WHERE style_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        return (int) $rec["mnr"];
    }

    public function updateMediaQuery(
        int $a_id,
        string $a_mquery
    ): void {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "UPDATE sty_media_query SET " .
            " mquery = " . $ilDB->quote($a_mquery, "text") .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
    }

    /**
     * Get media query for id
     *
     * @param
     * @return
     */
    public function getMediaQueryForId(
        int $a_id
    ): array {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM sty_media_query " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        return $ilDB->fetchAssoc($set);
    }

    /**
     * Delete media query
     *
     * @param int $a_id media query id
     */
    public function deleteMediaQuery(
        int $a_id
    ): void {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM sty_media_query WHERE " .
            " style_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND id = " . $ilDB->quote($a_id, "integer")
        );
        $this->saveMediaQueryOrder();
    }

    /**
     * Save media query order
     */
    public function saveMediaQueryOrder(
        array $a_order_nr = null
    ): void {
        $ilDB = $this->db;

        $mqueries = $this->getMediaQueries();
        if (is_array($a_order_nr)) {
            foreach ($mqueries as $k => $mq) {
                $mqueries[$k]["order_nr"] = $a_order_nr[$mq["id"]];
            }
            $mqueries = ilArrayUtil::sortArray($mqueries, "order_nr", "", true);
        }
        $cnt = 10;
        foreach ($mqueries as $mq) {
            $ilDB->manipulate(
                "UPDATE sty_media_query SET " .
                " order_nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE id = " . $ilDB->quote($mq["id"], "integer")
            );
            $cnt += 10;
        }
    }


    //
    // Table template management
    //

    /**
     * Get table templates of style
     */
    public function getTemplates(
        string $a_type
    ): array {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM style_template WHERE " .
            "style_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            "temp_type = " . $ilDB->quote($a_type, "text") . " " .
            "ORDER BY name");

        $templates = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec["classes"] = $this->getTemplateClasses((int) $rec["id"]);
            $templates[] = $rec;
        }

        return $templates;
    }

    /**
     * Get template classes
     */
    public function getTemplateClasses(
        int $a_tid
    ): array {
        $ilDB = $this->db;
        $set = $ilDB->query("SELECT * FROM style_template_class WHERE " .
            "template_id = " . $ilDB->quote($a_tid, "integer"));

        $class = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $key = $rec["class_type"];
            $class[$key] = $rec["class"];
        }

        return $class;
    }


    /**
     * Add table template
     */
    public function addTemplate(
        string $a_type,
        string $a_name,
        array $a_classes
    ): int {
        $ilDB = $this->db;

        $tid = $ilDB->nextId("style_template");
        $ilDB->manipulate("INSERT INTO style_template " .
            "(id, style_id, name, temp_type)" .
            " VALUES (" .
            $ilDB->quote($tid, "integer") . "," .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($a_name, "text") . "," .
            $ilDB->quote($a_type, "text") .
            ")");

        foreach ($a_classes as $t => $c) {
            $ilDB->manipulate("INSERT INTO style_template_class " .
                "(template_id, class_type, class)" .
                " VALUES (" .
                $ilDB->quote($tid, "integer") . "," .
                $ilDB->quote($t, "text") . "," .
                $ilDB->quote($c, "text") .
                ")");
        }

        $this->writeTemplatePreview(
            $tid,
            ilObjStyleSheetGUI::_getTemplatePreview($this, $a_type, $tid, true)
        );

        return $tid;
    }

    /**
     * Update table template
     */
    public function updateTemplate(
        int $a_t_id,
        string $a_name,
        array $a_classes
    ): void {
        $ilDB = $this->db;

        $ilDB->manipulate("UPDATE style_template SET " .
            "name = " . $ilDB->quote($a_name, "text") .
            " WHERE id = " . $ilDB->quote($a_t_id, "integer"));

        $ilDB->manipulate(
            "DELETE FROM style_template_class WHERE " .
            "template_id = " . $ilDB->quote($a_t_id, "integer")
        );
        foreach ($a_classes as $t => $c) {
            $ilDB->manipulate("INSERT INTO style_template_class " .
                "(template_id, class_type, class)" .
                " VALUES (" .
                $ilDB->quote($a_t_id, "integer") . "," .
                $ilDB->quote($t, "text") . "," .
                $ilDB->quote($c, "text") .
                ")");
        }
    }

    public function addTemplateClass(
        int $a_t_id,
        string $a_type,
        string $a_class
    ): void {
        $ilDB = $this->db;

        $ilDB->manipulate("INSERT INTO style_template_class " .
            "(template_id, class_type, class)" .
            " VALUES (" .
            $ilDB->quote($a_t_id, "integer") . "," .
            $ilDB->quote($a_type, "text") . "," .
            $ilDB->quote($a_class, "text") .
            ")");
    }

    /**
     * Check whether template exists
     */
    public function templateExists(
        string $a_template_name
    ): bool {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM style_template WHERE " .
            "style_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            "name = " . $ilDB->quote($a_template_name, "text"));
        if ($ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * @param int $a_t_id template id
     */
    public function getTemplate(int $a_t_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM style_template WHERE " .
            "style_id = " . $ilDB->quote($this->getId(), "integer") . " " .
            " AND id = " . $ilDB->quote($a_t_id, "integer"));

        if ($rec = $ilDB->fetchAssoc($set)) {
            $rec["classes"] = $this->getTemplateClasses((int) $rec["id"]);

            $template = $rec;
            return $template;
        }
        return array();
    }

    /**
     * Lookup table template name for template ID
     */
    public function lookupTemplateName(int $a_t_id): string
    {
        return self::_lookupTemplateName($a_t_id);
    }

    /**
     * Lookup table template name for template ID
     */
    public static function _lookupTemplateName(int $a_t_id): ?string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT name FROM style_template WHERE " .
            " id = " . $ilDB->quote($a_t_id, "integer"));

        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["name"];
        }

        return null;
    }

    /**
     * Get table template xml
     */
    public function getTemplateXML(): string
    {
        $ilDB = $this->db;

        $tag = "<StyleTemplates>";

        $ttypes = array("table", "vaccordion", "haccordion", "carousel");

        foreach ($ttypes as $ttype) {
            $ts = $this->getTemplates($ttype);

            foreach ($ts as $t) {
                $atts = ilObjStyleSheet::_getTemplateClassTypes($ttype);
                /*$atts = array("table" => "TableClass",
                    "caption" => "CaptionClass",
                    "row_head" => "RowHeadClass",
                    "row_foot" => "RowFootClass",
                    "col_head" => "ColHeadClass",
                    "col_foot" => "ColFootClass",
                    "odd_row" => "OddRowClass",
                    "even_row" => "EvenRowClass",
                    "odd_col" => "OddColClass",
                    "even_col" => "EvenColClass");*/
                $c = $t["classes"];

                $tag .= '<StyleTemplate Name="' . $t["name"] . '">';

                foreach ($atts as $type => $t2) {
                    if (($c[$type] ?? "") != "") {
                        $tag .= '<StyleClass Type="' . $type . '" Value="' . $c[$type] . '" />';
                    }
                }

                $tag .= "</StyleTemplate>";
            }
        }

        $tag .= "</StyleTemplates>";

        //echo htmlentities($tag);
        return $tag;
    }

    /**
     * Write table template preview
     */
    public function writeTemplatePreview(
        int $a_t_id,
        string $a_preview_html
    ): void {
        $ilDB = $this->db;
        $a_preview_html = str_replace(' width=""', "", $a_preview_html);
        $a_preview_html = str_replace(' valign="top"', "", $a_preview_html);
        $a_preview_html = str_replace('<div class="ilc_text_block_TableContent">', "<div>", $a_preview_html);
        //echo "1-".strlen($a_preview_html)."-";
        //echo htmlentities($a_preview_html);
        if (strlen($a_preview_html) > 4000) {
            //echo "2";
            $a_preview_html = "";
        }
        $ilDB->manipulate("UPDATE style_template SET " .
            "preview = " . $ilDB->quote($a_preview_html, "text") .
            " WHERE id = " . $ilDB->quote($a_t_id, "integer"));
    }

    /**
     * Lookup table template preview
     */
    public function lookupTemplatePreview(int $a_t_id): string
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT preview FROM style_template " .
            " WHERE id = " . $ilDB->quote($a_t_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["preview"] ?? "";
        }

        return "";
    }

    /**
     * Lookup table template preview
     */
    public static function _lookupTemplateIdByName(
        int $a_style_id,
        string $a_name
    ): ?int {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT id FROM style_template " .
            " WHERE style_id = " . $ilDB->quote($a_style_id, "integer") .
            " AND name = " . $ilDB->quote($a_name, "text"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["id"];
        }

        return null;
    }

    /**
     * Remove table template
     */
    public function removeTemplate(int $a_t_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM style_template WHERE " .
            " style_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            " id = " . $ilDB->quote($a_t_id, "integer"));

        $ilDB->manipulate(
            "DELETE FROM style_template_class WHERE " .
            "template_id = " . $ilDB->quote($a_t_id, "integer")
        );
    }

    public function writeStyleSetting(
        string $a_name,
        string $a_value
    ): void {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM style_setting WHERE " .
            " style_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND name = " . $ilDB->quote($a_name, "text")
        );

        $ilDB->manipulate("INSERT INTO style_setting " .
            "(style_id, name, value) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($a_name, "text") . "," .
            $ilDB->quote($a_value, "text") .
            ")");
    }

    /**
     * Lookup style setting
     */
    public function lookupStyleSetting(string $a_name): string
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT value FROM style_setting " .
            " WHERE style_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND name = " . $ilDB->quote($a_name, "text")
        );
        $rec = $ilDB->fetchAssoc($set);

        return $rec["value"] ?? '';
    }

    /**
     * Write style usage
     */
    public static function writeStyleUsage(
        int $a_obj_id,
        int $a_style_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->replace(
            "style_usage",
            array(
            "obj_id" => array("integer", $a_obj_id)),
            array(
                "style_id" => array("integer", $a_style_id))
        );
    }

    /**
     * Lookup object style
     */
    public static function lookupObjectStyle(
        int $a_obj_id
    ): int {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT style_id FROM style_usage " .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        if (!is_array($rec)) {
            return 0;
        }

        if (ilObject::_lookupType((int) $rec["style_id"]) == "sty") {
            return (int) $rec["style_id"];
        }

        return 0;
    }

    /**
     * Lookup objects for style
     * @return int[]
     */
    public static function lookupObjectForStyle(
        int $a_style_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $obj_ids = array();
        if (ilObject::_lookupType($a_style_id) == "sty") {
            $set = $ilDB->query(
                "SELECT DISTINCT obj_id FROM style_usage " .
                " WHERE style_id = " . $ilDB->quote($a_style_id, "integer")
            );

            while ($rec = $ilDB->fetchAssoc($set)) {
                $obj_ids[] = (int) $rec["obj_id"];
            }
        }
        return $obj_ids;
    }
}
