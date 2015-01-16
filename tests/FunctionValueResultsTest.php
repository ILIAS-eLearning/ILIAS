<?php
class FunctionValueResultsTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider functions_and_args
     */
    public function testFunctionResult($fun, $args) {
        $fn = _fn($fun);
        $res1 = call_user_func_array($fun, $args);
        $tmp = $fn;
        for ($i = 0; $i < $fn->arity(); ++$i) {
            $tmp = $tmp->apply(_val($args[$i]));
        }
        $res2 = $tmp->get();
        $this->assertEquals($res1, $res2);
    
    }

    public function functions_and_args() {
        $intval = function($a) { return intval($a); };
        $explode = function($a, $b) { return explode($a, $b); };
        return array
            ( array($intval, array("12"))
            , array($intval, array("122123"))
            , array($intval, array("45689"))
            , array($explode, array(" ", "Hello World"))
            , array($explode, array(";", "1;2"))
            , array($explode, array("-", "2015-01-02"))
            );
    }
}

?>
