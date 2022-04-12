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

class ilQTIAssessmentTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIAssessment::class, new ilQTIAssessment());
    }

    public function testSetGetIdent() : void
    {
        $instance = new ilQTIAssessment();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    public function testSetGetTitle() : void
    {
        $instance = new ilQTIAssessment();
        $instance->setTitle('Some input.');
        $this->assertEquals('Some input.', $instance->getTitle());
    }

    public function testSetGetXmllang() : void
    {
        $instance = new ilQTIAssessment();
        $instance->setXmllang('Some input.');
        $this->assertEquals('Some input.', $instance->getXmllang());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTIAssessment();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }
}
