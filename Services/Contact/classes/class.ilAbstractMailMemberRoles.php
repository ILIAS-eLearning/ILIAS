<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilAbstractMailMemberRoles
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
abstract class ilAbstractMailMemberRoles
{
    /**
     * @param int $ref_id
     * @return array
     */
    abstract public function getMailRoles(int $ref_id) : array;
    
    /**
     * @return string
     */
    abstract public function getRadioOptionTitle() : string;

    /**
     * @param int $role_id
     * @return String
     */
    final public function getMailboxRoleAddress(int $role_id) : string
    {
        return (new \ilRoleMailboxAddress($role_id))->value();
    }
}
