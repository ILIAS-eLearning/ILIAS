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

use ILIAS\Style\Content;

/**
 * Style Import Parser
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilStyleImportParser extends ilSaxParser
{
    protected string $cdata = "";
    protected array $cur_template_classes;
    protected array $cur_template;
    protected array $current_tags = [];
    protected string $current_type = "";
    protected string $current_class = "";
    protected string $current_tag = "";
    protected array $styles;
    protected ilObjStyleSheet $style_obj;
    protected ilTree $tree;
    protected Content\ColorManager $color_manager;
    protected array $chars = [];

    public function __construct(
        string $a_xml_file,
        ilObjStyleSheet $a_style_obj
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();

        $service = $DIC->contentStyle()->internal();
        $access_manager = $service->domain()->access(0, $DIC->user()->getId());
        $access_manager->enableWrite(true);

        $this->color_manager = $service->domain()->color(
            $a_style_obj->getId(),
            $access_manager
        );

        $this->style_obj = $a_style_obj;

        parent::__construct($a_xml_file);
    }


    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * start the parser
    */
    public function startParsing(): void
    {
        $this->styles = array();
        parent::startParsing();
        $this->style_obj->setStyle($this->styles);
        $this->style_obj->setCharacteristics($this->chars);
    }

    public function handlerBeginTag(
        $a_xml_parser,
        string $a_name,
        array $a_attribs
    ): void {
        switch ($a_name) {
            case "Style":
                $this->current_tag = $a_attribs["Tag"];
                $this->current_class = $a_attribs["Class"];
                $this->current_type = $a_attribs["Type"];
                if ($this->current_class == "PageTitle" && $this->current_type == "page_title" && $this->current_tag == "div") {
                    $this->current_tag = "h1";
                }
                if ($this->current_class == "Headline1" && $this->current_tag == "div") {
                    $this->current_tag = "h1";
                    $this->current_type = "heading1";
                }
                if ($this->current_class == "Headline2" && $this->current_tag == "div") {
                    $this->current_tag = "h2";
                    $this->current_type = "heading2";
                }
                if ($this->current_class == "Headline3" && $this->current_tag == "div") {
                    $this->current_tag = "h3";
                    $this->current_type = "heading3";
                }
                $this->current_tags = array();
                $this->chars[] = array("type" => $this->current_type,
                    "class" => $this->current_class);
                break;

            case "StyleParameter":
                $this->current_tags[] = array(
                    "tag" => $this->current_tag,
                    "class" => $this->current_class,
                    "parameter" => $a_attribs["Name"],
                    "type" => $this->current_type,
                    "value" => $a_attribs["Value"],
                    "custom" => $a_attribs["Custom"]);
                break;

            case "StyleColor":
                $this->color_manager->addColor($a_attribs["Name"], $a_attribs["Code"]);
                break;

            case "StyleTemplate":
                $this->cur_template = array("type" => $a_attribs["Type"],
                    "name" => $a_attribs["Name"]);
                $this->cur_template_classes = array();
                break;

            case "StyleTemplateClass":
                $this->cur_template_classes[$a_attribs["ClassType"]] =
                    $a_attribs["Class"];
                break;

        }
        $this->cdata = "";
    }

    public function handlerEndTag(
        $a_xml_parser,
        string $a_name
    ): void {
        switch ($a_name) {
            case "Title":
                $this->style_obj->setTitle($this->cdata);
                break;

            case "Description":
                $this->style_obj->setDescription($this->cdata);
                break;

            case "Style":
                $this->styles[] = $this->current_tags;
                break;

            case "StyleTemplate":
                $this->style_obj->addTemplate(
                    $this->cur_template["type"],
                    $this->cur_template["name"],
                    $this->cur_template_classes
                );
                break;

        }
    }

    public function handlerCharacterData(
        $a_xml_parser,
        string $a_data
    ): void {
        // i don't know why this is necessary, but
        // the parser seems to convert "&gt;" to ">" and "&lt;" to "<"
        // in character data, but we don't want that, because it's the
        // way we mask user html in our content, so we convert back...
        $a_data = str_replace("<", "&lt;", $a_data);
        $a_data = str_replace(">", "&gt;", $a_data);

        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        $a_data = preg_replace("/\n/", "", $a_data);
        $a_data = preg_replace("/\t+/", "", $a_data);
        if (!empty($a_data)) {
            $this->cdata .= $a_data;
        }
    }
}
