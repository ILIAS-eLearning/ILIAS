<?php

declare(strict_types=1);

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
 * Class ilMailMimeSenderSystem
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMailMimeSenderUser implements ilMailMimeSender
{
    protected ilSetting $settings;
    protected ilObjUser $user;

    public function __construct(ilSetting $settings, ilObjUser $user)
    {
        $this->settings = $settings;
        $this->user = $user;
    }

    public function hasReplyToAddress(): bool
    {
        return true;
    }

    public function getReplyToAddress(): string
    {
        if (
            true === (bool) $this->settings->get('use_global_reply_to_addr', '0') &&
            is_string($this->settings->get('global_reply_to_addr', '')) &&
            $this->settings->get('global_reply_to_addr', '') !== ''
        ) {
            return $this->settings->get('global_reply_to_addr', '');
        }

        return $this->user->getEmail();
    }

    public function getReplyToName(): string
    {
        return $this->user->getFullname();
    }

    public function hasEnvelopFromAddress(): bool
    {
        return $this->settings->get('mail_system_usr_env_from_addr', '') !== '';
    }

    public function getEnvelopFromAddress(): string
    {
        return $this->settings->get('mail_system_usr_env_from_addr', '');
    }

    public function getFromAddress(): string
    {
        return $this->settings->get('mail_system_usr_from_addr', '');
    }

    public function getFromName(): string
    {
        $from = $this->settings->get('mail_system_usr_from_name', '');
        if ($from === '') {
            return $this->user->getFullname();
        }

        $name = str_ireplace('[FULLNAME]', $this->user->getFullname(), $from);
        $name = str_ireplace('[FIRSTNAME]', $this->user->getFirstname(), $name);
        $name = str_ireplace('[LASTNAME]', $this->user->getLastname(), $name);
        if ($name !== $from) {
            return $name;
        }

        return $from;
    }
}
