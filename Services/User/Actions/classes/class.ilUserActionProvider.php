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
 * A class that provides a collection of actions on users
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilUserActionProvider
{
    protected int $user_id;
    protected ilLanguage $lng;
    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
    }

    public function setUserId(int $a_val): void
    {
        $this->user_id = $a_val;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Collect actions for a target user
     */
    abstract public function collectActionsForTargetUser(int $a_target_user): ilUserActionCollection;

    /**
     * @return string component id as defined in services.xml/module.xml
     */
    abstract public function getComponentId(): string;

    /**
     * @return array[string] keys must be unique action ids (strings), values should be the names of the actions (from ilLanguage)
     */
    abstract public function getActionTypes(): array;

    public function getJsScripts(string $a_action_type): array
    {
        return array();
    }
}
