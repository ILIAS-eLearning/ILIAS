<?php
// PSR-2
namespace Vendor\Package;

use BarClass as Bar;
use FooInterface;
use OtherVendor\OtherPackage\BazClass;
use ZPackage;

class Foo extends Bar implements FooInterface
{
    public function sampleMethod($a, $b = null)
    {
        if ($a === $b) {
            \Vendor\Package\bar();
        } elseif ($a > $b) {
            $foo->bar($arg1);
        } else {
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
$a = 0; $b = 0; $c = 0; $d = 0; $e = 0 ; $f = 0;
$bar = (string) $a;
$foo = (int) $b;
// concat_space
$foo = 'bar' . 3 . 'baz' . 'qux';
// binary_operator_spaces
$a = 1 + $b ^ $d !== $e or $f;
// unary_operator_spaces
$sample = 0;
$sample++;
//Unused blank lines: begin

//Unused blank lines: end
--$sample;
$sample = !!$a;
$sample = ~$c;
function &foo()
{
}
// function_typehint_space
function sample(array $a)
{
}
// return_type_declaration
function bar(int $a) : string
{
};
// whitespace_after_comma_in_array
$sample = array(1,'a',$b,);
