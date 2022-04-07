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

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Refinery;
use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class PasswordContraintsTest extends TestCase
{

    /**
     * Test a set of values
     *
     * @return array[[$constraint,$ok_values,$error_values]]
     */
    public function constraintsProvider() : array
    {
        $lng = $this->createMock(\ilLanguage::class);
        $d = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($d, $lng);
        $v = $refinery->password();

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
    public function testAccept($constraint, $ok_values, $error_values) : void
    {
        foreach ($ok_values as $ok_value) {
            $this->assertTrue($constraint->accepts($ok_value));
        }
        foreach ($error_values as $error_value) {
            $this->assertFalse($constraint->accepts($error_value));
        }
    }
}
