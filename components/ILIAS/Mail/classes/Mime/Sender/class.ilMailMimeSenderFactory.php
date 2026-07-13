<?php

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

declare(strict_types=1);

use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;

class ilMailMimeSenderFactory
{
    /** @var array<int, ilMailMimeSender> */
    protected array $senders = [];
    protected int $anonymous_usr_id = 0;

    public function __construct(
        protected ilSetting $settings,
        protected TemplateEngineFactoryInterface $template_engine_factory,
        ?int $anonymous_usr_id = null
    ) {
        if ($anonymous_usr_id === null && defined('ANONYMOUS_USER_ID')) {
            $anonymous_usr_id = ANONYMOUS_USER_ID;
        }
        if ($anonymous_usr_id === null) {
            throw new Exception();
        }

        $this->anonymous_usr_id = $anonymous_usr_id;
    }

    protected function isSystemMail(int $usr_id): bool
    {
        return $usr_id === $this->anonymous_usr_id;
    }

    public function getSenderByUsrId(int $usr_id): ilMailMimeSender
    {
        if (array_key_exists($usr_id, $this->senders)) {
            return $this->senders[$usr_id];
        }

        if ($this->isSystemMail($usr_id)) {
            $sender = $this->system();
        } else {
            $sender = $this->user($usr_id);
        }

        $this->senders[$usr_id] = $sender;

        return $sender;
    }

    public function system(): ilMailMimeSenderSystem
    {
        return new ilMailMimeSenderSystem($this->settings);
    }

    public function user(int $usr_id): ilMailMimeSenderUser
    {
        return new ilMailMimeSenderUserById($this->settings, $usr_id, $this->template_engine_factory);
    }

    public function userByEmailAddress(string $email_address): ilMailMimeSenderUser
    {
        return new ilMailMimeSenderUserByEmailAddress($this->settings, $email_address, $this->template_engine_factory);
    }
}
