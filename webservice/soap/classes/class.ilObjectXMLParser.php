<?php

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *****************************************************************************/
class ilObjectXMLParser extends ilSaxParser
{
    public array $object_data = [];

    private int $ref_id = 0;
    private int $parent_id = 0;
    private int $curr_obj = 0;
    private array $time_target = [];
    private string $cdata = '';

    public function __construct(string $a_xml_data = '', ?bool $throw_exception = false)
    {
        parent::__construct('', $throw_exception);
        $this->setXMLContent($a_xml_data);
    }

    public function getObjectData() : array
    {
        return $this->object_data;
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @return void
     */
    public function setHandlers($a_xml_parser) : void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        switch ($a_name) {
            case 'Objects':
                $this->curr_obj = -1;
                break;

            case 'Object':
                ++$this->curr_obj;

                $this->addProperty('type', (string) $a_attribs['type']);
                $this->addProperty(
                    'obj_id',
                    is_numeric($a_attribs['obj_id']) ? (int) $a_attribs["obj_id"] : ilUtil::__extractId(
                        $a_attribs["obj_id"],
                        IL_INST_ID
                    )
                );
                $this->addProperty('offline', $a_attribs['offline']);
                break;

            case 'ImportId':
            case 'LastUpdate':
            case 'CreateDate':
            case 'Owner':
            case 'Description':
            case 'Title':
                break;

            case 'References':
                $this->time_target = [];
                $this->ref_id = $a_attribs["ref_id"];
                $this->parent_id = $a_attribs['parent_id'];
                break;

            case 'TimeTarget':
                $this->time_target['timing_type'] = $a_attribs['type'];
                break;

            case 'Timing':
                $this->time_target['timing_visibility'] = $a_attribs['visibility'];
                if (isset($a_attribs['starting_time'])) {
                    $this->time_target['starting_time'] = $a_attribs['starting_time'];
                }
                if (isset($a_attribs['ending_time'])) {
                    $this->time_target['ending_time'] = $a_attribs['ending_time'];
                }

                if ($a_attribs['ending_time'] < $a_attribs['starting_time']) {
                    throw new ilObjectXMLException('Starting time must be earlier than ending time.');
                }
                break;

            case 'Suggestion':
                $this->time_target['changeable'] = $a_attribs['changeable'];

                if (isset($a_attribs['starting_time'])) {
                    $this->time_target['suggestion_start'] = $a_attribs['starting_time'];
                }
                if (isset($a_attribs['ending_time'])) {
                    $this->time_target['suggestion_end'] = $a_attribs['ending_time'];
                }
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
            case 'Object':
            case 'Objects':
                break;

            case 'Title':
                $this->addProperty('title', trim($this->cdata));
                break;

            case 'Description':
                $this->addProperty('description', trim($this->cdata));
                break;

            case 'Owner':
                $this->addProperty('owner', trim($this->cdata));
                break;

            case 'CreateDate':
                $this->addProperty('create_date', trim($this->cdata));
                break;

            case 'LastUpdate':
                $this->addProperty('last_update', trim($this->cdata));
                break;

            case 'ImportId':
                $this->addProperty('import_id', trim($this->cdata));
                break;

            case 'References':
                $this->addReference($this->ref_id, $this->parent_id, $this->time_target);
                break;
        }

        $this->cdata = '';
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param string $a_data
     * @return void
     */
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        if ($a_data !== "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->cdata .= $a_data;
        }
    }

    /**
     * @param string$a_name
     * @param string | int $a_value
     * @return void
     */
    private function addProperty(string $a_name, $a_value) : void
    {
        $this->object_data[$this->curr_obj][$a_name] = $a_value;
    }

    private function addReference(int $a_ref_id, int $a_parent_id, array $a_time_target) : void
    {
        $reference['ref_id'] = $a_ref_id;
        $reference['parent_id'] = $a_parent_id;
        $reference['time_target'] = $a_time_target;

        if (isset($reference['time_target']['changeable']) && $reference['time_target']['changeable'] &&
            !isset($reference['time_target']['suggestion_start'], $reference['time_target']['suggestion_end'])) {
            throw new ilObjectXMLException(
                'Missing attributes: "starting_time" and "ending_time" required for attribute "changeable"'
            );
        }

        $this->object_data[$this->curr_obj]['references'][] = $reference;
    }
}
