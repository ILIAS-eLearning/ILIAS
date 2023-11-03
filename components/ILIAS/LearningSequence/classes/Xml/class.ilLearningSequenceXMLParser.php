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

class ilLearningSequenceXMLParser extends ilSaxParser
{
    protected ilObjLearningSequence $obj;
    protected bool $storing;

    /**
     * @var (string|int)[]
     */
    protected array $object;

    /**
     * @var (string|int)[]
     */
    protected array $ls_item_data;

    /**
     * @var string[]
     */
    protected array $settings;

    /**
     * @var array
     */
    protected array $lp_settings;
    protected int $counter;
    protected string $actual_name;
    protected string $cdata;

    public function __construct(ilObjLearningSequence $obj, string $xml)
    {
        parent::__construct();

        $this->obj = $obj;
        $this->storing = false;
        $this->setXMLContent($xml);

        $this->object = array();
        $this->ls_item_data = array();
        $this->settings = array();
        $this->lp_settings = array();
        $this->lp_settings["lp_item_ref_ids"] = array();
        $this->counter = 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function start(): array
    {
        $this->startParsing();

        $ret = array();
        $ret["object"] = $this->object;
        $ret["item_data"] = $this->ls_item_data;
        $ret["settings"] = $this->settings;
        $ret["lp_settings"] = $this->lp_settings;

        return $ret;
    }

    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, "handleBeginTag", "handleEndTag");
        xml_set_character_data_handler($a_xml_parser, 'handleCharacterData');
    }

    public function handleBeginTag(
        $parser,
        string $name,
        array $attributes
    ): void {
        $this->actual_name = $name;

        switch ($name) {
            case "lso":
                $this->object["ref_id"] = $attributes["ref_id"];
                break;
            case "ls_item":
                $this->ls_item_data[$this->counter]["id"] = $attributes["id"];
                $this->beginStoreCData();
                break;
            default:
                break;
        }
    }

    public function handleEndTag($parser, string $name): void
    {
        $this->cdata = trim($this->cdata);

        switch ($name) {
            case "title":
                $this->obj->setTitle(trim($this->cdata));
                break;
            case "description":
                $this->obj->setDescription(trim($this->cdata));
                break;
            case "ls_item":
                $this->endStoreCData();
                break;
            case "ls_item_order_number":
                $this->counter++;
                break;
            case "abstract":
                $this->settings["abstract"] = base64_decode(trim($this->cdata));
                break;
            case "extro":
                $this->settings["extro"] = base64_decode(trim($this->cdata));
                break;
            case "abstract_img":
                $this->settings["abstract_img"] = trim($this->cdata);
                break;
            case "extro_img":
                $this->settings["extro_img"] = trim($this->cdata);
                break;
            case "abstract_img_data":
                $this->settings["abstract_img_data"] = trim($this->cdata);
                break;
            case "extro_img_data":
                $this->settings["extro_img_data"] = trim($this->cdata);
                break;
            case "members_gallery":
                $this->settings["members_gallery"] = trim($this->cdata);
                break;
            case "lp_item_ref_id":
                $this->lp_settings["lp_item_ref_ids"][] = trim($this->cdata);
                break;
            case "lp_type":
                $this->lp_settings["lp_type"] = trim($this->cdata);
                break;
            case "lp_mode":
                $this->lp_settings["lp_mode"] = trim($this->cdata);
                break;
            default:
                break;
        }

        $this->cdata = '';
    }

    public function handleCharacterData($parser, $data): void
    {
        $this->cdata .= ($data ?? "");
        $this->storeData();
    }

    protected function beginStoreCData(): void
    {
        $this->storing = true;
    }

    protected function endStoreCData(): void
    {
        $this->storing = false;
    }

    protected function storeData(): void
    {
        if ($this->storing) {
            $this->ls_item_data[$this->counter][$this->actual_name] = $this->cdata ?? "";
        }
    }
}
