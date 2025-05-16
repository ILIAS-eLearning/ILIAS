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

use PHPUnit\Framework\Attributes\DataProvider;

class ilMailErrorFormatterTest extends ilMailBaseTestCase
{
    private ilMailErrorFormatter $error_formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $component_factory = $this->getMockBuilder(ilComponentFactory::class)->getMock();

        $this->setGlobalVariable('component.factory', $component_factory);

        $language_mock = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $language_mock->method('txt')->willReturnCallback(static function (string $key): string {
            if ($key === 'error1') {
                return '-' . $key . '-';
            }

            if ($key === 'error3') {
                return $key . ' (1. %s/2. %s/3. %s)';
            }

            return $key;
        });

        $this->error_formatter = new ilMailErrorFormatter($language_mock);
    }

    public static function errorCollectionProvider(): array
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
     * @param ilMailError[] $errors
     */
    #[DataProvider('errorCollectionProvider')]
    public function testErrorFormatter(array $errors, string $exteced_html): void
    {
        $this->assertSame($exteced_html, $this->brutallyTrimHTML($this->error_formatter->format($errors)));
    }
}
