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
 * Class ilMd5PasswordEncoder
 * This class implements the ILIAS password encryption mechanism used in ILIAS3/ILIAS4
 * We didn't use any salts until we introduced this password service
 * To implement a new generic Message Digest encoder, please create a separate class.
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 * @deprecated
 */
class ilMd5PasswordEncoder extends ilBasePasswordEncoder
{
    public function encodePassword(string $raw, string $salt) : string
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new ilPasswordException('Invalid password.');
        }

        return md5($raw);
    }

    public function isPasswordValid(string $encoded, string $raw, string $salt) : bool
    {
        return !$this->isPasswordTooLong($raw) && $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }

    public function getName() : string
    {
        return 'md5';
    }
}
