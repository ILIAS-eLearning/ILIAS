<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

/**
 * Test standard-constraints of a password.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class PasswordContraintsTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test a set of values
     *
     * @return array[[$constraint,$ok_values,$error_values]]
     */
    public function constraintsProvider()
    {
        $lng = $this->createMock(\ilLanguage::class);
        $d = new \ILIAS\Data\Factory();
        $validation = new \ILIAS\Validation\Factory($d, $lng);
        $v = $validation->password();

        return array(
            array(
                $v->hasMinLength(3),
                [$d->password('abc'), $d->password('abcd')],
                [$d->password('a'), $d->password('ab')]
            ),
            array(
                $v->hasLowerChars(),
                [$d->password('abc'), $d->password('AbC')],
                [$d->password('AB'), $d->password('21'), $d->password('#*+')]
            ),

            array(
                $v->hasUpperChars(),
                [$d->password('Abc'), $d->password('ABC')],
                [$d->password('abc'), $d->password('21'), $d->password('#*+')]
            ),
            array(
                $v->hasNumbers(),
                [$d->password('Ab1'), $d->password('123')],
                [$d->password('abc'), $d->password('ABC'), $d->password('#*+')]
            ),

            array(
                $v->hasSpecialChars(),
                [$d->password('Ab+'), $d->password('123#')],
                [$d->password('abc'), $d->password('ABC'), $d->password('123')]
            )
        );
    }

    /**
     * @dataProvider constraintsProvider
     */
    public function testAccept($constraint, $ok_values, $error_values)
    {
        foreach ($ok_values as $ok_value) {
            $this->assertTrue($constraint->accepts($ok_value));
        }
        foreach ($error_values as $error_value) {
            $this->assertFalse($constraint->accepts($error_value));
        }
    }
}
