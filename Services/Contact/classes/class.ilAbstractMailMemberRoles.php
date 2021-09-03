<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilAbstractMailMemberRoles
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
abstract class ilAbstractMailMemberRoles
{
    
    abstract public function getMailRoles(int $ref_id) : array;
    
    
    abstract public function getRadioOptionTitle() : string;

    
    final public function getMailboxRoleAddress(int $role_id) : string
    {
        return (new \ilRoleMailboxAddress($role_id))->value();
    }
}
