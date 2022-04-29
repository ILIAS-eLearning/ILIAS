<?php declare(strict_types=1);

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
 * Class ilAbstractMailMemberRoles
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
abstract class ilAbstractMailMemberRoles
{
    /**
     * @param int $ref_id
     * @return array{role_id: int, mailbox: string, form_option_title: string, default_checked?: bool}[]
     */
    abstract public function getMailRoles(int $ref_id) : array;

    abstract public function getRadioOptionTitle() : string;

    final public function getMailboxRoleAddress(int $role_id) : string
    {
        return (new ilRoleMailboxAddress($role_id))->value();
    }
}
