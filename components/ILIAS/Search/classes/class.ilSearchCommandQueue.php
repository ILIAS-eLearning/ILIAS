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

declare(strict_types=1);
/**
*
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilSearchCommandQueue
{
    private static ?self $instance = null;

    protected ilDBInterface $db;

    /**
     * Constructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * get singleton instance
     */
    public static function factory(): ilSearchCommandQueue
    {
        if (self::$instance instanceof ilSearchCommandQueue) {
            return self::$instance;
        }
        return self::$instance = new ilSearchCommandQueue();
    }

    /**
     * update / save new entry
     */
    public function store(ilSearchCommandQueueElement $element): void
    {
        $this->db->replace(
            'search_command_queue',
            [
                'obj_id' => [ilDBConstants::T_INTEGER, $element->getObjId()],
                'obj_type' => [ilDBConstants::T_TEXT, $element->getObjType()],
                'sub_id' => [ilDBConstants::T_INTEGER, 0]
            ],
            [
                'sub_type' => [ilDBConstants::T_TEXT, ''],
                'command' => [ilDBConstants::T_TEXT, $element->getCommand()],
                'last_update' => [ilDBConstants::T_DATE, $this->db->now()],
                'finished' => [ilDBConstants::T_INTEGER, 0]
            ]
        );
    }
}
