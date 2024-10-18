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

use PHPUnit\Framework\TestCase;

class PostingReplySubjectBuilderTest extends TestCase
{
    /**
     * @return Generator<string, array{"subject": string, "prefix": string, "repetition_prefix": string, "expected": string}>
     */
    public function postingSubjectProvider(): Generator
    {
        yield 'Subject without reply prefix' => [
            'subject' => 'This is a subject',
            'prefix' => 'Re:',
            'repetition_prefix' => 'Re (%s):',
            'expected' => 'Re: This is a subject'
        ];

        yield 'Subject without reply prefix and prefix without expected end character' => [
            'subject' => 'This is a subject',
            'prefix' => 'Re',
            'repetition_prefix' => 'Re (%s):',
            'expected' => 'Re: This is a subject'
        ];

        yield 'Subject with repeated reply prefix' => [
            'subject' => 'Re: Re: Re: This is a subject',
            'prefix' => 'Re:',
            'repetition_prefix' => 'Re (%s):',
            'expected' => 'Re (4): This is a subject'
        ];

        yield 'Subject with optimized repeated reply prefix' => [
            'subject' => 'Re (3): This is a subject',
            'prefix' => 'Re:',
            'repetition_prefix' => 'Re (%s):',
            'expected' => 'Re (4): This is a subject'
        ];

        yield 'Subject with optimized repeated reply prefix (without spaces)' => [
            'subject' => 'Re(3): This is a subject',
            'prefix' => 'Re:',
            'repetition_prefix' => 'Re (%s):',
            'expected' => 'Re(4): This is a subject'
        ];

        yield 'Subject with repeated reply prefix and repetition-prefix without expected end character' => [
            'subject' => 'Re: Re: Re: This is a subject',
            'prefix' => 'Re:',
            'repetition_prefix' => 'Re (%s)',
            'expected' => 'Re (4): This is a subject'
        ];

        yield 'Subject with optimized repeated reply prefix and repetition-prefix without expected end character' => [
            'subject' => 'Re (3): This is a subject',
            'prefix' => 'Re:',
            'repetition_prefix' => 'Re (%s)',
            'expected' => 'Re (4): This is a subject'
        ];

        yield 'Subject with optimized repeated reply prefix (without spaces) and repetition-prefix without expected end character' => [
            'subject' => 'Re(3): This is a subject',
            'prefix' => 'Re:',
            'repetition_prefix' => 'Re (%s)',
            'expected' => 'Re(4): This is a subject'
        ];
    }

    /**
     * @dataProvider postingSubjectProvider
     */
    public function testPostingSubjectBuilder(
        string $subject,
        string $reply_prefix,
        string $optimized_repeated_reply_prefix,
        string $expected_result
    ): void {
        $posting_subject_builder = new PostingReplySubjectBuilder($reply_prefix, $optimized_repeated_reply_prefix);
        $result = $posting_subject_builder->build($subject);

        $this->assertSame($expected_result, $result);
    }
}
