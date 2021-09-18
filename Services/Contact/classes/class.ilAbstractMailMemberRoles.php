<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAbstractMailMemberRoles
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
abstract class ilAbstractMailMemberRoles
{
    /**
     * @param int $ref_id
     * @return array{role_id: int, mailbox: string, form_option_title: string, default_checked: bool}[]
     */
    abstract public function getMailRoles(int $ref_id) : array;

    abstract public function getRadioOptionTitle() : string;

    final public function getMailboxRoleAddress(int $role_id) : string
    {
        return (new ilRoleMailboxAddress($role_id))->value();
    }
}
