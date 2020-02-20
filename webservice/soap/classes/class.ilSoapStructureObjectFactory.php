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
   * factory classs for structure objects
   *
   * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
   * @version $Id: class.ilSoapStructureReader.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
   *
   * @package ilias
   */

class ilSoapStructureObjectFactory
{
    public function getInstanceForObject($object)
    {
        $classname = ilSoapStructureObjectFactory::_getClassnameForType($object->getType());

        if ($classname != null) {
            switch ($object->getType()) {
                    case "lm":
                    case "glo":
                    return new $classname(
                        $object->getId(),
                        $object->getType(),
                        $object->getTitle(),
                        $object->getLongDescription(),
                        $object->getRefId()
                    );
                    break;
            }
        }
        return null;
    }

    public function getInstance($objId, $type, $title, $description, $parentRefId)
    {
        $classname = ilSoapStructureObjectFactory::_getClassnameForType($type);
        if ($classname == null) {
            return null;
        }

        return new $classname($objId, $type, $title, $description, $parentRefId);
    }

    public function _getClassnameForType($type)
    {
        switch ($type) {
            case "lm":
                include_once "./webservice/soap/classes/class.ilSoapRepositoryStructureObject.php";
                return "ilSoapRepositoryStructureObject";
            case "st":
                include_once "./webservice/soap/classes/class.ilSoapLMChapterStructureObject.php";
                return "ilSoapLMChapterStructureObject";
            case "pg":
                include_once "./webservice/soap/classes/class.ilSoapLMPageStructureObject.php";
                return "ilSoapLMPageStructureObject";
            case "glo":
                include_once "./webservice/soap/classes/class.ilSoapRepositoryStructureObject.php";
                return "ilSoapRepositoryStructureObject";
            case "git":
                include_once "./webservice/soap/classes/class.ilSoapGLOTermStructureObject.php";
                return "ilSoapGLOTermStructureObject";
            case "gdf":
                include_once "./webservice/soap/classes/class.ilSoapGLOTermDefinitionStructureObject.php";
                return "ilSoapGLOTermDefinitionStructureObject";


        }
        return null;
    }
}
