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
    protected string $current_container_setting = '';

    // State for ItemConditions parsing
    protected bool $in_item_conditions = false;
    protected ?array $current_condition = null;
    protected ?array $current_table = null;
    protected ?array $current_row = null;
    protected string $current_col_name = '';
    protected bool $in_col = false;

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
        xml_set_element_handler($a_xml_parser, $this->handleBeginTag(...), $this->handleEndTag(...));
        xml_set_character_data_handler($a_xml_parser, $this->handleCharacterData(...));
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
                $this->settings["mode"] = $attributes['mode'];
                $this->settings["start_ref_id"] = $attributes['start_ref_id'];
                $this->settings["end_ref_id"] = $attributes['end_ref_id'];
                break;
            case Writer::TAG_LPSETTING:
                $this->lp_settings["lp_type"] = $attributes['type'];
                $this->lp_settings["lp_mode"] = $attributes['mode'];
                $this->lp_settings["lp_item_ref_ids"] = [];
                break;

            case Writer::TAG_LSITEM:
                $this->counter = (int) $attributes["ref_id"];
                $this->ls_item_data[$this->counter]["ref_id"] = $attributes["ref_id"];
                if (isset($attributes["position"])) {
                    $this->ls_item_data[$this->counter]["position"] = $attributes["position"];
                }
                break;

            case Writer::TAG_CONDITION:
                $this->ls_item_data[$this->counter]["condition_type"] = $attributes["type"];
                $this->ls_item_data[$this->counter]["condition_value"] = '';
                break;

            case Writer::TAG_ITEM_CONDITIONS:
                $this->in_item_conditions = true;
                if (!isset($this->ls_item_data[$this->counter]["conditions"])) {
                    $this->ls_item_data[$this->counter]["conditions"] = [];
                }
                break;

            case Writer::TAG_ITEM_CONDITION:
                if ($this->in_item_conditions) {
                    $this->current_condition = [
                        'type' => $attributes['type'] ?? '',
                        'subtype' => $attributes['subtype'] ?? null,
                        'data' => [],
                    ];
                }
                break;

            case 'Table':
                if ($this->current_condition !== null) {
                    $this->current_table = [
                        'table' => $attributes['name'] ?? '',
                        'fields' => [],
                        'rows' => [],
                    ];
                }
                break;

            case 'Field':
                if ($this->current_table !== null) {
                    $field_name = $attributes['name'] ?? '';
                    $meta = $attributes;
                    unset($meta['name']);
                    $this->current_table['fields'][$field_name] = $meta;
                }
                break;

            case 'Row':
                if ($this->current_table !== null) {
                    $this->current_row = [];
                }
                break;

            case 'Col':
                if ($this->current_row !== null) {
                    $this->current_col_name = $attributes['name'] ?? '';
                    $this->in_col = true;
                }
                break;

            case Writer::TAG_CONTAINERSETTING:
                $this->current_container_setting = $attributes['id'];
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

            case Writer::TAG_CONTAINERSETTING:
                if ($this->current_container_setting) {
                    ilContainer::_writeContainerSetting(
                        $this->obj->getId(),
                        $this->current_container_setting,
                        trim($this->cdata)
                    );
                }
                break;

            case Writer::TAG_TITLE:
                $this->obj->setTitle(trim($this->cdata));
                break;

            case Writer::TAG_DESCRIPTION:
                $this->obj->setDescription(trim($this->cdata));
                break;

            case 'Col':
                if ($this->current_row !== null && $this->in_col) {
                    $this->current_row[$this->current_col_name] = $this->cdata;
                }
                $this->in_col = false;
                $this->current_col_name = '';
                break;

            case 'Row':
                if ($this->current_table !== null && $this->current_row !== null) {
                    $this->current_table['rows'][] = $this->current_row;
                }
                $this->current_row = null;
                break;

            case 'Table':
                if ($this->current_condition !== null && $this->current_table !== null) {
                    $this->current_condition['data'][] = $this->current_table;
                }
                $this->current_table = null;
                break;

            case Writer::TAG_ITEM_CONDITION:
                if ($this->in_item_conditions && $this->current_condition !== null) {
                    $this->ls_item_data[$this->counter]["conditions"][] = $this->current_condition;
                }
                $this->current_condition = null;
                break;

            case Writer::TAG_ITEM_CONDITIONS:
                $this->in_item_conditions = false;
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
