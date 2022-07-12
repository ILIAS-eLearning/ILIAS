<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilECSUserConsent
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSUserConsent
{
    private int $usr_id;
    private int $mid;

    protected ilDBInterface $db;

    public function __construct(int $a_usr_id, int $a_mid)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->usr_id = $a_usr_id;
        $this->mid = $a_mid;
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function getMid() : int
    {
        return $this->mid;
    }

    public function save() : void
    {
        $this->db->replace(
            'ecs_user_consent',
            [
                'usr_id' => [ilDBConstants::T_INTEGER, $this->getUserId()],
                'mid' => [ilDBConstants::T_INTEGER, $this->getMid()]
            ],
            []
        );
    }

    public function delete() : void
    {
        $query = 'DELETE FROM ecs_user_consent ' .
            'WHERE usr_id = ' . $this->db->quote(
                $this->getUserId(),
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'AND mid = ' . $this->db->quote(
                $this->getMid(),
                ilDBConstants::T_INTEGER
            );
        $this->db->manipulate($query);
    }
}
