<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the factory of constraints
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ValidationFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Validation\Factory
     */
    protected $f = null;

    protected function setUp()
    {
        $this->lng = $this->createMock(\ilLanguage::class);
        $this->f = new Validation\Factory(new Data\Factory(), $this->lng);
    }

    protected function tearDown()
    {
        $this->f = null;
    }

    public function testIsInt()
    {
        $is_numeric = $this->f->isNumeric();
        $this->assertInstanceOf(Validation\Constraint::class, $is_numeric);
    }

    public function testIsNumeric()
    {
        $is_int = $this->f->isInt();
        $this->assertInstanceOf(Validation\Constraint::class, $is_int);
    }

    public function testGreaterThan()
    {
        $gt = $this->f->greaterThan(5);
        $this->assertInstanceOf(Validation\Constraint::class, $gt);
    }

    public function testLessThan()
    {
        $lt = $this->f->lessThan(5);
        $this->assertInstanceOf(Validation\Constraint::class, $lt);
    }

    public function testHasMinLength()
    {
        $min = $this->f->hasMinLength(1);
        $this->assertInstanceOf(Validation\Constraint::class, $min);
    }

    public function testCustom()
    {
        $custom = $this->f->custom(function ($value) {
            return "This was fault";
        }, 5);
        $this->assertInstanceOf(Validation\Constraint::class, $custom);
    }

    public function testSequential()
    {
        $constraints = array(
                $this->f->greaterThan(5),
                $this->f->lessThan(15)
            );

        $sequential = $this->f->sequential($constraints);
        $this->assertInstanceOf(Validation\Constraint::class, $sequential);
    }

    public function testParallel()
    {
        $constraints = array(
                $this->f->greaterThan(5),
                $this->f->lessThan(15)
            );

        $parallel = $this->f->parallel($constraints);
        $this->assertInstanceOf(Validation\Constraint::class, $parallel);
    }

    public function testNot()
    {
        $constraint = $this->f->greaterThan(5);
        $not = $this->f->not($constraint);
        $this->assertInstanceOf(Validation\Constraint::class, $not);
    }

    public function testLoadsLanguageModule()
    {
        $lng = $this->createMock(\ilLanguage::class);

        $lng
            ->expects($this->once())
            ->method("loadLanguageModule")
            ->with(Validation\Factory::LANGUAGE_MODULE);

        new Validation\Factory(new Data\Factory(), $lng);
    }
}
