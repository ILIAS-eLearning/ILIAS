<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Class ilRepositoryObjectSearchGUI
 * Repository object search
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectSearchGUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilAccess $access;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjectDefinition $obj_definition;
    private int $ref_id;
    private ilObject $object;
    private object $parent_obj;
    private string $parent_cmd;

    protected GlobalHttpState $http;
    protected Factory $refinery;





    public function __construct(int $a_ref_id, object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->obj_definition = $DIC['objDefinition'];
        
        $this->ref_id = $a_ref_id;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        try {
            $repo_object = ilObjectFactory::getInstanceByRefId($this->getRefId());
            if ($repo_object instanceof ilObject) {
                $this->object = $repo_object;
            }
        } catch (ilObjectNotFoundException $e) {
            throw $e;
        }
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
    }

    public static function getSearchBlockHTML(string $a_title) : string
    {
        $block = new ilRepositoryObjectSearchBlockGUI($a_title);
        return $block->getHTML();
    }

    public function executeCommand() : void
    {
        if (!$this->access->checkAccess('read', '', $this->getObject()->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
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

    public function getLang() : ilLanguage
    {
        return $this->lng;
    }

    public function getCtrl() : ilCtrl
    {
        return $this->ctrl;
    }


    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getObject() : ilObject
    {
        return $this->object;
    }

    public function getParentGUI() : object
    {
        return $this->parent_obj;
    }

    public function getParentCmd() : string
    {
        return $this->parent_cmd;
    }

    /**
     * @throws Exception
     */
    protected function performSearch() : bool
    {
        try {
            $search = new ilRepositoryObjectDetailSearch(ilObject::_lookupObjId($this->getRefId()));

            $search_term = '';
            if ($this->http->wrapper()->post()->has('search_term')) {
                $search_term = $this->http->wrapper()->post()->retrieve(
                    'search_term',
                    $this->refinery->kindlyTo()->string()
                );
            }
            $search->setQueryString($search_term);
            $result = $search->performSearch();
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            $this->getCtrl()->returnToParent($this);
            return false;
        }
        // @todo: add a factory to allow overwriting of search result presentation
        $result_table = $this->getResultTableInstance();
        $result_table->setSearchTerm($search_term);
        $result_table->setResults($result);
        
        $result_table->init();
        $result_table->parse();
        
        $this->tpl->setContent($result_table->getHTML());
        return true;
    }

    public function getResultTableInstance() : ?object
    {
        $class = $this->obj_definition->getClassName($this->getObject()->getType());
        $location = $this->obj_definition->getLocation($this->getObject()->getType());
        $full_class = "ilObj" . $class . "SearchResultTableGUI";

        if (include_once($location . "/class." . $full_class . ".php")) {
            return new $full_class(
                $this,
                'performSearch',
                $this->getRefId()
            );
        }
        return null;
    }
}
