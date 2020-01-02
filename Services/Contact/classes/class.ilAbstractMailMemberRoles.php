<?php
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
    abstract public function getMailRoles($ref_id);
    
    /**
     * @return string
     */
    abstract public function getRadioOptionTitle();

    /**
     * @param int $role_id
     * @return String
     */
    final public function getMailboxRoleAddress($role_id)
    {
        return (new \ilRoleMailboxAddress($role_id))->value();
    }
}
