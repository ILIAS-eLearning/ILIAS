<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
* Webresource xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesWebResource
*/
class ilWebResourceImporter extends ilXmlImporter
{
    private $webl = null;
    

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
            $this->link = ilObjectFactory::getInstanceByObjId($new_id, false);
        } else {
            include_once './Modules/WebResource/classes/class.ilObjLinkResource.php';
            $this->link = new ilObjLinkResource();
            $this->link->setType('webr');
            $this->link->create(true);
        }

        try {
            include_once './Modules/WebResource/classes/class.ilWebLinkXmlParser.php';
            $parser = new ilWebLinkXmlParser($this->link, $a_xml);
            $parser->setMode(ilWebLinkXmlParser::MODE_CREATE);
            $parser->start();
            $a_mapping->addMapping('Modules/WebResource', 'webr', $a_id, $this->link->getId());
        } catch (ilSaxParserException $e) {
            $GLOBALS['DIC']->logger()->webr()->error(': Parsing failed with message, "' . $e->getMessage() . '".');
        } catch (ilWebLinkXMLParserException $e) {
            $GLOBALS['DIC']->logger()->error(': Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }
}
