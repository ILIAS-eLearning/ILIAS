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
 *********************************************************************/

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
