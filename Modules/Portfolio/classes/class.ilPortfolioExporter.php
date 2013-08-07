<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
 * Portfolio definition
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @ingroup ServicesPortfolio
 */
class ilPortfolioExporter extends ilXmlExporter
{	
	public function __construct()
	{
			
	}
	
	public function init()
	{
	}
	
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		exit();
	}
	
	public function getValidSchemaVersions($a_entity)
	{
		exit();
	}
	
}
?>