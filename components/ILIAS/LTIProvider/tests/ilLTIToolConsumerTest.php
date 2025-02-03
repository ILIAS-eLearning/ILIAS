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

use PHPUnit\Framework\TestCase;

/**
 * Class ilLTIToolConsumerTest
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilLTIToolConsumerTest extends TestCase
{
    public function testTitle(): void
    {
        $this->markTestSkipped('Test skipped while integrating the new dependencies in LTI components.');

        $ltiToolConsumer = new ilLTIPlatform(
            $this->createMock(ilLTIDataConnector::class)
        );
        $testString = str_shuffle(uniqid('abcdefgh'));
        $ltiToolConsumer->setTitle($testString);

        $this->assertEquals($testString, $ltiToolConsumer->getTitle());
    }
}
