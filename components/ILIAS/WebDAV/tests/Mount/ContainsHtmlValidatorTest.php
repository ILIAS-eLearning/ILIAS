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

namespace ILIAS\WebDAV\Tests\Mount;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Small;
use ILIAS\WebDAV\Mount\ContainsHtmlValidator;

#[Small]
final class ContainsHtmlValidatorTest extends TestCase
{
    #[Test]
    public function plainText_returnsFalse(): void
    {
        $this->assertFalse((new ContainsHtmlValidator('hello world'))->isValid());
    }

    #[Test]
    public function emptyString_returnsFalse(): void
    {
        $this->assertFalse((new ContainsHtmlValidator(''))->isValid());
    }

    #[Test]
    public function textWithoutAngleBrackets_returnsFalse(): void
    {
        $this->assertFalse((new ContainsHtmlValidator('this has 1 > 0 in math'))->isValid());
    }

    #[Test]
    public function singleParagraphTag_returnsTrue(): void
    {
        $this->assertTrue((new ContainsHtmlValidator('<p>hello</p>'))->isValid());
    }

    #[Test]
    public function bareTag_returnsTrue(): void
    {
        $this->assertTrue((new ContainsHtmlValidator('<br/>'))->isValid());
    }

    #[Test]
    public function fullDocument_returnsTrue(): void
    {
        $this->assertTrue((new ContainsHtmlValidator(
            '<html><body><p>hi</p></body></html>'
        ))->isValid());
    }
}
