<?php

/**
 * Class ilDclBooleanFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclMobFieldModel extends ilDclFileuploadFieldModel
{
    public static $mob_suffixes = array('jpg', 'jpeg', 'gif', 'png', 'mp3', 'flx', 'mp4', 'm4v', 'mov', 'wmv');


    /**
     * @inheritDoc
     */
    public function getValidFieldProperties()
    {
        return array(ilDclBaseFieldModel::PROP_WIDTH, ilDclBaseFieldModel::PROP_HEIGHT, ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT);
    }


    /**
     * @return bool
     */
    public function allowFilterInListView()
    {
        return false;
    }
}
