<?php

/**
 * Class ilDclPluginFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 */
class ilDclPluginFieldModel extends ilDclBaseFieldModel
{

    /**
     * @inheritDoc
     */
    public function getValidFieldProperties()
    {
        return array_merge(array(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME), $this->getCustomValidFieldProperties());
    }


    /**
     * Method for adding custom fields to plugins
     *
     * @return array
     */
    public function getCustomValidFieldProperties()
    {
        return array();
    }


    /**
     * @return bool
     */
    public function allowFilterInListView()
    {
        return false;
    }
}
