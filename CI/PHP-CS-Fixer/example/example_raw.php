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

// PSR-12
namespace Vendor\Package;

use ZPackage;
use FooInterface;
use BarClass as Bar;
use OtherVendor\OtherPackage\BazClass;

class Foo extends Bar implements FooInterface
{
	public function sampleMethod($a, $b = null)
	{
		if ($a === $b) 
		{
			bar();
		} 
		elseif ($a > $b) 
		{
				$foo->bar($arg1);
		} 
		else 
		{
				BazClass::bar($arg2, $arg3);
		}
    }

    final public static function bar()
	{
   		// method body
	}
}

/** thx to https://mlocati.github.io/php-cs-fixer-configurator for the examples **/
// cast_spaces
$a = 0; $b = 0; $c = 0; $d=0; $e= 0 ; $f =0;
$bar  = ( string )  $a;
$foo =  (int)$b;
// concat_space
$foo = 'bar' . 3 . 'baz'.'qux';
// binary_operator_spaces
$a= 1  + $b^ $d !==  $e or   $f;
// unary_operator_spaces
$sample = 0;
$sample ++;
//Unused blank lines: begin



//Unused blank lines: end
-- $sample;
$sample = ! ! $a;
$sample = ~  $c;
function & foo(){}
// function_typehint_space
function sample(array$a)
{}
// return_type_declaration
function bar(int $a):string {};
// whitespace_after_comma_in_array
$sample = array(1,'a',$b,);