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

namespace ILIAS\Tests\Refinery;

use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Refinery\IsExecutableTransformation;
use PHPUnit\Framework\TestCase;
use ILIAS\Language\Language;

class IsExecutableTransformationTest extends TestCase
{
    public function testConstruct(): void
    {
        $language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(IsExecutableTransformation::class, new IsExecutableTransformation($language));
    }

    public function testFailingAccept(): void
    {
        $language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();
        $transformation = new IsExecutableTransformation($language);

        $this->assertFalse($transformation->accepts('I hope this string is no valid path...'));
    }

    public function testSuccessfulAccept(): void
    {
        if (!is_file('/bin/sh')) {
            $this->markTestSkipped('Shell /bin/sh is not available.');
            return;
        }
        $language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();
        $transformation = new IsExecutableTransformation($language);

        $this->assertTrue($transformation->accepts('/bin/sh'));
    }
}
