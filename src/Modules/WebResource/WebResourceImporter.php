<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Modules\WebResource;

use ilObjectFactory;
use ilSaxParserException;
use ilXmlImporter;

/**
 * Class WebResourceImporter
 *
 * Webresource xml importer
 *
 * @package ILIAS\Modules\WebResource
 *
 * @ingroup ModulesWebResource
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class WebResourceImporter extends ilXmlImporter {

	private $webl = NULL;


	public function init() {
	}
	
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping) {
		if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
			$this->link = ilObjectFactory::getInstanceByObjId($new_id, false);
		} else {
			$this->link = new ObjLinkResource();
			$this->link->setType('webr');
			$this->link->create(true);
		}

		try {
			$parser = new WebLinkXmlParser($this->link, $a_xml);
			$parser->setMode(WebLinkXmlParser::MODE_CREATE);
			$parser->start();
			$a_mapping->addMapping('src/Modules/WebResource', 'webr', $a_id, $this->link->getId());
		} catch (ilSaxParserException $e) {
			$GLOBALS['DIC']->logger()->webr()->error(': Parsing failed with message, "' . $e->getMessage() . '".');
		} catch (WebLinkXmlParserException $e) {
			$GLOBALS['DIC']->logger()->error(': Parsing failed with message, "' . $e->getMessage() . '".');
		}
	}
}
