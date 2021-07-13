<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        global $DIC;

        $ilLog = $DIC->logger();

        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $this->link = ilObjectFactory::getInstanceByObjId($new_id, false);
        } else {
            $this->link = new ilObjLinkResource();
            $this->link->setType('webr');
            $this->link->create(true);
        }

        try {
            $parser = new ilWebLinkXmlParser($this->link, $a_xml);
            $parser->setMode(ilWebLinkXmlParser::MODE_CREATE);
            $parser->start();
            $a_mapping->addMapping('Modules/WebResource', 'webr', $a_id, $this->link->getId());
        } catch (ilSaxParserException $e) {
            $ilLog->webr()->error(': Parsing failed with message, "' . $e->getMessage() . '".');
        } catch (ilWebLinkXMLParserException $e) {
            $ilLog->error(': Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }
}
