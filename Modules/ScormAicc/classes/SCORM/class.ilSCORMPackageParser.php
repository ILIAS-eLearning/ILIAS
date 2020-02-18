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

require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMManifest.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMOrganizations.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMOrganization.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResources.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResourceFile.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResourceDependency.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMTree.php");

/**
* SCORM Package Parser
*
* @author
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMPackageParser extends ilSaxParser
{
    public $cnt;				// counts open elements
    public $current_element;	// store current element type
    public $slm_object;
    public $parent_stack;		// stack of current parent nodes
    public $tree_created;		// flag that determines wether the scorm tree has been created
    public $scorm_tree;		// manifest tree
    public $current_organization;	// current organization object
    public $current_resource;	// current resource object
    public $item_stack;		// current open item objects
    public $package_title = "";	// title for the package (title from organisation)


    /**
    * Constructor
    *
    * @param	object		$a_lm_object	must be of type ilObjLearningModule
    * @param	string		$a_xml_file		xml file
    * @access	public
    */
    public function __construct(&$a_slm_object, $a_xml_file)
    {
        parent::__construct($a_xml_file);
        $this->cnt = array();
        $this->current_element = array();
        $this->slm_object = $a_slm_object;
        $this->tree_created = false;
        $this->parent_stack = array();
        $this->item_stack = array();
    }

    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    public function startParsing()
    {
        parent::startParsing();
    }
    
    public function getPackageTitle()
    {
        return $this->package_title;
    }

    /*
    * update parsing status for a element begin
    */
    public function beginElement($a_name)
    {
        if (!isset($this->status["$a_name"])) {
            $this->cnt[$a_name] == 1;
        } else {
            $this->cnt[$a_name]++;
        }
        $this->current_element[count($this->current_element)] = $a_name;
    }

    /*
    * update parsing status for an element ending
    */
    public function endElement($a_name)
    {
        $this->cnt[$a_name]--;
        unset($this->current_element[count($this->current_element) - 1]);
    }

    /*
    * returns current element
    */
    public function getCurrentElement()
    {
        return ($this->current_element[count($this->current_element) - 1]);
    }

    /*
    * returns current element
    */
    public function getAncestorElement($nr = 1)
    {
        return ($this->current_element[count($this->current_element) - 1 - $nr]);
    }

    /*
    * returns number of current open elements of type $a_name
    */
    public function getOpenCount($a_name)
    {
        if (isset($this->cnt[$a_name])) {
            return $this->cnt[$a_name];
        } else {
            return 0;
        }
    }

    /**
    * generate a tag with given name and attributes
    *
    * @param	string		"start" | "end" for starting or ending tag
    * @param	string		element/tag name
    * @param	array		array of attributes
    */
    public function buildTag($type, $name, $attr="")
    {
        $tag = "<";

        if ($type == "end") {
            $tag.= "/";
        }

        $tag.= $name;

        if (is_array($attr)) {
            foreach ($attr as $k => $v) {
                $tag .= " " . $k . "=\"$v\"";
            }
        }

        $tag.= ">";

        return $tag;
    }

    public function getCurrentParent()
    {
        return $this->parent_stack[count($this->parent_stack) - 1];
    }

    /**
    * handler for begin of element
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        //echo "<br>handlerBeginTag:".$a_name;
        switch ($a_name) {
            case "manifest":
                $manifest = new ilSCORMManifest();
                $manifest->setSLMId($this->slm_object->getId());
                $manifest->setImportId($a_attribs["identifier"]);
                $manifest->setVersion($a_attribs["version"]);
                $manifest->setXmlBase($a_attribs["xml:base"]);
                $manifest->create();
                if (!$this->tree_created) {
                    $this->sc_tree = new ilSCORMTree($this->slm_object->getId());
                    $this->sc_tree->addTree($this->slm_object->getId(), $manifest->getId());
                } else {
                    $this->sc_tree->insertNode($manifest->getId(), $this->getCurrentParent());
                }
                array_push($this->parent_stack, $manifest->getId());
                break;

            case "organizations":
                $organizations = new ilSCORMOrganizations();
                $organizations->setSLMId($this->slm_object->getId());
                $organizations->setDefaultOrganization($a_attribs["default"]);
                $organizations->create();
                $this->sc_tree->insertNode($organizations->getId(), $this->getCurrentParent());
                array_push($this->parent_stack, $organizations->getId());
                break;

            case "organization":
                $organization = new ilSCORMOrganization();
                $organization->setSLMId($this->slm_object->getId());
                $organization->setImportId($a_attribs["identifier"]);
                $organization->setStructure($a_attribs["structure"]);
                $organization->create();
                $this->current_organization =&$organization;
                $this->sc_tree->insertNode($organization->getId(), $this->getCurrentParent());
                array_push($this->parent_stack, $organization->getId());
                break;

            case "item":
                $item = new ilSCORMItem();
                $item->setSLMId($this->slm_object->getId());
                $item->setImportId($a_attribs["identifier"]);
                $item->setIdentifierRef($a_attribs["identifierref"]);
                if (strtolower($a_attribs["isvisible"]) != "false") {
                    $item->setVisible(true);
                } else {
                    $item->setVisible(false);
                }
                $item->setParameters($a_attribs["parameters"]);
                $item->create();
                $this->sc_tree->insertNode($item->getId(), $this->getCurrentParent());
                array_push($this->parent_stack, $item->getId());
                $this->item_stack[count($this->item_stack)] =&$item;
                break;

            case "adlcp:prerequisites":
                $this->item_stack[count($this->item_stack) - 1]->setPrereqType($a_attribs["type"]);
                break;

            case "resources":
                $resources = new ilSCORMResources();
                $resources->setSLMId($this->slm_object->getId());
                $resources->setXmlBase($a_attribs["xml:base"]);
                $resources->create();
                $this->sc_tree->insertNode($resources->getId(), $this->getCurrentParent());
                array_push($this->parent_stack, $resources->getId());
                break;

            case "resource":
                $resource = new ilSCORMResource();
                $resource->setSLMId($this->slm_object->getId());
                $resource->setImportId($a_attribs["identifier"]);
                $resource->setResourceType($a_attribs["type"]);
                $resource->setScormType($a_attribs["adlcp:scormtype"]);
                $resource->setXmlBase($a_attribs["xml:base"]);
                $resource->setHRef($a_attribs["href"]);
                $resource->create();
                $this->current_resource =&$resource;
                $this->sc_tree->insertNode($resource->getId(), $this->getCurrentParent());
                array_push($this->parent_stack, $resource->getId());
                break;

            case "file":
                $file = new ilSCORMResourceFile();
                $file->setHRef($a_attribs["href"]);
                $this->current_resource->addFile($file);
                break;

            case "dependency":
                $dependency = new ilSCORMResourceDependency();
                $dependency->setIdentifierRef($a_attribs["identifierref"]);
                $this->current_resource->addDependency($dependency);
                break;

        }
        $this->beginElement($a_name);
    }

    /**
    * handler for end of element
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        //echo "<br>handlerEndTag:".$a_name;

        switch ($a_name) {
            case "manifest":
            case "organizations":
            case "resources":
                array_pop($this->parent_stack);
                break;

            case "organization":
                $this->current_organization->update();
                array_pop($this->parent_stack);
                break;

            case "item":
                $this->item_stack[count($this->item_stack) - 1]->update();
                unset($this->item_stack[count($this->item_stack) - 1]);
                array_pop($this->parent_stack);
                break;

            case "resource":
                $this->current_resource->update();
                array_pop($this->parent_stack);
                break;

        }
        $this->endElement($a_name);
    }

    /**
    * handler for character data
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        //echo "<br>handlerCharacterData:".$this->getCurrentElement().":".$a_data;
        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        $a_data = preg_replace("/\n/", "", $a_data);
        $a_data = preg_replace("/\t+/", "", $a_data);
        if (!empty($a_data)) {
            switch ($this->getCurrentElement()) {
                case "title":
                    switch ($this->getAncestorElement(1)) {
                        case "organization":
                            $this->current_organization->setTitle(
                                $this->current_organization->getTitle() . $a_data
                            );
                            $this->package_title = $this->current_organization->getTitle();
                            break;

                        case "item":
                            $this->item_stack[count($this->item_stack) - 1]->setTitle(
                                $this->item_stack[count($this->item_stack) - 1]->getTitle() . $a_data
                            );
                            break;
                    }
                    break;

                case "adlcp:prerequisites":
                    $this->item_stack[count($this->item_stack) - 1]->setPrerequisites($a_data);
                    break;

                case "adlcp:maxtimeallowed":
                    $this->item_stack[count($this->item_stack) - 1]->setMaxTimeAllowed($a_data);
                    break;

                case "adlcp:timelimitaction":
                    $this->item_stack[count($this->item_stack) - 1]->setTimeLimitAction($a_data);
                    break;

                case "adlcp:datafromlms":
                    $this->item_stack[count($this->item_stack) - 1]->setDataFromLms($a_data);
                    break;

                case "adlcp:masteryscore":
                    $this->item_stack[count($this->item_stack) - 1]->setMasteryScore($a_data);
                    break;

            }
        }
    }
}
