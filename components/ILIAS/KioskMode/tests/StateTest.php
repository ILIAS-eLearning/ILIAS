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

use PHPUnit\Framework\TestCase;
use ILIAS\KioskMode\State;

class StateTest extends TestCase
{
    public function testGetNullValue(): State
    {
        $state = new State();
        $this->assertNull($state->getValueFor('invalid_key'));
        return $state;
    }

    /**
     * @depends testGetNullValue
     */
    public function testValue(State $state): State
    {
        $key = 'key';
        $value = 'value';
        $state = $state->withValueFor($key, $value);
        $this->assertEquals($value, $state->getValueFor($key));
        return $state;
    }

    /**
     * @depends testValue
     */
    public function testSerialize(State $state): void
    {
        $expected = json_encode(['key' => 'value'], JSON_THROW_ON_ERROR);
        $this->assertEquals($expected, $state->serialize());
    }

    /**
     * @depends testValue
     */
    public function testRemoveValue(State $state): void
    {
        $state = $state->withValueFor('keep', 'this');
        $state = $state->withoutKey('key');
        $expected = json_encode(['keep' => 'this'], JSON_THROW_ON_ERROR);
        $this->assertEquals($expected, $state->serialize());
    }
}
