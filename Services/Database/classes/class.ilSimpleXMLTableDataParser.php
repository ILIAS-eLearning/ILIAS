<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Xml/classes/class.ilSaxParser.php');

/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilSimpleXMLTableDataParser extends ilSaxParser
{

    /**
     * @var string
     */
    protected $table;
    /**
     * @var null|string
     */
    protected $file = null;
    /**
     * @var null|\SimpleXMLElement
     */
    protected $xml = null;
    /**
     * @var string
     */
    protected $value = '';


    /**
     * ilSimpleXMLTableDataParser constructor.
     *
     * @param string $a_xml
     */
    public function __construct($a_xml)
    {
        $this->file = $a_xml;
        $this->xml = simplexml_load_file($this->file);
    }


    public function startParsing()
    {
        global $DIC;
        $ilDB = $DIC->database();

        $table = $this->xml->xpath('/Table');
        foreach ($table[0]->attributes() as $k => $v) {
            $this->table = $v;
        }

        foreach ($this->xml->Row as $row) {
            $data = array();
            foreach ($row->children() as $value) {
                $type = (string) $value['type'];
                $content = (string) $value;
                $data[(string) $value['name']] = array(
                    $type,
                    $content,
                );
            }
            $ilDB->insert($this->table, $data);
        }
    }
}
