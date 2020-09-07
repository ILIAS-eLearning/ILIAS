<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesWebServicesECS
 */
class ilECSCmsTreeSynchronizer
{
    private $server = null;
    private $mid = null;
    private $tree_id = null;
    private $tree = null;
    
    private $default_settings = array();
    private $global_settings = null;
    
    
    /**
     * Constructor
     */
    public function __construct(ilECSSetting $server, $mid, $tree_id)
    {
        $this->server = $server;
        $this->mid = $mid;
        $this->tree = new ilECSCmsTree($tree_id);
        $this->tree_id = $tree_id;
        
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
        $this->global_settings = ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->mid);
    }
    
    /**
     * Get server
     * @return ilECSSetting
     */
    public function getServer()
    {
        return $this->server;
    }
    
    /**
     * @return ilECSCmsTree
     */
    public function getTree()
    {
        return $this->tree;
    }
    
    /**
     * Get default settings
     * @return type
     */
    public function getDefaultSettings()
    {
        return (array) $this->default_settings;
    }
    
    /**
     * get global settings
     * @return ilECSNodeMappingSettings
     */
    public function getGlobalSettings()
    {
        return $this->global_settings;
    }
    
    /**
     * Synchronize tree
     *
     * @return boolean
     */
    public function sync()
    {
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
        $this->default_settings = ilECSNodeMappingAssignments::lookupSettings(
            $this->getServer()->getServerId(),
            $this->mid,
            $this->tree_id,
            0
        );
        
        // return if setting is false => no configuration done
        if (!$this->getDefaultSettings()) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': No directory allocation settings. Aborting');
            return true;
        }
            
        // lookup obj id of root node
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        
        $root_obj_id = ilECSCmsTree::lookupRootId($this->tree_id);
        $this->syncNode($root_obj_id, 0);
        
        // Tree structure is up to date, now check node movements
        $this->checkTreeUpdates($root_obj_id);
        return true;
    }
    
    /**
     * Start tree update check
     * @param type $a_root_obj_id
     * @return bool
     */
    protected function checkTreeUpdates($a_root_obj_id)
    {
        if ($this->default_settings['tree_update'] == false) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Tree update disabled for tree with id ' . $this->getTree()->getTreeId());
            return false;
        }
        
        // Start recursion
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';
        $mapping = new ilECSNodeMappingAssignment(
            $this->getServer()->getServerId(),
            $this->mid,
            $this->tree_id,
            $a_root_obj_id
        );
        $a_root_ref_id = $mapping->getRefId();
        if ($a_root_ref_id) {
            $this->handleTreeUpdate($a_root_ref_id, $a_root_obj_id);
        }
    }
    
    /**
     * Handle tree update (recursively)
     * @param type $a_parent_ref_id
     * @param type $tnode_id
     */
    protected function handleTreeUpdate($a_parent_ref_id, $a_tnode_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        // Check if node is already imported at location "parent_ref_id"
        // If not => move it
        $cms_data = new ilECSCmsData($a_tnode_id);
        
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        $import_obj_id = ilECSImport::lookupObjIdByContentId(
            $this->getServer()->getServerId(),
            $this->mid,
            $cms_data->getCmsId()
        );
        if (!$import_obj_id) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': cms tree node not imported. tnode_id: ' . $a_tnode_id);
            return false;
        }
        
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ' parent ref:' . $a_parent_ref_id . ' tnode:' . $a_tnode_id);
        $ref_ids = ilObject::_getAllReferences($import_obj_id);
        $import_ref_id = end($ref_ids);
        $import_ref_id_parent = $tree->getParentId($import_ref_id);
        
        if ($a_parent_ref_id != $import_ref_id_parent) {
            // move node
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Moving node ' . $a_parent_ref_id . ' to ' . $import_ref_id);
            $tree->moveTree($import_ref_id, $a_parent_ref_id);
        }
        
        // iterate through childs
        $childs = $this->getTree()->getChilds($a_tnode_id);
        foreach ((array) $childs as $node) {
            $this->handleTreeUpdate($import_ref_id, $node['child']);
        }
        return true;
    }
    
    /**
     * Sync node
     * @param type $cs_id
     * @param type $setting
     */
    protected function syncNode($tree_obj_id, $parent_id, $a_mapped = false)
    {
        $childs = $this->getTree()->getChilds($tree_obj_id);
        
        $assignment = new ilECSNodeMappingAssignment(
            $this->getServer()->getServerId(),
            $this->mid,
            $this->tree_id,
            $tree_obj_id
        );
        
        if ($assignment->getRefId()) {
            $parent_id = $assignment->getRefId();
        }
        
        // information for deeper levels
        if ($assignment->isMapped()) {
            $a_mapped = true;
        }
        
        if ($a_mapped) {
            $parent_id = $this->syncCategory($assignment, $parent_id);
        }
        
        // this is not necessary
        #if($parent_id)
        {
            // iterate through childs
            foreach ($childs as $node) {
                $this->syncNode($node['child'], $parent_id, $a_mapped);
            }
        }
        return true;
    }
    
    /**
     * Sync category
     * @param ilECSNodeMappingAssignment $ass
     */
    protected function syncCategory(ilECSNodeMappingAssignment $ass, $parent_id)
    {
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        $data = new ilECSCmsData($ass->getCSId());

        // Check if node is imported => create
        // perform title update
        // perform position update
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        $obj_id = ilECSImport::lookupObjIdByContentId(
            $this->getServer()->getServerId(),
            $this->mid,
            $data->getCmsId()
        );
        if ($obj_id) {
            $refs = ilObject::_getAllReferences($obj_id);
            $ref_id = end($refs);
            
            
            $cat = ilObjectFactory::getInstanceByRefId($ref_id, false);
            if (($cat instanceof ilObject) and $this->default_settings['title_update']) {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Updating cms category ');
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Title is ' . $data->getTitle());
                $cat->deleteTranslation($GLOBALS['DIC']['lng']->getDefaultLanguage());
                $cat->addTranslation(
                    $data->getTitle(),
                    $cat->getLongDescription(),
                    $GLOBALS['DIC']['lng']->getDefaultLanguage(),
                    1
                );
                $cat->setTitle($data->getTitle());
                $cat->update();
            } else {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Updating cms category -> nothing to do');
            }
            return $ref_id;
        } elseif ($this->getGlobalSettings()->isEmptyContainerCreationEnabled()) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Creating new cms category');
            
            // Create category
            include_once './Modules/Category/classes/class.ilObjCategory.php';
            $cat = new ilObjCategory();
            $cat->setOwner(SYSTEM_USER_ID);
            $cat->setTitle($data->getTitle());
            $cat->create(); // true for upload
            $cat->createReference();
            $cat->putInTree($parent_id);
            $cat->setPermissions($parent_id);
            $cat->deleteTranslation($GLOBALS['DIC']['lng']->getDEfaultLanguage());
            $cat->addTranslation(
                $data->getTitle(),
                $cat->getLongDescription(),
                $GLOBALS['DIC']['lng']->getDefaultLanguage(),
                1
            );
            
            // set imported
            $import = new ilECSImport(
                $this->getServer()->getServerId(),
                $cat->getId()
            );
            $import->setMID($this->mid);
            $import->setContentId($data->getCmsId());
            $import->setImported(true);
            $import->save();
            
            return $cat->getRefId();
        } else {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Creation of empty containers is disabled.');
            return 0;
        }
    }
}
