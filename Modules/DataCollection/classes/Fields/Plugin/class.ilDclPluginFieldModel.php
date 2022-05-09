<?php

/**
 * Class ilDclPluginFieldModel
 * @author  Michael Herren <mh@studer-raimann.ch>
 */
class ilDclPluginFieldModel extends ilDclBaseFieldModel
{
    public function getValidFieldProperties() : array
    {
        return array_merge(array(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME), $this->getCustomValidFieldProperties());
    }

    /**
     * Method for adding custom fields to plugins
     */
    public function getCustomValidFieldProperties() : array
    {
        return array();
    }

    /**
     * @return bool
     */
    public function allowFilterInListView() : bool
    {
        return false;
    }
}
