<?php
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
 ********************************************************************
 */

/**
 * Class ilDclBooleanFieldModel
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclMobFieldModel extends ilDclFileuploadFieldModel
{
    public static array $mob_suffixes = array('jpg', 'jpeg', 'gif', 'png', 'mp3', 'flx', 'mp4', 'm4v', 'mov', 'wmv');

    public function getValidFieldProperties() : array
    {
        return array(ilDclBaseFieldModel::PROP_WIDTH,
                     ilDclBaseFieldModel::PROP_HEIGHT,
                     ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT
        );
    }

    public function allowFilterInListView() : bool
    {
        return false;
    }
}
