<?php
include_once 'Services/Migration/DBUpdate_5295/classes/classes/class.ilMD5295SaxParser.php';

class ilMD5295XMLParser extends ilMD5295SaxParser
{
    // So k�nnte bspw eine ContentObjectParser Klasse aussehen.
    // Alle LM spezifischen Attribute werden wie gehabt hier behandelt. Werden Metadata spezifische Attribute �bergeben, werden einfach die
    // entsprechenden Funktionen von ilMD5295SaxParser.php aufgerufen.
    // Wichtig ist nur, da� ein MD-Objekt mit den Object-Ids und dem Objekttyp angelegt wird ($this->setMDObject(new ilMD5295(...)))


    public function __construct($content, $a_obj_id, $a_rbac_id, $a_type)
    {
        $this->setMDObject(new ilMD5295($a_obj_id, $a_rbac_id, $a_type));

        // Wenn content eine XML-Datei ist:
        #parent::__construct($content);

        // Ist content ein xml-String:
        parent::__construct();
        $this->setXMLContent($content);
    }
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        if ($this->in_meta_data) {
            parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
            return true;
        }
            

        switch ($a_name) {
            case 'MetaData':
                $this->in_meta_data = true;
                parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
                return true;
                
            default:
                // hier die Tags aller nicht-MetaData Attribute
        }
    }
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        if ($this->in_meta_data) {
            parent::handlerEndTag($a_xml_parser, $a_name);
            return true;
        }
        switch ($a_name) {
            case 'MetaData':
                $this->in_meta_data = false;
                parent::handlerEndTag($a_xml_parser, $a_name);
                return true;

            default:
                // hier die Tags aller nicht-MetaData Attribute
        }
    }

    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        if ($this->in_meta_data) {
            parent::handlerCharacterData($a_xml_parser, $a_data);
            return true;
        }
    }
}
