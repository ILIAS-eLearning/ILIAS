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

class ilMailMimeSubjectBuilderTest extends ilMailBaseTestCase
{
    private const string DEFAULT_PREFIX = 'docu default';

    /**
     * @return array<string, array<int, string>>
     */
    public static function globalSubjectPrefixOnlyProvider(): array
    {
        return [
            'Global Prefix without Brackets' => ['docu', 'docu %s'],
            'Global Prefix with Brackets' => ['[docu]', '[docu] %s'],
        ];
    }

    /**
     * @return array<string, array<int, string|null>>
     */
    public static function subjectPrefixesProvider(): array
    {
        return [
            'Global Prefix without Brackets and Additional Context Prefix' => ['docu', 'Course', '[docu : Course] %s'],
            'Global Prefix with Brackets and Additional Context Prefix' => ['[docu]', 'Course', '[docu : Course] %s'],
            'Empty Global Prefix with Brackets and Additional Context Prefix' => [
                '',  // The administrator saved the global email settings form with an empty global subject prefix
                'Course',
                '[Course] %s',
            ],
            'Absent Global Prefix with Brackets and Additional Context Prefix' => [
                null, // The administrator did not save the global email settings form, yet
                'Course',
                '[' . self::DEFAULT_PREFIX . ' : Course] %s',
            ],
        ];
    }

    public function testSubjectMustNotBeChangedWhenNoPrefixShouldBeAdded(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $subject_builder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $this->assertSame($subject, $subject_builder->subject($subject));
        $this->assertSame($subject, $subject_builder->subject($subject, false, 'Course'));
    }

    #[DataProvider('globalSubjectPrefixOnlyProvider')]
    public function testGlobalPrefixMustBePrependedWhenDefinedAndPrefixShouldBeAppended(
        string $global_prefix,
        string $expected_subject
    ): void {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('get')->with('mail_subject_prefix')->willReturn($global_prefix);

        $subject_builder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $expected_subject = sprintf($expected_subject, $subject);
        $this->assertSame($expected_subject, $subject_builder->subject($subject, true));
    }

    public function testDefaultPrefixMustBePrependedWhenNoGlobalPrefixIsDefinedAndPrefixShouldBeAppended(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('get')->with('mail_subject_prefix')->willReturn(
            null
        );

        $subject_builder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $expected_subject = self::DEFAULT_PREFIX . ' ' . $subject;
        $this->assertSame($expected_subject, $subject_builder->subject($subject, true));
    }

    #[DataProvider('subjectPrefixesProvider')]
    public function testContextPrefixMustBePrependedWhenGivenAndPrefixShouldBeAppended(
        ?string $global_prefix,
        string $context_prefix,
        string $expected_subject
    ): void {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('get')->with('mail_subject_prefix')->willReturn($global_prefix);

        $subject_builder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $expected_subject = sprintf($expected_subject, $subject);
        $this->assertSame($expected_subject, $subject_builder->subject($subject, true, $context_prefix));
    }
}
