<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Category Import Parser
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCategoryImportParser extends ilSaxParser
{
    protected ilRbacAdmin $rbacadmin;
    protected ilRbacReview $rbacreview;
    protected ilRbacSystem $rbacsystem;
    /** @var int[] $parent */
    public array $parent;		// current parent ref id
    public int $parent_cnt;
    public int $withrol;
    protected ilLogger $cat_log;
    protected ?ilObjCategory $category = null;
    protected string $default_language = "";
    protected string $cur_spec_lang = "";
    protected string $cur_title = "";
    protected string $cur_description = "";
    protected string $cdata = "";

    /**
     * ilCategoryImportParser constructor.
     * @param string $a_xml_file
     * @param int    $a_parent
     * @param int    $withrol   // must have value 1 when creating a hierarchy of local roles
     */
    public function __construct(
        string $a_xml_file,
        int $a_parent,
        int $withrol
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->rbacadmin = $DIC->rbac()->admin();
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->parent_cnt = 0;
        $this->parent[$this->parent_cnt] = $a_parent;
        $this->parent_cnt++;
        $this->withrol = $withrol;

        parent::__construct($a_xml_file);
    }

    public function setHandlers($a_xml_parser) : void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    // generate a tag with given name and attributes
    public function buildTag(
        string $type,
        string $name,
        array $attr = null
    ) : string {
        $tag = "<";

        if ($type === "end") {
            $tag .= "/";
        }

        $tag .= $name;

        if (is_array($attr)) {
            foreach ($attr as $k => $v) {
                $tag .= " " . $k . "=\"$v\"";
            }
        }

        $tag .= ">";

        return $tag;
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param string $a_name
     * @param array  $a_attribs
     * @return void
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        switch ($a_name) {
            case "Category":
                $cur_parent = $this->parent[$this->parent_cnt - 1];
                $this->category = new ilObjCategory();
                $this->category->setImportId($a_attribs["Id"] . " (#" . $cur_parent . ")");
                $this->default_language = $a_attribs["DefaultLanguage"];
                $this->category->setTitle($a_attribs["Id"]);
                $this->category->create();
                $this->category->createReference();
                $this->category->putInTree($cur_parent);
                $this->parent[$this->parent_cnt++] = $this->category->getRefId();
                break;

        case "CategorySpec":
          $this->cur_spec_lang = $a_attribs["Language"];
          break;

        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param string $a_name
     * @return void
     */
    public function handlerEndTag($a_xml_parser, string $a_name) : void
    {
        switch ($a_name) {
            case "Category":
                unset($this->category, $this->parent[$this->parent_cnt - 1]);
                $this->parent_cnt--;
                break;

            case "CategorySpec":
                $is_def = '0';
                if ($this->cur_spec_lang === $this->default_language) {
                    $this->category->setTitle($this->cur_title);
                    $this->category->setDescription($this->cur_description);
                    $this->category->update();
                    $is_def = '1';
                }
                $this->category->addTranslation(
                    $this->cur_title,
                    $this->cur_description,
                    $this->cur_spec_lang,
                    $is_def
                );
                break;

            case "Title":
                $this->cur_title = $this->cdata;
                break;

            case "Description":
                $this->cur_description = $this->cdata;
                break;
        }

        $this->cdata = "";
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param string $a_data
     * @return void
     */
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        // i don't know why this is necessary, but
        // the parser seems to convert "&gt;" to ">" and "&lt;" to "<"
        // in character data, but we don't want that, because it's the
        // way we mask user html in our content, so we convert back...
        $a_data = str_replace(["<", ">"], ["&lt;", "&gt;"], $a_data);

        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        $a_data = preg_replace("/\n/", "", $a_data);
        $a_data = preg_replace("/\t+/", "", $a_data);
        if (!empty($a_data)) {
            $this->cdata .= $a_data;
        }
    }
}
