<?php declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilLTIConsumeProviderTest
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilLTIConsumeProviderTest extends TestCase
{
    public function testSetId() : void
    {
        $ltiConsumerProvider = new ilLTIConsumeProvider();
        $testId = rand(10000, 99999);
        $ltiConsumerProvider->setId($testId);

        $this->assertEquals($testId, $ltiConsumerProvider->getId());
    }

    public function testSetTitle() : void
    {
        $ltiConsumerProvider = new ilLTIConsumeProvider();
        $testTitle = str_shuffle(uniqid('abcdefgh'));
        $ltiConsumerProvider->setTitle($testTitle);

        $this->assertEquals($testTitle, $ltiConsumerProvider->getTitle());
    }
}
