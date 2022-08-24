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

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSubjectBuilderTest extends ilMailBaseTest
{
    private const DEFAULT_PREFIX = 'docu default';

    /**
     * @return array<string, array<int, string>>
     */
    public function globalSubjectPrefixOnlyProvider(): array
    {
        return [
            'Global Prefix without Brackets' => ['docu', 'docu %s'],
            'Global Prefix with Brackets' => ['[docu]', '[docu] %s'],
        ];
    }

    /**
     * @return array<string, array<int, string|null>>
     */
    public function subjectPrefixesProvider(): array
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
        $subjectBuilder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $this->assertSame($subject, $subjectBuilder->subject($subject));
        $this->assertSame($subject, $subjectBuilder->subject($subject, false, 'Course'));
    }

    /**
     * @dataProvider globalSubjectPrefixOnlyProvider
     */
    public function testGlobalPrefixMustBePrependedWhenDefinedAndPrefixShouldBeAppended(
        string $globalPrefix,
        string $expectedSubject
    ): void {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('get')->with('mail_subject_prefix')->willReturn($globalPrefix);

        $subjectBuilder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $expectedSubject = sprintf($expectedSubject, $subject);
        $this->assertSame($expectedSubject, $subjectBuilder->subject($subject, true));
    }

    public function testDefaultPrefixMustBePrependedWhenNoGlobalPrefixIsDefinedAndPrefixShouldBeAppended(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('get')->with('mail_subject_prefix')->willReturn(
            null
        );

        $subjectBuilder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $expectedSubject = self::DEFAULT_PREFIX . ' ' . $subject;
        $this->assertSame($expectedSubject, $subjectBuilder->subject($subject, true));
    }

    /**
     * @dataProvider subjectPrefixesProvider
     */
    public function testContextPrefixMustBePrependedWhenGivenAndPrefixShouldBeAppended(
        ?string $globalPrefix,
        string $contextPrefix,
        string $expectedSubject
    ): void {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('get')->with('mail_subject_prefix')->willReturn($globalPrefix);

        $subjectBuilder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $expectedSubject = sprintf($expectedSubject, $subject);
        $this->assertSame($expectedSubject, $subjectBuilder->subject($subject, true, $contextPrefix));
    }
}
