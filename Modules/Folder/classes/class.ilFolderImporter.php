<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * folder xml importer
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilFolderImporter extends ilXmlImporter
{
    private $folder = null;
    

    public function init() : void
    {
    }
    
    /**
     * Import XML
     * @param
     * @return void
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $this->folder = ilObjectFactory::getInstanceByObjId($new_id, false);
        } elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', 0)) {
            $this->folder = ilObjectFactory::getInstanceByRefId($new_id, false);
        } elseif (!$this->folder instanceof ilObjFolder) {
            $this->folder = new ilObjFolder();
            $this->folder->create(true);
        }

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
