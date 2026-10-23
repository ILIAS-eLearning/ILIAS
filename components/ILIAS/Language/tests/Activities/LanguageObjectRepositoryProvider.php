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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

/**
 * A RepositoryProvider mock answering per obj_id from small fake language objects (the legacy \ilObjLanguage
 * cannot be built without $DIC). The factory is only asked when the Activity actually touches an object, so
 * a factory that throws proves an object was never reached.
 */
trait LanguageObjectRepositoryProvider
{
    protected function languageObjectProvider(\Closure $lng_objects, \Closure $obj_language_factory): RepositoryProvider&Stub
    {
        $provider = $this->createStub(RepositoryProvider::class);
        $this->answerFromLanguageObjects($provider, $lng_objects, $obj_language_factory);

        return $provider;
    }

    /** Same answers, but a mock for tests that also set expectations on the remaining provider methods. */
    protected function languageObjectProviderMock(\Closure $lng_objects, \Closure $obj_language_factory): RepositoryProvider&MockObject
    {
        $provider = $this->createMock(RepositoryProvider::class);
        $this->answerFromLanguageObjects($provider, $lng_objects, $obj_language_factory);

        return $provider;
    }

    private function answerFromLanguageObjects(
        RepositoryProvider&Stub $provider,
        \Closure $lng_objects,
        \Closure $obj_language_factory
    ): void {
        $provider->method('listLanguageObjects')->willReturnCallback($lng_objects);
        $provider->method('isSystemLanguage')->willReturnCallback(
            static fn(int $id): bool => $obj_language_factory($id)->isSystemLanguage()
        );
        $provider->method('isUserLanguage')->willReturnCallback(
            static fn(int $id): bool => $obj_language_factory($id)->isUserLanguage()
        );
        $provider->method('isInstalled')->willReturnCallback(
            static fn(int $id): bool => $obj_language_factory($id)->isInstalled()
        );
        $provider->method('uninstall')->willReturnCallback(static function (int $id) use ($obj_language_factory): ?array {
            $language = $obj_language_factory($id);

            return $language->uninstall() === '' ? null : $language->getModulesWithUnwrittenOverlay();
        });
        $provider->method('removeLocalChanges')->willReturnCallback(static function (int $id) use ($obj_language_factory): ?array {
            $language = $obj_language_factory($id);

            return $language->removeLocalChanges() ? $language->getModulesWithUnwrittenOverlay() : null;
        });
    }
}
