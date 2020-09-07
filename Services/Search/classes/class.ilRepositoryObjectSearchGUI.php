<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRepositoryObjectSearchGUI
 * Repository object search
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectSearchGUI
{
    private $lng = null;
    private $ctrl = null;
    private $ref_id = 0;
    private $object = null;
    private $parent_obj;
    private $parent_cmd;
    
    
    
    /**
     * Constructor
     */
    public function __construct($a_ref_id, $a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->ref_id = $a_ref_id;
        $this->lng = $lng;
        
        $this->ctrl = $ilCtrl;
        
        
        include_once './Services/Object/classes/class.ilObjectFactory.php';
        $factory = new ilObjectFactory();
        $this->object = $factory->getInstanceByRefId($this->getRefId(), false);
        
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
    }
    
    /**
     * Get standar search block html
     * @param type $a_title
     * @return string
     */
    public static function getSearchBlockHTML($a_title)
    {
        include_once './Services/Search/classes/class.ilRepositoryObjectSearchBlockGUI.php';
        $block = new ilRepositoryObjectSearchBlockGUI($a_title);
        return $block->getHTML();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        if (!$GLOBALS['DIC']['ilAccess']->checkAccess('read', '', $this->getObject()->getRefId())) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->getCtrl()->returnToParent($this->getParentGUI());
        }
        
        $next_class = $this->getCtrl()->getNextClass();
        $cmd = $this->getCtrl()->getCmd();

    
        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * Get language object
     * @return ilLanguage
     */
    public function getLang()
    {
        return $this->lng;
    }
    
    /**
     * Get ctrl
     * @return ilCtrl
     */
    public function getCtrl()
    {
        return $this->ctrl;
    }


    public function getRefId()
    {
        return $this->ref_id;
    }
    
    /**
     *
     * @return ilObject
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * get parent gui
     */
    public function getParentGUI()
    {
        return $this->parent_obj;
    }
    
    /**
     * @return string
     */
    public function getParentCmd()
    {
        return $this->parent_cmd;
    }

    /**
     * Perform search lucene or direct search
     */
    protected function performSearch()
    {
        include_once './Services/Search/classes/class.ilRepositoryObjectDetailSearch.php';
        
        try {
            $search = new ilRepositoryObjectDetailSearch(ilObject::_lookupObjId($this->getRefId()));
            $search->setQueryString(ilUtil::stripSlashes($_POST['search_term']));
            $result = $search->performSearch();
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->getCtrl()->returnToParent($this);
            return false;
        }
        // @todo: add a factory to allow overwriting of search result presentation
        $result_table = $this->getResultTableInstance();
        $result_table->setSearchTerm(ilUtil::stripSlashes($_POST['search_term']));
        $result_table->setResults($result);
        
        $result_table->init();
        $result_table->parse();
        
        $GLOBALS['DIC']['tpl']->setContent($result_table->getHTML());
    }
    
    /**
     * Get result table instance
     * @global type $objDefinition
     * @return type
     */
    public function getResultTableInstance()
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        

        $class = $objDefinition->getClassName($this->getObject()->getType());
        $location = $objDefinition->getLocation($this->getObject()->getType());
        $full_class = "ilObj" . $class . "SearchResultTableGUI";
        
        if (include_once($location . "/class." . $full_class . ".php")) {
            return new $full_class(
                $this,
                'performSearch',
                $this->getRefId()
            );
        }
    }
}
