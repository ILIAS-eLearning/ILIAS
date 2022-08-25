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

class ilMailBodyPurifierTest extends ilMailBaseTest
{
    public function bodyProvider(): array
    {
        return [
            'Reply indicators are kept' => [
                '> This is the original message' . chr(10) . '> Stretching over multiple lines',
                '> This is the original message' . chr(10) . '> Stretching over multiple lines',
            ],
            'Reply indicators are kept, even if body contains (supported and unsupported) HTML' => [
                '> This is the original <b>message</b>' . chr(10) . '> <section>Stretching</section> over multiple lines',
                '> This is the original < b>message< /b>' . chr(10) . '> < section>Stretching< /section> over multiple lines',
            ]
        ];
    }

    /**
     * @dataProvider bodyProvider
     */
    public function testMailBodyPurifier(string $body, string $expectedBody): void
    {
        $purifier = new ilMailBodyPurifier();

        $this->assertSame($expectedBody, $purifier->purify($body));
    }

    public function testCarriageReturnCharactersAreRemoved(): void
    {
        $purifier = new ilMailBodyPurifier();

        $this->assertStringNotContainsString(chr(13), $purifier->purify(chr(13)));
    }
}
