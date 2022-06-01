<?php

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
 * XML parser for container structure
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilContainerXmlParser
{
    protected ilSetting $settings;
    protected ilObjectDefinition $obj_definition;
    protected ilLogger $cont_log;
    private int $source = 0;
    private ?ilImportMapping $mapping = null;
    private string $xml = '';
    private int $root_id = 0;
    public static array $style_map = [];

    public function __construct(
        ilImportMapping $mapping,
        string $xml = ''
    ) {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $this->mapping = $mapping;
        $this->xml = $xml;
        $this->cont_log = ilLoggerFactory::getLogger('cont');
    }

    public function getMapping() : ?ilImportMapping
    {
        return $this->mapping;
    }
    
    public function parse(string $a_root_id) : void
    {
        $sxml = simplexml_load_string($this->xml);
        $this->root_id = (int) $a_root_id;
        foreach ($sxml->Item as $item) {
            $this->initItem($item, $this->mapping->getTargetId());
        }
    }
    
    protected function initItem(
        SimpleXMLElement $item,
        int $a_parent_node
    ) : void {
        $ilSetting = $this->settings;
        
        $title = (string) $item['Title'];
        $ref_id = (string) $item['RefId'];
        $obj_id = (string) $item['Id'];
        $type = (string) $item['Type'];
        
        
        $new_ref = $this->getMapping()->getMapping('Services/Container', 'refs', $ref_id);

        if (
            !$new_ref &&
            ($obj_id == $this->root_id)
        ) {
            // if container without subitems a dummy container has already been created
            // see ilImportContainer::createDummy()
            $new_ref = $this->mapping->getMapping('Services/Container', 'refs', '0');
            
            // see below and ilContainerImporter::finalProcessing()
            $this->mapping->addMapping('Services/Container', 'objs', $obj_id, (string) ilObject::_lookupObjId((int) $new_ref));
        }
        
        if (!$new_ref) {
            $new_ref = $this->createObject((int) $ref_id, (int) $obj_id, $type, $title, $a_parent_node);
        }
        if (!$new_ref) {
            // e.g inactive plugin
            return;
        }

        // Course item information
        foreach ($item->Timing as $timing) {
            $this->parseTiming($new_ref, $a_parent_node, $timing);
        }

        foreach ($item->Item as $subitem) {
            $this->initItem($subitem, $new_ref);
        }
            
        $new_obj_id = $this->mapping->getMapping('Services/Container', 'objs', $obj_id);
            
        // style
        if ((int) $item['Style']) {
            self::$style_map[(int) $item['Style']][] = $new_obj_id;
        }
        
        // pages
        if ($ilSetting->get('enable_cat_page_edit', '0')) {
            if ($item['Page'] == "1") {
                $this->mapping->addMapping('Services/COPage', 'pg', 'cont:' . $obj_id, 'cont:' . $new_obj_id);
                $this->cont_log->debug("add pg cont mapping, old: " . $obj_id . ", new: " . $new_obj_id . ", Page: -" . $item['Page'] . "-");
            }
            
            if ($item['StartPage'] == "1") {
                $this->mapping->addMapping('Services/COPage', 'pg', 'cstr:' . $obj_id, 'cstr:' . $new_obj_id);
            }
        }
    }
    
    // Parse timing info
    protected function parseTiming(
        int $a_ref_id,
        int $a_parent_id,
        SimpleXMLElement $timing
    ) : void {
        $type = (string) $timing['Type'];
        $visible = (string) $timing['Visible'];
        $changeable = (string) $timing['Changeable'];
        
        $crs_item = new ilObjectActivation();
        $crs_item->setTimingType((int) $type);
        $crs_item->toggleVisible((bool) $visible);
        $crs_item->toggleChangeable((bool) $changeable);
        
        foreach ($timing->children() as $sub) {
            switch ($sub->getName()) {
                case 'Start':
                    $dt = new ilDateTime((string) $sub, IL_CAL_DATETIME, ilTimeZone::UTC);
                    $crs_item->setTimingStart($dt->get(IL_CAL_UNIX));
                    break;
                
                case 'End':
                    $dt = new ilDateTime((string) $sub, IL_CAL_DATETIME, ilTimeZone::UTC);
                    $crs_item->setTimingEnd($dt->get(IL_CAL_UNIX));
                    break;

                case 'SuggestionStart':
                    $dt = new ilDateTime((string) $sub, IL_CAL_DATETIME, ilTimeZone::UTC);
                    $crs_item->setSuggestionStart($dt->get(IL_CAL_UNIX));
                    break;

                case 'SuggestionEnd':
                    $dt = new ilDateTime((string) $sub, IL_CAL_DATETIME, ilTimeZone::UTC);
                    $crs_item->setSuggestionEnd($dt->get(IL_CAL_UNIX));
                    break;
                
                case 'EarliestStart':
                    $dt = new ilDateTime((string) $sub, IL_CAL_DATETIME, ilTimeZone::UTC);
                    $crs_item->setEarliestStart($dt->get(IL_CAL_UNIX));
                    break;

                case 'LatestEnd':
                    break;
            }
        }
        
        
        if ($crs_item->getTimingStart()) {
            $crs_item->update($a_ref_id, $a_parent_id);
        }
    }
    
    protected function createObject(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title,
        int $parent_node
    ) : ? int {
        $objDefinition = $this->obj_definition;

        // A mapping for this object already exists => create reference
        $new_obj_id = $this->getMapping()->getMapping('Services/Container', 'objs', (string) $obj_id);
        if ($new_obj_id) {
            $obj = ilObjectFactory::getInstanceByObjId((int) $new_obj_id, false);
            if ($obj instanceof  ilObject) {
                $obj->createReference();
                $obj->putInTree($parent_node);
                $obj->setPermissions($parent_node);
                $this->mapping->addMapping('Services/Container', 'refs', (string) $ref_id, (string) $obj->getRefId());
                return $obj->getRefId();
            }
        }

        if (!$objDefinition->isAllowedInRepository($type) || $objDefinition->isInactivePlugin($type)) {
            $this->cont_log->notice('Cannot import object of type: ' . $type);
            return null;
        }

        $class_name = "ilObj" . $objDefinition->getClassName($type);
        $location = $objDefinition->getLocation($type);

        include_once($location . "/class." . $class_name . ".php");
        $new = new $class_name();
        $new->setTitle($title);
        $new->create(true);
        $new->createReference();
        $new->putInTree($parent_node);
        $new->setPermissions($parent_node);
        
        $this->mapping->addMapping('Services/Container', 'objs', (string) $obj_id, (string) $new->getId());
        $this->mapping->addMapping('Services/Container', 'refs', (string) $ref_id, (string) $new->getRefId());
        
        return $new->getRefId();
    }
}
