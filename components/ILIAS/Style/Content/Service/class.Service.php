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

namespace ILIAS\Style\Content;

use ILIAS\DI\Container;

class Service
{
    protected Container $DIC;
    protected static array $instance = [];

    public function __construct(Container $DIC)
    {
        $this->DIC = $DIC;
    }

    public function internal(): InternalService
    {
        return self::$instance["internal"] ??= new InternalService($this->DIC);
    }

    public function gui(): GUIService
    {
        return self::$instance["gui"] ??= new GUIService($this->internal());
    }

    public function domain(): DomainService
    {
        return self::$instance["domain"] ??= new DomainService($this->internal());
    }
}
