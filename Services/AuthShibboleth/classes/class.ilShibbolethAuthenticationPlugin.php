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
 * Plugin definition
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesAuthShibboleth
 */
abstract class ilShibbolethAuthenticationPlugin extends ilPlugin implements ilShibbolethAuthenticationPluginInt
{
    /**
     * @var ilShibbolethAuthenticationPlugin[]
     */
    protected array $active_plugins = [];

    /**
     * @param mixed $a_value
     */
    protected function checkValue(array $a_user_data, string $a_keyword, $a_value) : bool
    {
        if (!$a_user_data[$a_keyword]) {
            return false;
        }
        if (is_array($a_user_data[$a_keyword])) {
            foreach ($a_user_data[$a_keyword] as $values) {
                if (strcasecmp(trim($values), $a_value) === 0) {
                    return true;
                }
            }

            return false;
        }
        return strcasecmp(trim($a_user_data[$a_keyword]), trim($a_value)) === 0;
    }


    public function beforeLogin(ilObjUser $user) : ilObjUser
    {
        return $user;
    }


    public function afterLogin(ilObjUser $user) : ilObjUser
    {
        return $user;
    }


    public function beforeCreateUser(ilObjUser $user) : ilObjUser
    {
        return $user;
    }


    public function afterCreateUser(ilObjUser $user) : ilObjUser
    {
        return $user;
    }


    public function beforeLogout(ilObjUser $user) : ilObjUser
    {
        return $user;
    }


    public function afterLogout(ilObjUser $user) : ilObjUser
    {
        return $user;
    }


    public function beforeUpdateUser(ilObjUser $user) : ilObjUser
    {
        return $user;
    }


    public function afterUpdateUser(ilObjUser $user) : ilObjUser
    {
        return $user;
    }
}
