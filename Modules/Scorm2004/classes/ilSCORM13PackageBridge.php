<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
	*/

/*
 * SCORM 2004 RTE to ILIAS integration bridge
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.ilSCORMExplorer.php 12711 2006-12-01 15:24:41Z akill $
 *
 * @ingroup ModulesScormAicc
 */

require_once "./Modules/Scorm2004/classes/ilSCORM13Package.php";
include_once ("./Modules/Scorm2004/classes/ilSCORM13DB.php");
define('IL_OP_DB_TYPE', 'sqlite');
define('IL_OP_DB_DSN', 'sqlite2:./Modules/Scorm2004/data/sqlite2.db');
define('IL_OP_USER_NAME', '');
define('IL_OP_USER_PASSWORD', '');

class ilSCORM13PackageBridge extends ilSCORM13Package{
	
	
	const DB_ENCODE_XSL = './Modules/Scorm2004/templates/xsl/op/op-scorm13.xsl';
	const DB_DECODE_XSL = './Modules/Scorm2004/templates/xsl/op/op-scorm13-revert.xsl';
	const VALIDATE_XSD = './Modules/Scorm2004/templates/xsd/op/op-scorm13.xsd';


	/**
	 * Removes a SCORM 2004 package from database
	 *
	 * @access       public
	 * @return       string title of package
	 */
	public function dbRemoveAll($packageId) {
		$this->packageId=$packageId;
		parent::dbRemoveAll();
	}

	
	
	/**
	 * Imports an extracted SCORM 2004 module from ilias-data dir into database
	 *
	 * @access       public
	 * @return       string title of package
	 */
	public function il_import($packageFolder,$packeId){
		ilSCORM13DB::init(IL_OP_DB_DSN, IL_OP_DB_TYPE);				
		$this->packageFolder=$packageFolder;
		$this->packageId=$packeId;
		$this->imsmanifestFile = $this->packageFolder . '/' . 'imsmanifest.xml';

		//step 1 - parse Manifest-File
		$this->imsmanifest = new DOMDocument;
		$this->imsmanifest->async = false;
		if (!@$this->imsmanifest->load($this->imsmanifestFile))
		{
			$this->diagnostic[] = 'XML not wellformed';
			return false;
		}
		//step 2 tranform
		$this->manifest = $this->transform($this->imsmanifest, self::DB_ENCODE_XSL);
		
		if (!$this->manifest)
		{
			$this->diagnostic[] = 'Cannot transform into normalized manifest';
			return false;
		}


		//step 3 validation -just for normalized XML
		if (!$this->validate($this->manifest, self::VALIDATE_XSD))
		{
			$this->diagnostic[] = 'normalized XML is not conform to ' . self::VALIDATE_XSD;
			return false;
		}

		//step 4 import into DB
	//	$this->dbAddNew(); // add new package record
	//	$this->dbRemoveAll(); // remove old data on this id
		ilSCORM13DB::begin();
//		$this->dbAddNew(); // add new sahs and package record
		$this->dbImport($this->manifest);
		ilSCORM13DB::commit();

		//step 5
		$x = simplexml_load_string($this->manifest->saveXML());
		// add database values from package and sahs_lm records as defaults
		$x['persistPreviousAttempts'] = $this->packageData['persistPreviousAttempts'];
		$x['online'] = $this->packageData['online'];
		$x['defaultLessonMode'] = $this->packageData['default_lesson_mode'];
		$x['credit'] = $this->packageData['credit'];
		$x['autoReview'] = $this->packageData['auto_review'];
		$j = array();
		// first read resources into flat array to resolve item/identifierref later
		$r = array();
		foreach ($x->resource as $xe)
		{
			$r[strval($xe['id'])] = $xe;
			unset($xe);
		}
		// iterate through items and set href and scoType as activity attributes
		foreach ($x->xpath('//*[local-name()="item"]') as $xe)
		{
			// get reference to resource and set href accordingly
			if ($b = $r[strval($xe['resourceId'])])
			{
				$xe['href'] = strval($b['base']) . strval($b['href']);
				unset($xe['resourceId']);
				if (strval($b['scormType'])=='sco') $xe['sco'] = true;
			}
		}
		// iterate recursivly through activities and build up simple php object
		// with items and associated sequencings
		// top node is the default organization which is handled as an item
		self::jsonNode($x->organization, $j['item']);
		foreach($x->sequencing as $s)
		{
			self::jsonNode($s, $j['sequencing'][]);
		}
		// combined manifest+resources xml:base is set as organization base
		$j['item']['base'] = strval($x['base']);
		// package folder is base to whole playing process
		$j['base'] = $packageFolder . '/';
		$j['foreignId'] = floatval($x['foreignId']); // manifest cp_node_id for associating global (package wide) objectives
		$j['id'] = strval($x['id']); // manifest id for associating global (package wide) objectives

		//step 6 wrapping up
		ilSCORM13DB::setRecord('cp_package', array(
		'obj_id' => $this->packageId,
		'xmldata' => $x->asXML(),
		'jsdata' => json_encode($j),
		), 'obj_id');
		return $j['item']['title'];
	}

	
}


?>