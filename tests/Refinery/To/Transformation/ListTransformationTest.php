<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class ListTransformationTest extends TestCase
{
    /**
     * @throws \ilException
     */
    public function testListTransformationIsValid()
    {
        $listTransformation = new ListTransformation(new StringTransformation());

        $result = $listTransformation->transform(array('hello', 'world'));

        $this->assertEquals(array('hello', 'world'), $result);
    }

    public function testTransformOnEmptyArrayFails()
    {
        $this->expectNotToPerformAssertions();

        $listTransformation = new ListTransformation(new StringTransformation());
        try {
            $result = $listTransformation->transform(array());
        } catch (ConstraintViolationException $exception) {
            return;
        }

        $this->fail();
    }

    public function testApplyToOnEmptyArrayFails()
    {
        $listTransformation = new ListTransformation(new StringTransformation());
        $result = $listTransformation->applyTo(new Ok(array()));
        $this->assertTrue($result->isError());
    }

    public function testTransformOnNullFails()
    {
        $this->expectNotToPerformAssertions();

        $listTransformation = new ListTransformation(new StringTransformation());
        try {
            $result = $listTransformation->transform(null);
        } catch (ConstraintViolationException $exception) {
            return;
        }

        $this->fail();
    }

    public function testApplyToOnNullFails()
    {
        $listTransformation = new ListTransformation(new StringTransformation());
        $result = $listTransformation->applyTo(new Ok(null));
        $this->assertTrue($result->isError());
    }


    public function testListTransformationIsInvalid()
    {
        $this->expectNotToPerformAssertions();

        $listTransformation = new ListTransformation(new StringTransformation());

        try {
            $result = $listTransformation->transform(array('hello', 2));
        } catch (ConstraintViolationException $exception) {
            return;
        }

        $this->fail();
    }

    public function testListApplyIsValid()
    {
        $listTransformation = new ListTransformation(new StringTransformation());

        $result = $listTransformation->applyTo(new Ok(array('hello', 'world')));

        $this->assertEquals(array('hello', 'world'), $result->value());
        $this->assertTrue($result->isOK());
    }

    public function testListApplyIsInvalid()
    {
        $listTransformation = new ListTransformation(new StringTransformation());

        $result = $listTransformation->applyTo(new Ok(array('hello', 2)));

        $this->assertTrue($result->isError());
    }
}
