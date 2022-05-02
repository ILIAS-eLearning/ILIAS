<?php declare(strict_types=1);

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
 * Preloader for object list GUIs
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjectListGUIPreloader
{
    protected ilObjectDefinition $obj_definition;
    protected ilTree $tree;
    protected ilObjectDataCache $obj_data_cache;
    protected ilObjUser $user;
    protected ilRbacSystem $rbacsystem;
    protected ilFavouritesManager $fav_manager;

    protected int $context;

    protected array $obj_ids = [];
    protected array $obj_ids_by_type = [];
    protected array $ref_ids = [];
    protected array $ref_ids_by_type = [];
    protected array $types = [];

    public function __construct(int $context)
    {
        global $DIC;

        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->fav_manager = new ilFavouritesManager();
        $this->context = $context;
    }
    
    public function addItem(int $obj_id, string $type, ?int $ref_id = null) : void
    {
        $this->obj_ids[] = $obj_id;
        $this->obj_ids_by_type[$type][] = $obj_id;
        $this->types[] = $type;
        
        if ($ref_id) {
            $this->ref_ids[] = $ref_id;
            $this->ref_ids_by_type[$type][] = $ref_id;
        }
    }
    
    public function preload() : void
    {
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
            
            if (isset($this->ref_ids_by_type[$type])) {
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

            $class = $this->obj_definition->getClassName($type);
            $location = $this->obj_definition->getLocation($type);
            if ($class && $location) { // #12775
                $full_class = "ilObj" . $class . "Access";
                if (is_file($location . "/class." . $full_class . ".php")) {
                    include_once($location . "/class." . $full_class . ".php");
                }
                if (class_exists($full_class)) {
                    call_user_func(
                        array($full_class, "_preloadData"),
                        $this->obj_ids_by_type[$type] ?? [],
                        $this->ref_ids_by_type[$type] ?? []
                    );
                }
            }
        }
        
        if ($this->ref_ids) {
            $this->tree->preloadDeleted($this->ref_ids);
            $this->tree->preloadDepthParent($this->ref_ids);
            $this->obj_data_cache->preloadReferenceCache($this->ref_ids, false);
            $this->rbacsystem->preloadRbacPaCache($this->ref_ids, $this->user->getId());
            
            if ($this->user->getId() != ANONYMOUS_USER_ID &&
                $this->context != ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP) {
                $this->fav_manager->loadData($this->user->getId(), $this->ref_ids);
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
