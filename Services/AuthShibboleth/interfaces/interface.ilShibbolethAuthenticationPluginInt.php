<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilShibbolethAuthenticationPluginInt
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilShibbolethAuthenticationPluginInt
{
    public function beforeLogin(ilObjUser $user) : ilObjUser;


    public function afterLogin(ilObjUser $user) : ilObjUser;


    public function beforeLogout(ilObjUser $user) : ilObjUser;


    public function afterLogout(ilObjUser $user) : ilObjUser;


    public function beforeCreateUser(ilObjUser $user) : ilObjUser;


    public function afterCreateUser(ilObjUser $user) : ilObjUser;


    public function beforeUpdateUser(ilObjUser $user) : ilObjUser;


    public function afterUpdateUser(ilObjUser $user) : ilObjUser;
}
