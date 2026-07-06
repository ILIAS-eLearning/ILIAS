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
 */

declare(strict_types=1);



use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Language\Language;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase, please only use inside this context.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait LanguageStubs
{
    protected function createFixedLanguageStub(string $translation): Language&MockObject
    {
        $stub = $this->createMock(Language::class);
        $stub->method('txt')->willReturn($translation);
        return $stub;
    }

    protected function createRelayArgumentLanguageStub(): Language&MockObject
    {
        $stub = $this->createMock(Language::class);
        $stub->method('txt')->willReturnArgument(0);
        return $stub;
    }
}
