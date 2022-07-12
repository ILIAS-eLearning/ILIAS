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

class ilLearningSequenceActivationTest extends TestCase
{
    public function testCreateObjectMinimal() : void
    {
        $obj = new ilLearningSequenceActivation(22);

        $this->assertInstanceOf(ilLearningSequenceActivation::class, $obj);
        $this->assertEquals(22, $obj->getRefId());
        $this->assertFalse($obj->getIsOnline());
        $this->assertFalse($obj->getEffectiveOnlineStatus());
        $this->assertNull($obj->getActivationStart());
        $this->assertNull($obj->getActivationEnd());
    }

    public function testCreateObjectMaximal() : void
    {
        $start_date = new DateTime('2021-07-21 07:30');
        $end_date = new DateTime('2021-07-21 07:35');

        $obj = new ilLearningSequenceActivation(
            44,
            true,
            true,
            $start_date,
            $end_date
        );

        $this->assertInstanceOf(ilLearningSequenceActivation::class, $obj);
        $this->assertEquals(44, $obj->getRefId());
        $this->assertTrue($obj->getIsOnline());
        $this->assertTrue($obj->getEffectiveOnlineStatus());
        $this->assertEquals($start_date, $obj->getActivationStart());
        $this->assertEquals($end_date, $obj->getActivationEnd());
    }

    public function testWithOnline() : void
    {
        $start_date = new DateTime('2021-07-21 07:44');
        $end_date = new DateTime('2021-07-21 07:50');

        $obj = new ilLearningSequenceActivation(
            555,
            true,
            true,
            $start_date,
            $end_date
        );

        $new_obj = $obj->withIsOnline(false);

        $this->assertInstanceOf(ilLearningSequenceActivation::class, $obj);
        $this->assertEquals(555, $obj->getRefId());
        $this->assertTrue($obj->getIsOnline());
        $this->assertTrue($obj->getEffectiveOnlineStatus());
        $this->assertEquals($start_date, $obj->getActivationStart());
        $this->assertEquals($end_date, $obj->getActivationEnd());


        $this->assertInstanceOf(ilLearningSequenceActivation::class, $new_obj);
        $this->assertEquals(555, $new_obj->getRefId());
        $this->assertFalse($new_obj->getIsOnline());
        $this->assertTrue($new_obj->getEffectiveOnlineStatus());
        $this->assertEquals($start_date, $new_obj->getActivationStart());
        $this->assertEquals($end_date, $new_obj->getActivationEnd());
    }

    public function testWithActivationStart() : void
    {
        $start_date = new DateTime('2021-07-21 07:44');
        $end_date = new DateTime('2021-07-21 07:50');
        $new_date = new DateTime('2021-07-20 08:50');

        $obj = new ilLearningSequenceActivation(
            555,
            true,
            true,
            $start_date,
            $end_date
        );

        $new_obj = $obj->withActivationStart($new_date);

        $this->assertInstanceOf(ilLearningSequenceActivation::class, $obj);
        $this->assertEquals(555, $obj->getRefId());
        $this->assertTrue($obj->getIsOnline());
        $this->assertTrue($obj->getEffectiveOnlineStatus());
        $this->assertEquals($start_date, $obj->getActivationStart());
        $this->assertEquals($end_date, $obj->getActivationEnd());


        $this->assertInstanceOf(ilLearningSequenceActivation::class, $new_obj);
        $this->assertEquals(555, $new_obj->getRefId());
        $this->assertTrue($new_obj->getIsOnline());
        $this->assertTrue($new_obj->getEffectiveOnlineStatus());
        $this->assertEquals($new_date, $new_obj->getActivationStart());
        $this->assertEquals($end_date, $new_obj->getActivationEnd());
    }

    public function testWithActivationEnd() : void
    {
        $start_date = new DateTime('2021-07-21 07:44');
        $end_date = new DateTime('2021-07-21 07:50');
        $new_date = new DateTime('2021-07-17 19:50');

        $obj = new ilLearningSequenceActivation(
            555,
            true,
            true,
            $start_date,
            $end_date
        );

        $new_obj = $obj->withActivationEnd($new_date);

        $this->assertInstanceOf(ilLearningSequenceActivation::class, $obj);
        $this->assertEquals(555, $obj->getRefId());
        $this->assertTrue($obj->getIsOnline());
        $this->assertTrue($obj->getEffectiveOnlineStatus());
        $this->assertEquals($start_date, $obj->getActivationStart());
        $this->assertEquals($end_date, $obj->getActivationEnd());


        $this->assertInstanceOf(ilLearningSequenceActivation::class, $new_obj);
        $this->assertEquals(555, $new_obj->getRefId());
        $this->assertTrue($new_obj->getIsOnline());
        $this->assertTrue($new_obj->getEffectiveOnlineStatus());
        $this->assertEquals($start_date, $new_obj->getActivationStart());
        $this->assertEquals($new_date, $new_obj->getActivationEnd());
    }
}
