<?php declare(strict_types=1);

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
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;

class ilQTIDisplayfeedbackTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIDisplayfeedback::class, new ilQTIDisplayfeedback());
    }

    public function testSetGetFeedbacktype() : void
    {
        $instance = new ilQTIDisplayfeedback();
        $instance->setFeedbacktype('Some input.');
        $this->assertEquals('Some input.', $instance->getFeedbacktype());
    }

    public function testSetGetLinkrefid() : void
    {
        $instance = new ilQTIDisplayfeedback();
        $instance->setLinkrefid('Some input.');
        $this->assertEquals('Some input.', $instance->getLinkrefid());
    }

    public function testSetGetContent() : void
    {
        $instance = new ilQTIDisplayfeedback();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }
}
