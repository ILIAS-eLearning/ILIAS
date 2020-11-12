<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Condition;

use ILIAS\Setup;
use ILIAS\Setup\Condition;
use PHPUnit\Framework\TestCase;

class ExternalConditionObjectiveTest extends TestCase
{
    /**
     * @var string
     */
    protected $label_t;

    /**
     * @var Condition\ExternalConditionObjective
     */
    protected $t;

    /**
     * @var string
     */
    protected $label_f;

    /**
     * @var Condition\ExternalConditionObjective
     */
    protected $f;

    public function setUp() : void
    {
        $this->label_t = "condition_true";
        $this->t = new Condition\ExternalConditionObjective(
            $this->label_t,
            function (Setup\Environment $e) {
                return true;
            }
        );
        $this->label_f = "condition_false";
        $this->f = new Condition\ExternalConditionObjective(
            $this->label_f,
            function (Setup\Environment $e) {
                return false;
            }
        );
    }

    public function testGetHash() : void
    {
        $this->assertIsString($this->t->getHash());
    }

    public function testHashIsDifferentForDifferentMessages() : void
    {
        $this->assertNotEquals($this->t->getHash(), $this->f->getHash());
    }

    public function testGetLabel() : void
    {
        $this->assertIsString($this->f->getLabel());
        $this->assertEquals($this->label_f, $this->f->getLabel());
        $this->assertEquals($this->label_t, $this->t->getLabel());
    }

    public function testIsNotable() : void
    {
        $this->assertTrue($this->f->isNotable());
    }

    public function testGetPreconditions() : void
    {
        $env = $this->createMock(Setup\Environment::class);

        $pre = $this->f->getPreconditions($env);
        $this->assertEquals([], $pre);
    }

    public function testAchieveFalse() : void
    {
        $this->expectException(Setup\UnachievableException::class);
        $env = $this->createMock(Setup\Environment::class);
        $this->f->achieve($env);
    }


    public function testAchieveTrue() : void
    {
        $env = $this->createMock(Setup\Environment::class);
        $res = $this->t->achieve($env);
        $this->assertEquals($env, $res);
    }
}
