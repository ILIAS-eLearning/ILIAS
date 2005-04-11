<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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


/**
* Meta Data class
* always instantiate this class first to set/get single meta data elements
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMD extends ilMDBase
{
	/*
	 * meta elements
	 *
	 */

	function ilMD($a_rbac_id,$a_obj_id,$a_type)
	{
		parent::ilMDBase($a_rbac_id,$a_obj_id,$a_type);
	}

	function &getGeneral()
	{
		include_once 'class.ilMDGeneral.php';

		$gen =& new ilMDGeneral($this);
		$gen->read();

		return $gen;
	}
	function &addGeneral()
	{
		include_once 'class.ilMDGeneral.php';

		$gen =& new ilMDGeneral($this);

		return $gen;
	}


	function &getLifecycle()
	{
		include_once 'class.ilMDLifecycle.php';

		$lif =& new ilMDLifecycle($this);
		$lif->read();

		return $lif;
	}
	function &addLifecycle()
	{
		include_once 'class.ilMDLifecycle.php';

		$lif =& new ilMDLifecycle($this);

		return $lif;
	}

	function &getMetaMetadata()
	{
		include_once 'class.ilMDMetaMetadata.php';

		$met =& new ilMDMetaMetadata($this);
		$met->read();

		return $met;
	}
	function &addMetaMetadata()
	{
		include_once 'class.ilMDMetaMetadata.php';

		$met =& new ilMDMetaMetadata($this);

		return $met;
	}

	function &getTechnical()
	{
		include_once 'class.ilMDTechnical.php';

		$tec =& new ilMDTechnical($this);
		$tec->read();
	}
	function &addTechnical()
	{
		include_once 'class.ilMDTechnical.php';

		$tec =& new ilMDTechnical($this);
	}

	function &getEducational()
	{
		include_once 'class.ilMDEducational.php';

		$tec =& new ilMDEducational($this);
		$tec->read();

		return $tec;
	}
	function &addEducational()
	{
		include_once 'class.ilMDEducational.php';

		$tec =& new ilMDEducational($this);

		return $tec;
	}
	function &getRights()
	{
		include_once 'class.ilMDRights.php';

		$rig =& new ilMDRights($this);
		$rig->read();
		
		return $rig;
	}
	function &addRights()
	{
		include_once 'class.ilMDRights.php';

		$rig =& new ilMDRights($this);
		
		return $rig;
	}

	function &getRelationIds()
	{
		include_once 'class.ilMDRelation.php';

		return ilMDRelation::_getIds($this->getRBACId(),$this->getObjId());
	}
	function &getRelation($a_relation_id)
	{
		if(!$a_relation_id)
		{
			return false;
		}

		include_once 'class.ilMDRelation.php';

		return new ilMDRelation($this,$a_relation_id);
	}
	function &addRelation()
	{
		include_once 'class.ilMDRelation.php';

		return new ilMDRelation($this);
	}


	function &getAnnotationIds()
	{
		include_once 'class.ilMDAnnotation.php';

		return ilMDAnnotation::_getIds($this->getRBACId(),$this->getObjId());
	}
	function &getAnnotation($a_annotation_id)
	{
		if(!$a_annotation_id)
		{
			return false;
		}
		include_once 'class.ilMDAnnotation.php';

		return new ilMDAnnotation($this,$a_annotation_id);
	}
	function &addAnnotation()
	{
		include_once 'class.ilMDAnnotation.php';
		
		return new ilMDAnnotation($this);
	}

	function &getClassificationIds()
	{
		include_once 'class.ilMDClassification.php';

		return ilMDClassification::_getIds($this->getRBACId(),$this->getObjId());
	}
	function &getClassification($a_classification_id)
	{
		if(!$a_classification_id)
		{
			return false;
		}

		include_once 'class.ilMDClassification.php';

		return new ilMDClassification($this,$a_classification_id);
	}
	function &addClassification()
	{
		include_once 'class.ilMDClassification.php';

		return new ilMDClassification($this);
	}

	/*
	 * XML Export of all meta data
	 * @param object (xml writer) see class.ilMD2XML.php
	 * 
	 */
	function toXML(&$writer)
	{
		$writer->xmlStartTag('MetaData');

		// Annotations
		foreach($this->getAnnotationIds() as $id)
		{
			$ann =& $this->getAnnotation($id);
			$ann->toXML($writer);
		}
		
		// Classification
		foreach($this->getClassificationIds() as $id)
		{
			$cla =& $this->getClassification($id);
			$cla->toXML($writer);
		}
		
		$writer->xmlEndTag('MetaData');
	}
}
?>