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
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class PrivacyDocGeneratorTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('repo', null, [
            'components' => ['User' => [], 'Mail' => []],
        ]);
    }

    private function phpstanOutput(): string
    {
        $message = static fn(string $component, string $purpose, array $args, int $line): array => [
            'message' => PrivacyResolveReportRule::MARKER . json_encode([
                'privacy_type' => 'PostalAddress',
                'purpose_class' => $purpose,
                'purpose_args' => $args,
                'component' => $component,
                'line' => $line,
            ], JSON_THROW_ON_ERROR),
            'line' => $line,
        ];

        return vfsStream::newFile('output.json')->withContent(json_encode([
            'files' => [
                '/repo/components/ILIAS/User/A.php' => [
                    'messages' => [$message('User', 'DisplayToUser', ['public_profile'], 10)],
                ],
                '/repo/components/ILIAS/Mail/B.php' => [
                    'messages' => [$message('Mail', 'PassToComponent', ['Notifications', 'x'], 20)],
                ],
            ],
        ], JSON_THROW_ON_ERROR))->at($this->root)->url();
    }

    public function testWritesOneReportPerComponent(): void
    {
        $written = new PrivacyDocGenerator(
            $this->phpstanOutput(),
            $this->root->url() . '/components'
        )->run();

        $this->assertSame(['Mail', 'User'], array_keys($written));
        $this->assertFileExists($this->root->url() . '/components/User/PRIVACY_DATA.md');
        $this->assertFileExists($this->root->url() . '/components/Mail/PRIVACY_DATA.md');
        $this->assertStringContainsString(
            '# Privacy Data Usage – User',
            (string) file_get_contents($written['User'])
        );
    }

    public function testDryRunWritesNothing(): void
    {
        $written = new PrivacyDocGenerator(
            $this->phpstanOutput(),
            $this->root->url() . '/components',
            dry_run: true
        )->run();

        $this->assertCount(2, $written);
        $this->assertFileDoesNotExist($this->root->url() . '/components/User/PRIVACY_DATA.md');
        $this->assertFileDoesNotExist($this->root->url() . '/components/Mail/PRIVACY_DATA.md');
    }

    public function testComponentFilter(): void
    {
        $written = new PrivacyDocGenerator(
            $this->phpstanOutput(),
            $this->root->url() . '/components',
            filter_component: 'User'
        )->run();

        $this->assertSame(['User'], array_keys($written));
        $this->assertFileDoesNotExist($this->root->url() . '/components/Mail/PRIVACY_DATA.md');
    }

    public function testTargetFilenameOverride(): void
    {
        $written = new PrivacyDocGenerator(
            $this->phpstanOutput(),
            $this->root->url() . '/components',
            filter_component: 'User',
            target_filename: 'PRIVACY.md'
        )->run();

        $this->assertSame($this->root->url() . '/components/User/PRIVACY.md', $written['User']);
        $this->assertFileExists($this->root->url() . '/components/User/PRIVACY.md');
        $this->assertFileDoesNotExist($this->root->url() . '/components/User/PRIVACY_DATA.md');
    }
}
