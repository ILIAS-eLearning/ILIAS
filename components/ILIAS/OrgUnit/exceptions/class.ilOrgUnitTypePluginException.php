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
 *********************************************************************/

/**
 * Class ilOrgUnitTypePluginException
 * This exception is thrown whenever one or multiple ilOrgUnitTypeHook plugin(s) did not allow an action on a ilOrgUnitType object,
 * e.g. updating, deleting or setting title.
 * It stores additionally the plugin objects which did not allow the action.
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOrgUnitTypePluginException extends ilObjOrgUnitException
{
    /**
     * Contains plugin objects causing this exception
     * @var array[ilOrgUnitTypeHookPlugin]
     */
    protected $plugins = array();

    public function __construct(string $message, $plugins = array())
    {
        parent::__construct($message);
        $this->plugins = $plugins;
    }

    /**
     * @param string[] $plugins
     */
    public function setPlugins(array $plugins)
    {
        $this->plugins = $plugins;
    }

    /**
     * @return array
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
