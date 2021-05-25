<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Preloader for object list GUIs
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjectListGUIPreloader
{
    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    protected $context; // [int]
    protected $obj_ids; // [array]
    protected $obj_ids_by_type; // [array]
    protected $ref_ids; // [array]
    protected $ref_ids_by_type; // [array]
    protected $types; // [array]

    /**
     * @var ilFavouritesManager
     */
    protected $fav_manager;
    
    public function __construct($a_context)
    {
        global $DIC;

        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->context = $a_context;
        $this->fav_manager = new ilFavouritesManager();
    }
    
    public function addItem($a_obj_id, $a_type, $a_ref_id = null)
    {
        $this->obj_ids[] = $a_obj_id;
        $this->obj_ids_by_type[$a_type][] = $a_obj_id;
        $this->types[] = $a_type;
        
        if ($a_ref_id) {
            $this->ref_ids[] = $a_ref_id;
            $this->ref_ids_by_type[$a_type][] = $a_ref_id;
        }
    }
    
    public function preload()
    {
        $objDefinition = $this->obj_definition;
        $tree = $this->tree;
        $ilObjDataCache = $this->obj_data_cache;
        $ilUser = $this->user;
        $rbacsystem = $this->rbacsystem;
                                
        if (!$this->obj_ids) {
            return;
        }
        
        $this->obj_ids = array_unique($this->obj_ids);
        $this->types = array_unique($this->types);
        if ($this->ref_ids) {
            $this->ref_ids = array_unique($this->ref_ids);
        }
                        
        // type specific preloads
        foreach ($this->types as $type) {
            $this->obj_ids_by_type[$type] = array_unique($this->obj_ids_by_type[$type]);
            
            if (is_array($this->ref_ids_by_type[$type])) {
                $this->ref_ids_by_type[$type] = array_unique($this->ref_ids_by_type[$type]);
            }

            if ($this->context == ilObjectListGUI::CONTEXT_REPOSITORY ||
                $this->context == ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP ||
                $this->context == ilObjectListGUI::CONTEXT_SEARCH) {
                ilConditionHandler::preloadPersistedConditionsForTargetRecords(
                    $type,
                    $this->obj_ids_by_type[$type]
                );
            }

            $class = $objDefinition->getClassName($type);
            $location = $objDefinition->getLocation($type);
            if ($class && $location) { // #12775
                $full_class = "ilObj" . $class . "Access";
                include_once($location . "/class." . $full_class . ".php");
                if (class_exists($full_class)) {
                    call_user_func(
                        array($full_class, "_preloadData"),
                        $this->obj_ids_by_type[$type],
                        $this->ref_ids_by_type[$type]
                    );
                }
            }
        }
        
        if ($this->ref_ids) {
            $tree->preloadDeleted($this->ref_ids);
            $tree->preloadDepthParent($this->ref_ids);
            $ilObjDataCache->preloadReferenceCache($this->ref_ids, false);
            $rbacsystem->preloadRbacPaCache($this->ref_ids, $ilUser->getId());
            
            if ($ilUser->getId() != ANONYMOUS_USER_ID &&
                $this->context != ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP) {
                $this->fav_manager->loadData($ilUser->getId(), $this->ref_ids);
            }
            
            ilObjectActivation::preloadData($this->ref_ids);
        }
                        
        ilObjectListGUI::preloadCommonProperties($this->obj_ids, $this->context);
        
        if ($this->context == ilObjectListGUI::CONTEXT_REPOSITORY) {
            ilRating::preloadListGUIData($this->obj_ids);
            
            ilAdvancedMDValues::preloadByObjIds($this->obj_ids);
        }
        
        if ($this->context == ilObjectListGUI::CONTEXT_REPOSITORY ||
            $this->context == ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP ||
            $this->context == ilObjectListGUI::CONTEXT_SEARCH) {
            ilLPStatus::preloadListGUIData($this->obj_ids);
        }
    }
}
