<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesWebServicesECS
 */
class ilECSCmsTreeSynchronizer
{
    private ilLogger $logger;
    private ilLanguage $lng;
    private ilTree $tree;
    
    private ?\ilECSSetting $server = null;
    private $mid = null;
    private $tree_id = null;
    private ?\ilECSCmsTree $ecs_tree = null;
    
    private array $default_settings = array();
    private ?\ilECSNodeMappingSettings $global_settings = null;
    
    
    /**
     * Constructor
     */
    public function __construct(ilECSSetting $server, $mid, $tree_id)
    {
        global $DIC;
        
        $this->logger = $DIC->logger()->wsrv();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        
        
        $this->server = $server;
        $this->mid = $mid;
        $this->ecs_tree = new ilECSCmsTree($tree_id);
        $this->tree_id = $tree_id;
        
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
    public function getECSTree()
    {
        return $this->ecs_tree;
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
        $this->default_settings = ilECSNodeMappingAssignments::lookupSettings(
            $this->getServer()->getServerId(),
            $this->mid,
            $this->tree_id,
            0
        );
        
        // return if setting is false => no configuration done
        if (!$this->getDefaultSettings()) {
            $this->logger->info('No directory allocation settings. Aborting');
            return true;
        }
            
        // lookup obj id of root node
        
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
            $this->logger->info('Tree update disabled for tree with id ' . $this->getECSTree()->getTreeId());
            return false;
        }
        
        // Start recursion
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
        // Check if node is already imported at location "parent_ref_id"
        // If not => move it
        $cms_data = new ilECSCmsData($a_tnode_id);
        
        $import_obj_id = ilECSImportManager::getInstance()->lookupObjIdByContentId(
            $this->getServer()->getServerId(),
            $this->mid,
            $cms_data->getCmsId()
        );
        if (!$import_obj_id) {
            $this->logger->error('cms tree node not imported. tnode_id: ' . $a_tnode_id);
            return false;
        }
        
        $this->logger->info(' parent ref:' . $a_parent_ref_id . ' tnode:' . $a_tnode_id);
        $ref_ids = ilObject::_getAllReferences($import_obj_id);
        $import_ref_id = end($ref_ids);
        $import_ref_id_parent = $this->tree->getParentId($import_ref_id);
        
        if ($a_parent_ref_id != $import_ref_id_parent) {
            // move node
            $this->logger->info('Moving node ' . $a_parent_ref_id . ' to ' . $import_ref_id);
            $this->tree->moveTree($import_ref_id, $a_parent_ref_id);
        }
        
        // iterate through childs
        $childs = $this->getECSTree()->getChilds($a_tnode_id);
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
        $childs = $this->getECSTree()->getChilds($tree_obj_id);
        
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
        $data = new ilECSCmsData($ass->getCSId());

        // Check if node is imported => create
        // perform title update
        // perform position update
        $obj_id = ilECSImportManager::getInstance()->lookupObjIdByContentId(
            $this->getServer()->getServerId(),
            $this->mid,
            $data->getCmsId()
        );
        if ($obj_id) {
            $refs = ilObject::_getAllReferences($obj_id);
            $ref_id = end($refs);
            
            
            $cat = ilObjectFactory::getInstanceByRefId($ref_id, false);
            if (($cat instanceof ilObject) and $this->default_settings['title_update']) {
                $this->logger->info('Updating cms category ');
                $this->logger->info('Title is ' . $data->getTitle());
                $cat->deleteTranslation($this->lng->getDefaultLanguage());
                $cat->addTranslation(
                    $data->getTitle(),
                    $cat->getLongDescription(),
                    $this->lng->getDefaultLanguage(),
                    1
                );
                $cat->setTitle($data->getTitle());
                $cat->update();
            } else {
                $this->logger->info('Updating cms category -> nothing to do');
            }
            return $ref_id;
        } elseif ($this->getGlobalSettings()->isEmptyContainerCreationEnabled()) {
            $this->logger->info('Creating new cms category');
            
            // Create category
            $cat = new ilObjCategory();
            $cat->setOwner(SYSTEM_USER_ID);
            $cat->setTitle($data->getTitle());
            $cat->create(); // true for upload
            $cat->createReference();
            $cat->putInTree($parent_id);
            $cat->setPermissions($parent_id);
            $cat->deleteTranslation($this->lng->getDEfaultLanguage());
            $cat->addTranslation(
                $data->getTitle(),
                $cat->getLongDescription(),
                $this->lng->getDefaultLanguage(),
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
            $this->logger->info('Creation of empty containers is disabled.');
            return 0;
        }
    }
}
