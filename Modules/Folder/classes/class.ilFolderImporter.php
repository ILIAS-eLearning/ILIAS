<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
* folder xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesFolder
*/
class ilFolderImporter extends ilXmlImporter
{
    private $folder = null;
    

    public function init()
    {
    }
    
    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        include_once './Modules/Folder/classes/class.ilObjFolder.php';
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $this->folder = ilObjectFactory::getInstanceByObjId($new_id, false);
        } elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', 0)) {
            $this->folder = ilObjectFactory::getInstanceByRefId($new_id, false);
        } elseif (!$this->folder instanceof ilObjFolder) {
            $this->folder = new ilObjFolder();
            $this->folder->create(true);
        }

        include_once './Modules/Folder/classes/class.ilFolderXmlParser.php';

        try {
            $parser = new ilFolderXmlParser($this->folder, $a_xml);
            $parser->start();
            $a_mapping->addMapping('Modules/Folder', 'fold', $a_id, $this->folder->getId());
        } catch (ilSaxParserException $e) {
            $GLOBALS['ilLog']->write(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        } catch (ilWebLinkXMLParserException $e) {
            $GLOBALS['ilLog']->write(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }
}
