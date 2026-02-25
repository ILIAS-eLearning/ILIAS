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

class ilMailMimeSenderUserById extends ilMailMimeSenderUser
{
    /** @var array<int, ilObjUser> */
    protected static array $user_instances = [];

    public function __construct(ilSetting $settings, int $usr_id, TemplateEngineFactoryInterface $template_engine_factory)
    {
        if (!array_key_exists($usr_id, self::$user_instances)) {
            self::$user_instances[$usr_id] = new ilObjUser($usr_id);
        }

        parent::__construct($settings, self::$user_instances[$usr_id], $template_engine_factory);
    }

    public static function addUserToCache(int $usr_id, ilObjUser $user): void
    {
        self::$user_instances[$usr_id] = $user;
    }
}
