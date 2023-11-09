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

namespace ILIAS\scripts\Rector\DIC;

class DICMemberMap
{
    public const TPL = 'tpl';
    protected array $map = [];

    public function __construct()
    {
        $dicMember = new DICMember(
            self::TPL,
            \ilGlobalTemplateInterface::class,
            ['ui', 'mainTemplate'],
            'main_tpl'
        );
        $dicMember->setAlternativeClasses([\ilTemplate::class, \ilGlobalTemplate::class, \ilGlobalPageTemplate::class]);
        $this->map[self::TPL] = $dicMember;
    }

    public function getByName(string $name): DICMember
    {
        if (!isset($this->map[$name])) {
            throw new \InvalidArgumentException("The dependency '$name' is currently not configured");
        }
        return $this->map[$name];
    }
}
