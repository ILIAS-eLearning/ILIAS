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

class ilMailErrorFormatterTest extends ilMailBaseTest
{
    private ilMailErrorFormatter $errorFormatter;

    protected function setUp(): void
    {
        parent::setUp();

        $componentFactory = $this->getMockBuilder(ilComponentFactory::class)->getMock();

        $this->setGlobalVariable('component.factory', $componentFactory);

        $languageMock = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $languageMock->method('txt')->willReturnCallback(static function (string $key): string {
            if ('error1' === $key) {
                return '-' . $key . '-';
            }

            if ('error3' === $key) {
                return $key . ' (1. %s/2. %s/3. %s)';
            }

            return $key;
        });

        $this->errorFormatter = new ilMailErrorFormatter($languageMock);
    }

    public function errorCollectionProvider(): array
    {
        return [
            'Zero errors' => [
                [],
                ''
            ],
            'Exactly one error' => [
                [new ilMailError('error1')],
                'error1'
            ],
            'Two errors' => [
                [new ilMailError('error1'), new ilMailError('error2')],
                'error1<ul><li>error2</li></ul>'
            ],
            'More than two errors with placeholders' => [
                [new ilMailError('error1'), new ilMailError('error2'), new ilMailError('error3', ['a', 'b', 'c'])],
                'error1<ul><li>error2</li><li>error3 (1. a/2. b/3. c)</li></ul>'
            ],
        ];
    }

    /**
     * @dataProvider errorCollectionProvider
     * @param ilMailError[] $errors
     */
    public function testErrorFormatter(array $errors, string $expectedHtml): void
    {
        $this->assertSame($expectedHtml, $this->brutallyTrimHTML($this->errorFormatter->format($errors)));
    }
}
