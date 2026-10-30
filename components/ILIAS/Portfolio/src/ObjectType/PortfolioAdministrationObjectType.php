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

namespace ILIAS\Portfolio\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class PortfolioAdministrationObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'prfa';
    }

    public function getClassName(): string
    {
        return 'PortfolioAdministration';
    }

    public function getLocation(): string
    {
        return $this->getComponent() . '/Administration';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::RBAC,
            ObjectTypeFlag::SYSTEM,
            ObjectTypeFlag::REPOSITORY,
            ObjectTypeFlag::ADMINISTRATION,
        ];
    }

    public function getTranslationType(): string
    {
        return 'sys';
    }

    public function getParentTypes(): array
    {
        return [
            'adm' => 1,
        ];
    }
}
