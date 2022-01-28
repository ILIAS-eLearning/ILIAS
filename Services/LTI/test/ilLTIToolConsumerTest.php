<?php declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilLTIToolConsumerTest
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilLTIToolConsumerTest extends TestCase
{
    public function testTitle() : void
    {
        $ltiToolConsumer = new ilLTIToolConsumer();
        $testString = str_shuffle(uniqid('abcdefgh'));
        $ltiToolConsumer->setTitle($testString);

        $this->assertEquals($testString, $ltiToolConsumer->getTitle());
    }
}
