<?php
// BEGIN WebDAV
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

require_once "class.ilObjectDAV.php";
require_once "Modules/Category/classes/class.ilObjCategory.php";

/**
* Class ilObjCategoryDAV
*
* Handles DAV requests on a category object.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id$
*
* @package webdav
*/
class ilObjCategoryDAV extends ilObjectDAV
{
	/** 
	* Constructor
	*
	* @param refid A refid to the object.
	*/
	function ilObjCategoryDAV($refid) 
	{
		$this->ilObjectDAV($refid);
	}
	
	/**
	 * Returns the DAV resource type of this object.
	 * 
         * @return String "collection" or "".
	 */
	function getResourceType()
	{
		return "collection";
	}

	/**
	 * Returns the mime type of the content of this object.
         * @return String.
	 */
	function getContentType()
	{
		return 'httpd/unix-directory';
	}
	/**
	 * Returns the number of bytes of the content.
         * @return int.
	 */
	function getContentLength()
	{
		return 0;
	}
	/**
	 * Returns the ilias object type for collections that can be created as children of this object.
	 */
	function getILIASCollectionType()
	{
		return 'cat';
	}
	
	/**
	 * Writes the object data.
         * @return void.
	 */
	function write()
	{
		parent::write();

		// First get all translation entries, ...
		$trans =& $this->obj->getTranslations();
				
		// .. then delete old translation entries, ...
		$this->obj->removeTranslations();
		
		// ...and finally write new translations to object_translation
		for ($i = 0; $i < count($trans["Fobject"]); $i++)
		{
			// first entry is always the default language
			$default = ($i == 0) ? 1 : 0;
			$val = $trans["Fobject"][$i];

			$this->obj->addTranslation($this->obj->getTitle(),$val["desc"],$val["lang"],$default);
	}
	}
	/**	
	* Creates a dav collection as a child of this object.
	*
	* @param	string		the name of the collection.
	* @return	ilObjectDAV	returns the created collection, or null if creation failed.
	*/
	function createCollection($name)
	{
		global $lng, $tree;

		$this->lng =& $lng;
		
		$newObj = new ilObjCategory(0);
		$newObj->setType($this->getILIASCollectionType());
		$newObj->setTitle($name);
		//$newObj->setDescription('');
		$newObj->create();
		$newObj->createReference();
		$newObj->setPermissions($this->getRefId());
		$newObj->putInTree($this->getRefID());
		$newObj->addTranslation($name,'',$lng->getLangKey(),1);
		return new ilObjCategoryDAV($newObj->getRefId(), $newObj);
	}
}
// END WebDAV
?>
