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
 * Class ilDclPluginFieldModel
 * @author  Michael Herren <mh@studer-raimann.ch>
 */
class ilDclPluginFieldModel extends ilDclBaseFieldModel
{
    public function getValidFieldProperties(): array
    {
        return array_merge(array(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME), $this->getCustomValidFieldProperties());
    }

    /**
     * Method for adding custom fields to plugins
     */
    public function getCustomValidFieldProperties(): array
    {
        return array();
    }

    /**
     * @return bool
     */
    public function allowFilterInListView(): bool
    {
        return false;
    }
}
