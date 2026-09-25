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

namespace ILIAS\Data\Privacy\PHPStan\Generator;

use ILIAS\Data\Privacy\PHPStan\Collector\PrivacyResolveReportRule;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class CollectorResultParserTest extends TestCase
{
    private function outputFile(array $data): string
    {
        $root = vfsStream::setup('phpstan');
        return vfsStream::newFile('output.json')
            ->withContent(json_encode($data, JSON_THROW_ON_ERROR))
            ->at($root)->url();
    }

    public function testExtractsMarkedMessages(): void
    {
        $payload = [
            'privacy_type' => 'ILIAS\\Data\\Privacy\\Types\\PostalAddress',
            'purpose_class' => 'DisplayToUser',
            'purpose_args' => ['public_profile'],
            'component' => 'User',
            'line' => 12,
        ];
        $file = $this->outputFile([
            'files' => [
                '/repo/components/ILIAS/User/SomeClass.php' => [
                    'messages' => [
                        ['message' => 'Some unrelated PHPStan error', 'line' => 3],
                        ['message' => PrivacyResolveReportRule::MARKER . json_encode($payload, JSON_THROW_ON_ERROR), 'line' => 12],
                    ],
                ],
            ],
        ]);

        $entries = new CollectorResultParser()->parse($file);

        $this->assertCount(1, $entries);
        $this->assertSame('DisplayToUser', $entries[0]['purpose_class']);
        $this->assertSame(['public_profile'], $entries[0]['purpose_args']);
        $this->assertSame('User', $entries[0]['component']);
        $this->assertSame('/repo/components/ILIAS/User/SomeClass.php', $entries[0]['file']);
        $this->assertSame(12, $entries[0]['line']);
    }

    public function testMissingPayloadKeysFallBackToDefaults(): void
    {
        $file = $this->outputFile([
            'files' => [
                'f.php' => [
                    'messages' => [
                        ['message' => PrivacyResolveReportRule::MARKER . '{}', 'line' => 1],
                    ],
                ],
            ],
        ]);

        $entries = new CollectorResultParser()->parse($file);

        $this->assertSame(
            [
                'privacy_type' => 'unknown',
                'purpose_class' => 'unknown',
                'purpose_args' => [],
                'component' => 'Unknown',
                'file' => 'f.php',
                'line' => 0,
            ],
            $entries[0]
        );
    }

    public function testNonArrayPayloadIsIgnored(): void
    {
        $file = $this->outputFile([
            'files' => [
                'f.php' => [
                    'messages' => [
                        ['message' => PrivacyResolveReportRule::MARKER . '"scalar-payload"', 'line' => 1],
                    ],
                ],
            ],
        ]);

        $this->assertSame([], new CollectorResultParser()->parse($file));
    }

    public function testEmptyOutput(): void
    {
        $this->assertSame([], new CollectorResultParser()->parse($this->outputFile(['files' => []])));
        $this->assertSame([], new CollectorResultParser()->parse($this->outputFile(['totals' => []])));
    }

    public function testUnreadableFileThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        @new CollectorResultParser()->parse(vfsStream::setup('empty')->url() . '/missing.json');
    }

    public function testInvalidJsonThrows(): void
    {
        $root = vfsStream::setup('phpstan');
        $file = vfsStream::newFile('broken.json')->withContent('{not json')->at($root)->url();

        $this->expectException(\JsonException::class);
        new CollectorResultParser()->parse($file);
    }
}
