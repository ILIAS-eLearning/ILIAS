<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    private ilSCORMTree $sc_tree;
//    public array $cnt;				// counts open elements
    public array $current_element;	// store current element type
    public object $slm_object;      //better ilObjSCORMModule
    public array $parent_stack;		// stack of current parent nodes
    public bool $tree_created;		// flag that determines wether the scorm tree has been created
    public object $scorm_tree;		// manifest tree
    public object $current_organization;	// current organization object
    public object $current_resource;	// current resource object
    public array $item_stack;		// current open item objects
    public string $package_title = "";	// title for the package (title from organisation)
    /**
     * Constructor
     *
     * @param	object		$a_lm_object	must be of type ilObjLearningModule
     * @param	string		$a_xml_file		xml file
     */
    public function __construct(object $a_slm_object, string $a_xml_file)
    {
        parent::__construct($a_xml_file);
//        $this->cnt = array();
        $this->current_element = array();
        $this->slm_object = $a_slm_object;
        $this->tree_created = false;
        $this->parent_stack = array();
        $this->item_stack = array();
    }

    /**
     * set event handler
     * should be overwritten by inherited class
     *
     * @param resource|XMLParser $a_xml_parser
     * @return void
     */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * @throws ilSaxParserException
     */
    public function startParsing(): void
    {
        parent::startParsing();
    }

    public function getPackageTitle(): string
    {
        return $this->package_title;
    }

    /**
     * update parsing status for a element begin
     */
    public function beginElement(string $a_name): void
    {
//        if (!isset($this->status["$a_name"])) {
//            $this->cnt[$a_name] == 1;
//        } else {
//            $this->cnt[$a_name]++;
//        }
        $this->current_element[count($this->current_element)] = $a_name;
    }

    /**
     * update parsing status for an element ending
     */
    public function endElement(string $a_name): void
    {
//        $this->cnt[$a_name]--;
        unset($this->current_element[count($this->current_element) - 1]);
    }

    /**
     * returns current element
     */
    public function getCurrentElement(): ?string
    {
        return ($this->current_element[count($this->current_element) - 1]);
    }

    public function getAncestorElement(int $nr = 1): ?string
    {
        return ($this->current_element[count($this->current_element) - 1 - $nr]);
    }

    /*
    * returns number of current open elements of type $a_name
    */
//    public function getOpenCount($a_name)
//    {
//        if (isset($this->cnt[$a_name])) {
//            return $this->cnt[$a_name];
//        } else {
//            return 0;
//        }
//    }

    /**
     * generate a tag with given name and attributes
     */
    public function buildTag(string $type, string $name, ?array $attr = null): string
    {
        $tag = "<";

        if ($type === "end") {
            $tag .= "/";
        }

        $tag .= $name;

        if (is_array($attr)) {
            foreach ($attr as $k => $v) {
                $tag .= " " . $k . "=\"$v\"";
            }
        }

        $tag .= ">";

        return $tag;
    }

    public function getCurrentParent(): int
    {
        return $this->parent_stack[count($this->parent_stack) - 1];
    }

    /**
     * handler for begin of element
     * @param resource|XMLParser $a_xml_parser
     * @param string $a_name
     * @param array  $a_attribs
     * @return void
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        //echo "<br>handlerBeginTag:".$a_name;
        switch ($a_name) {
            case "manifest":
                $manifest = new ilSCORMManifest();
                $manifest->setSLMId($this->slm_object->getId());
                $manifest->setImportId($a_attribs["identifier"]);
                $manifest->setVersion($a_attribs["version"]);
                if (isset($a_attribs["xml:base"])) {
                    $manifest->setXmlBase($a_attribs["xml:base"]);
                }
                $manifest->create();
                if (!$this->tree_created) {
                    $this->sc_tree = new ilSCORMTree($this->slm_object->getId());
                    $this->sc_tree->addTree($this->slm_object->getId(), $manifest->getId());
                } else {
                    $this->sc_tree->insertNode($manifest->getId(), $this->getCurrentParent());
                }
                $this->parent_stack[] = $manifest->getId();
                break;

            case "organizations":
                $organizations = new ilSCORMOrganizations();
                $organizations->setSLMId($this->slm_object->getId());
                $organizations->setDefaultOrganization($a_attribs["default"]);
                $organizations->create();
                $this->sc_tree->insertNode($organizations->getId(), $this->getCurrentParent());
                $this->parent_stack[] = $organizations->getId();
                break;

            case "organization":
                $organization = new ilSCORMOrganization();
                $organization->setSLMId($this->slm_object->getId());
                $organization->setImportId($a_attribs["identifier"]);
                if (isset($a_attribs["structure"])) {
                    $organization->setStructure($a_attribs["structure"]);
                }
                $organization->create();
                $this->current_organization = &$organization;
                $this->sc_tree->insertNode($organization->getId(), $this->getCurrentParent());
                $this->parent_stack[] = $organization->getId();
                break;

            case "item":
                $item = new ilSCORMItem();
                $item->setSLMId($this->slm_object->getId());
                $item->setImportId($a_attribs["identifier"]);
                $item->setIdentifierRef($a_attribs["identifierref"]);
                if (isset($a_attribs["isvisible"])) {
                    if (strtolower((string) $a_attribs["isvisible"]) !== "false") {
                        $item->setVisible(true);
                    } else {
                        $item->setVisible(false);
                    }
                }
                if (isset($a_attribs["parameters"])) {
                    $item->setParameters($a_attribs["parameters"]);
                }
                $item->create();
                $this->sc_tree->insertNode($item->getId(), $this->getCurrentParent());
                $this->parent_stack[] = $item->getId();
                $this->item_stack[count($this->item_stack)] = &$item;
                break;

            case "adlcp:prerequisites":
                $this->item_stack[count($this->item_stack) - 1]->setPrereqType($a_attribs["type"]);
                break;

            case "resources":
                $resources = new ilSCORMResources();
                $resources->setSLMId($this->slm_object->getId());
                if (isset($a_attribs["xml:base"])) {
                    $resources->setXmlBase($a_attribs["xml:base"]);
                }
                $resources->create();
                $this->sc_tree->insertNode($resources->getId(), $this->getCurrentParent());
                $this->parent_stack[] = $resources->getId();
                break;

            case "resource":
                $resource = new ilSCORMResource();
                $resource->setSLMId($this->slm_object->getId());
                $resource->setImportId($a_attribs["identifier"]);
                $resource->setResourceType($a_attribs["type"]);
                if (isset($a_attribs["adlcp:scormtype"])) {
                    $resource->setScormType($a_attribs["adlcp:scormtype"]);
                }
                if (isset($a_attribs["xml:base"])) {
                    $resource->setXmlBase($a_attribs["xml:base"]);
                }
                $resource->setHRef($a_attribs["href"]);
                $resource->create();
                $this->current_resource = &$resource;
                $this->sc_tree->insertNode($resource->getId(), $this->getCurrentParent());
                $this->parent_stack[] = $resource->getId();
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
     * @param resource|XMLParser $a_xml_parser
     * @param string             $a_name
     * @return void
     */
    public function handlerEndTag($a_xml_parser, string $a_name): void
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
     * @param resource|XMLParser $a_xml_parser
     * @param string|null        $a_data
     * @return void
     */
    public function handlerCharacterData($a_xml_parser, ?string $a_data): void
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
