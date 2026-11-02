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

namespace ILIAS\Scripts\Privacy\Documentation;

use ILIAS\Scripts\PHPStan\Rules\Privacy\PrivacyResolveReportRule;
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
        $message = static fn(string $component, string $requirement, array $args, int $line): array => [
            'message' => PrivacyResolveReportRule::MARKER . json_encode([
                'privacy_type' => 'PostalAddress',
                'requirement_class' => $requirement,
                'requirement_args' => $args,
                'component' => $component,
            ], JSON_THROW_ON_ERROR),
            'line' => $line,
        ];

        return vfsStream::newFile('output.json')->withContent(json_encode([
            'files' => [
                '/repo/components/ILIAS/User/A.php' => [
                    'messages' => [$message('User', 'DisplayToUser', ['where' => 'on the profile', 'to' => 'the reader', 'in_order_to' => 'read it'], 10)],
                ],
                '/repo/components/ILIAS/Mail/B.php' => [
                    'messages' => [$message('Mail', 'PassToComponent', ['component' => 'Notifications', 'in_order_to' => 'notify'], 20)],
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
            '# User Privacy',
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

    public function testNeverTouchesTheHandwrittenPrivacyMd(): void
    {
        $handwritten = $this->root->url() . '/components/User/PRIVACY.md';
        file_put_contents($handwritten, 'handwritten');

        new PrivacyDocGenerator($this->phpstanOutput(), $this->root->url() . '/components')->run();

        $this->assertSame('handwritten', file_get_contents($handwritten));
        $this->assertStringContainsString(
            '[PRIVACY.md](PRIVACY.md)',
            (string) file_get_contents($this->root->url() . '/components/User/PRIVACY_DATA.md')
        );
        $this->assertStringContainsString(
            'The component has no handwritten PRIVACY.md yet.',
            (string) file_get_contents($this->root->url() . '/components/Mail/PRIVACY_DATA.md')
        );
    }

    public function testCheckReportsMissingAndOutdatedReportsOnly(): void
    {
        $generator = new PrivacyDocGenerator($this->phpstanOutput(), $this->root->url() . '/components');

        $this->assertSame(['Mail', 'User'], array_keys($generator->check()));

        $generator->run();
        $this->assertSame([], $generator->check());

        file_put_contents($this->root->url() . '/components/User/PRIVACY_DATA.md', 'outdated');
        $this->assertSame(['User'], array_keys($generator->check()));
    }

    public function testRemovesAndReportsReportsOfComponentsWithoutEntries(): void
    {
        $orphan = $this->root->url() . '/components/Orphan';
        mkdir($orphan);
        file_put_contents($orphan . '/PRIVACY_DATA.md', 'old');
        $generator = new PrivacyDocGenerator($this->phpstanOutput(), $this->root->url() . '/components');

        $this->assertArrayHasKey('Orphan', $generator->check());

        $generator->run();
        $this->assertFileDoesNotExist($orphan . '/PRIVACY_DATA.md');
        $this->assertSame([], $generator->check());
    }




    public function testAPartialAnalysisRemovesNoReports(): void
    {
        $other = $this->root->url() . '/components/Other';
        mkdir($other);
        file_put_contents($other . '/PRIVACY_DATA.md', 'from an earlier, complete run');
        $generator = new PrivacyDocGenerator(
            $this->phpstanOutput(),
            $this->root->url() . '/components',
            complete_analysis: false
        );

        $generator->run();

        $this->assertFileExists($other . '/PRIVACY_DATA.md');
        $this->assertArrayNotHasKey('Other', $generator->check());
    }
}
