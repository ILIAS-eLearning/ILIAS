<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use PHPUnit\Framework\MockObject\Stub;
use Psr\Http\Message\ServerRequestInterface;

trait ilObjLanguageExtGUITestFixture
{
    use ilObjLanguageGuiTestSupport;

    private function mockHttpWithParsedBody(array $parsed_body): \ILIAS\HTTP\GlobalHttpState
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($parsed_body);

        $http = $this->createStub(\ILIAS\HTTP\GlobalHttpState::class);
        $http->method('request')->willReturn($request);

        return $http;
    }

    private function createFakeLanguageObject(string $key): ilObjLanguageExt&Stub
    {
        /** @var ilObjLanguageExt&Stub $object */
        $object = $this->createStub(ilObjLanguageExt::class);
        $object->key = $key;

        return $object;
    }

    /**
     * Like createLanguageMockReturningTopicAsIs(), but the "lng_*" format strings carry a "%s" that makes the list visible.
     *
     * @param list<string> $installed_languages
     */
    private function createLanguageMockWithFormatStrings(array $installed_languages = []): ilLanguage&Stub
    {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnCallback(
            static fn(string $topic): string => str_starts_with($topic, 'lng_') ? $topic . ': %s' : $topic
        );
        $lng->method('getInstalledLanguages')->willReturn($installed_languages);

        return $lng;
    }
}
