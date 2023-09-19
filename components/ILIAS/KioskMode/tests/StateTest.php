<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
