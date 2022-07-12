<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilLTIToolConsumerTest
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilLTIToolConsumerTest extends TestCase
{
    public function testTitle() : void
    {
        $ltiToolConsumer = new ilLTIPlatform();
        $testString = str_shuffle(uniqid('abcdefgh'));
        $ltiToolConsumer->setTitle($testString);

        $this->assertEquals($testString, $ltiToolConsumer->getTitle());
    }
}
