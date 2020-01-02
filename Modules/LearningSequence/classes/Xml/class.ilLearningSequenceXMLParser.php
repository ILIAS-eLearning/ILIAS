<?php

declare(strict_types=1);

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilLearningSequenceXMLParser extends ilSaxParser
{
    /**
     * @var bool
     */
    protected $storing;

    /**
     * @var int
     */
    protected $counter;

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

    public function start() : array
    {
        $this->startParsing();

        $ret = array();
        $ret["object"] = $this->object;
        $ret["item_data"] = $this->ls_item_data;
        $ret["settings"] = $this->settings;
        $ret["lp_settings"] = $this->lp_settings;

        return $ret;
    }

    public function setHandlers($parser)
    {
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, "handleBeginTag", "handleEndTag");
        xml_set_character_data_handler($parser, 'handleCharacterData');
    }

    public function handleBeginTag(
        $parser,
        string $name,
        array $attributes
    ) {
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

    public function handleEndTag($parser, string $name)
    {
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
                $this->settings["abstract"] = trim($this->cdata);
                break;
            case "extro":
                $this->settings["extro"] = trim($this->cdata);
                break;
            case "abstract_img":
                $this->settings["abstract_img"] = trim($this->cdata);
                break;
            case "extor_img":
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
    }

    public function handleCharacterData($parser, $data)
    {
        $this->cdata = $data ?? "";
        $this->storeData();
    }

    protected function beginStoreCData()
    {
        $this->storing = true;
    }

    protected function endStoreCData()
    {
        $this->storing = false;
    }

    protected function storeData()
    {
        if ($this->storing) {
            $this->ls_item_data[$this->counter][$this->actual_name] = $this->cdata ?? "";
        }
    }
}
