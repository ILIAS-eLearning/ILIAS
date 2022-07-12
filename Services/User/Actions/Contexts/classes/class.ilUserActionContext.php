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
 * A context where user actions are used (e.g. who-is-online, profile, members gallery)
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilUserActionContext
{
    /**
     * Get compoment id of context as defined in service.xml/module.xml
     */
    abstract public function getComponentId() : string;

    /**
     * Get id for context. Should be unique within the component
     */
    abstract public function getContextId() : string;
}
