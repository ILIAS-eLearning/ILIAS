<?php
require_once('class.ilOrgUnitTypeException.php');

/**
 * Class ilOrgUnitTypePluginException
 * This exception is thrown whenever one or multiple ilOrgUnitTypeHook plugin(s) did not allow an action on a ilOrgUnitType object,
 * e.g. updating, deleting or setting title.
 * It stores additionally the plugin objects which did not allow the action.
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOrgUnitTypePluginException extends ilObjOrgUnitException {

    /**
     * Contains plugin objects causing this exception
     * @var array[ilOrgUnitTypeHookPlugin]
     */
    protected $plugins = array();


    public function __construct($a_message, $plugins=array()) {
        parent::__construct($a_message);
        $this->plugins = $plugins;
    }

    /**
     * @param array $plugins
     */
    public function setPlugins($plugins)
    {
        $this->plugins = $plugins;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }


}