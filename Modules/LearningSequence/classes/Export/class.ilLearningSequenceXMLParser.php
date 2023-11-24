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

declare(strict_types=1);

use ilLearningSequenceXMLWriter as Writer;

class ilLearningSequenceXMLParser extends ilSaxParser
{
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
    protected string $cdata = '';

    public function __construct(
        protected ilObjLearningSequence $obj,
        string $xml
    ) {
        parent::__construct();

        $this->setXMLContent($xml);

        $this->object = [];
        $this->ls_item_data = [];
        $this->settings = [];
        $this->lp_settings = [];
        $this->lp_settings["lp_item_ref_ids"] = [];
        $this->counter = 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function start(): array
    {
        $this->startParsing();
        $ret = [];
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
            case Writer::TAG_LSO:
                $this->object["ref_id"] = $attributes["ref_id"];
                $this->settings["members_gallery"] = $attributes['members_gallery'];
                break;
            case Writer::TAG_LPSETTING:
                $this->lp_settings["lp_type"] = $attributes['type'];
                $this->lp_settings["lp_mode"] = $attributes['mode'];
                $this->lp_settings["lp_item_ref_ids"] = [];
                break;

            case Writer::TAG_LSITEM:
                $this->counter = (int)$attributes["ref_id"];
                $this->ls_item_data[$this->counter]["ref_id"] = $attributes["ref_id"];
                break;

            case Writer::TAG_CONDITION:
                $this->ls_item_data[$this->counter]["condition_type"] = $attributes["type"];
                $this->ls_item_data[$this->counter]["condition_value"] = '';
                break;

            default:
                break;
        }
    }

    public function handleEndTag($parser, string $name): void
    {
        $this->cdata = trim($this->cdata);

        switch ($name) {
            case Writer::TAG_LPREFID:
                $this->lp_settings["lp_item_ref_ids"][] = trim($this->cdata);
                break;
            default:
                break;
        }

        $this->cdata = '';
    }

    public function handleCharacterData($parser, $data): void
    {
        $this->cdata .= ($data ?? "");
    }
}
