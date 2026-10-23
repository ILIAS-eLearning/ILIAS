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

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Language\Activities\UninstallLanguage;

trait UninstallLanguageTestFixture
{
    use LanguageObjectRepositoryProvider;

    protected function createActivity(
        \Closure $lng_objects,
        \Closure $obj_language_factory,
        ?\ilRbacSystem $rbac = null,
        ?RepositoryProvider $repository = null,
    ): UninstallLanguage {
        return new UninstallLanguage(
            $this->rbacOf($rbac ?? $this->createStub(\ilRbacSystem::class)),
            $this->languageFolder(),
            $repository ?? $this->languageObjectProvider($lng_objects, $obj_language_factory)
        );
    }
}
