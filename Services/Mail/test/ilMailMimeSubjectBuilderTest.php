<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSubjectBuilderTest extends ilMailBaseTest
{
    private const DEFAULT_PREFIX = 'docu default';

    /**
     * @return array<string, array<int, string>>
     */
    public function globalSubjectPrefixOnlyProvider() : array
    {
        return [
            'Global Prefix without Brackets' => ['docu', 'docu %s'],
            'Global Prefix with Brackets' => ['[docu]', '[docu] %s'],
        ];
    }

    /**
     * @return array<string, array<int, string|false>>
     */
    public function subjectPrefixesProvider() : array
    {
        return [
            'Global Prefix without Brackets and Additional Context Prefix' => ['docu', 'Course', '[docu : Course] %s'],
            'Global Prefix with Brackets and Additional Context Prefix' => ['[docu]', 'Course', '[docu : Course] %s'],
            'Empty Global Prefix with Brackets and Additional Context Prefix' => [
                '',  // The administrator saved the global email settings form with an empty global subject prefix
                'Course',
                '[Course] %s'
            ],
            'Absent Global Prefix with Brackets and Additional Context Prefix' => [
                false, // The administrator did not save the global email settings form, yet
                'Course',
                '[' . self::DEFAULT_PREFIX . ' : Course] %s'
            ],
        ];
    }

    public function testSubjectMustNotBeChangedWhenNotPrefixShouldBeAdded() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $subjectBuilder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $this->assertEquals($subject, $subjectBuilder->subject($subject, false));
        $this->assertEquals($subject, $subjectBuilder->subject($subject, false, 'Course'));
    }

    /**
     * @dataProvider globalSubjectPrefixOnlyProvider
     * @param string $globalPrefix
     * @param string $expectedSubject
     */
    public function testGlobalPrefixMustBePrependedWhenDefinedAndPrefixShouldBeAppended(
        string $globalPrefix,
        string $expectedSubject
    ) : void {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('get')->with('mail_subject_prefix')->willReturn($globalPrefix);

        $subjectBuilder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $expectedSubject = sprintf($expectedSubject, $subject);
        $this->assertEquals($expectedSubject, $subjectBuilder->subject($subject, true));
    }

    public function testDefaultPrefixMustBePrependedWhenNoGlobalPrefixIsDefinedAndPrefixShouldBeAppended() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('get')->with('mail_subject_prefix')->willReturn(false);

        $subjectBuilder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $expectedSubject = self::DEFAULT_PREFIX . ' ' . $subject;
        $this->assertEquals($expectedSubject, $subjectBuilder->subject($subject, true));
    }

    /**
     * @dataProvider subjectPrefixesProvider
     * @param string|false $globalPrefix
     * @param string       $contextPrefix
     * @param string       $expectedSubject
     */
    public function testContextPrefixMustBePrependedWhenGivenAndPrefixShouldBeAppended(
        $globalPrefix,
        string $contextPrefix,
        string $expectedSubject
    ) : void {
        $settings = $this->getMockBuilder(ilSetting::class)->onlyMethods(['get'])->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('get')->with('mail_subject_prefix')->willReturn($globalPrefix);

        $subjectBuilder = new ilMailMimeSubjectBuilder($settings, self::DEFAULT_PREFIX);

        $subject = 'phpunit';
        $expectedSubject = sprintf($expectedSubject, $subject);
        $this->assertEquals($expectedSubject, $subjectBuilder->subject($subject, true, $contextPrefix));
    }
}
