<?php
require_once('class.ilMemcacheServer.php');
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('./Services/Form/classes/class.ilMultiSelectInputGUI.php');

/**
 * Class ilMemcacheServerTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilMemcacheServerTableGUI extends ilTable2GUI
{
    const TPL_ID = 'tbl_il_memcached _servers';
    /**
     * @var array
     */
    protected $filter = array();


    public function __construct()
    {
        global $DIC;
        $lng = $DIC['lng'];
        parent::__construct(null, '');
        $this->setTitle($lng->txt('memcache_servers'));
        $this->setLimit(9999);
        $this->initColumns();

        $this->setDefaultOrderField('status');
        $this->setDefaultOrderDirection('asc');

        $this->setEnableHeader(true);
        $this->setFormAction('setup.php?cmd=gateway');
        $this->setRowTemplate('tpl.memcache_servers.html', 'Services/GlobalCache');
        $this->disable('footer');
        $this->setEnableTitle(true);

        $this->parseData();
    }


    /**
     * @param array $a_set
     */
    public function fillRow($a_set)
    {
        /**
         * @var $server ilMemcacheServer
         */
        $server = ilMemcacheServer::find($a_set['id']);

        $this->tpl->setVariable('STATUS', $server->isActive() ? ilUtil::getImagePath('icon_ok.svg') : ilUtil::getImagePath('icon_not_ok.svg'));
        $this->tpl->setVariable('STATUS_SERVER', $server->isReachable() ? ilUtil::getImagePath('icon_ok.svg') : ilUtil::getImagePath('icon_not_ok.svg'));
        $this->tpl->setVariable('HOST', $server->getHost());
        $this->tpl->setVariable('PORT', $server->getPort());
        $this->tpl->setVariable('WEIGHT', $server->getWeight());
        $this->addActionMenu($server);
    }


    /**
     * @param ilMemcacheServer $server
     */
    protected function addActionMenu(ilMemcacheServer $server)
    {
        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->lng->txt('memcache_actions'));
        $current_selection_list->setId('request_overview_actions_' . $server->getId());
        $current_selection_list->setUseImages(false);

        $current_selection_list->addItem($this->lng->txt('memcache_edit'), 'memcache_edit', 'setup.php?cmd=editMemcacheServer&mcsid='
                                                                                            . $server->getId());
        $current_selection_list->addItem($this->lng->txt('memcache_delete'), 'memcache_delete', 'setup.php?cmd=deleteMemcacheServer&mcsid='
                                                                                                . $server->getId());
        $this->tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
    }


    protected function parseData()
    {
        $servers = ilMemcacheServer::getArray();
        $this->setData($servers);
    }


    protected function initColumns()
    {
        $this->addColumn($this->txt('status'), '', '25px');
        $this->addColumn($this->txt('status_server'), '', '25px');
        $this->addColumn($this->txt('host'), '');
        $this->addColumn($this->txt('port'), '');
        $this->addColumn($this->txt('weight'), '');
        $this->addColumn($this->txt('actions'), '', '10px');
    }


    /**
     * @param $key
     *
     * @return string
     */
    protected function txt($key)
    {
        return $this->lng->txt('memcache_' . $key);
    }
}
