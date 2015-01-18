<?php

require_once("formlets.php");
require_once("tests/FormletTest.php");

// Acoording to haskells typeclassopedia.
class FormletIsApplicativeTest extends PHPUnit_Framework_TestCase {
    /**
     * Tests first functor law: map id = id 
     * When one maps id, it is as if nothing happened. 
     * @dataProvider formlets_and_values
     */
    public function testFunctorLaw1( $formlet, $fn1, $fn2, $value
                                   , $contains_applicable) {
        $mapped = $formlet->map(_id());
        $this->assertFormletsEqual($formlet, $mapped, $value, $contains_applicable); 
    }

    /**
     * Tests second functor law: map (q . h) = (map q) . (map h) 
     * When i map the composition of two functions it is as if if mapped the
     * functions one after another.
     * @dataProvider formlets_and_values
     */
    public function testFunctorLaw2( $formlet, $fn1, $fn2, $value
                                   , $contains_applicable) {
        $mapped_l = $formlet->map($fn1->composeWith($fn2));
        $mapped_r = $formlet->map($fn1)->map($fn2);
        return $this->assertFormletsEqual( $mapped_l, $mapped_r, $value
                                         , $contains_applicable);
    }

    /**
     * Tests first applicative law: pure id <*> v = v 
     * Pure has no effects when id is used as content.
     * @dataProvider formlets_and_values
     */
    public function testApplicativeIdentityLaw( $formlet, $fn1, $fn2, $value
                                              , $contains_applicable) {
        if ($contains_applicable)
            return;
        $left = _pure(_id())->cmb($formlet);
        $this->assertFormletsEqual($left, $formlet, $value, $contains_applicable);    
    }

    /**
     * Tests second applicative law: pure f <*> pure x  = pure (f x) 
     * Structure is preserved between functor space and function space.
     * That is most propably misformulated.
     * @dataProvider formlets_and_values
     */
    public function testApplicativeHomomorphism( $formlet, $fn1, $fn2, $value
                                               , $contains_applicable) {
        $left1 = _pure($fn1)->cmb(_pure($value));
        $right1 = _pure($fn1->apply($value));
        $this->assertFormletsEqual($left1, $right1, $value, false);
    }

    /**
     * Tests third applicative law: u <*> pure y = pure ($ y) <*> u
     * Applicative can be reversed when function application is reversed
     * two.
     * @dataProvider formlets_and_values
     */
    public function testApplicativeInterchange( $formlet, $fn1, $fn2, $value
                                              , $contains_applicable) {
        if (!$contains_applicable)
            return;
        $left = $formlet->cmb(_pure($value));
        $right = _pure(_application_to($value))->cmb($formlet);
        $this->assertFormletsEqual($left, $right, $value, false);
    }

    /**
     * Tests fourth applicative law: u <*> (v <*> w) = pure (.) <*> u <*> v <*> w 
     * @dataProvider formlets_and_values
     */
    public function testApplicativeComposition( $formlet, $fn1, $fn2, $value
                                              , $contains_applicable) {
     
        if (!$contains_applicable) {
            $left = _pure($fn1)
                        ->cmb( _pure($fn2)->cmb($formlet) ); 
            $right = _pure(_composition())
                        ->cmb(_pure($fn1))
                        ->cmb(_pure($fn2))
                        ->cmb($formlet)
                        ;
            $this->assertFormletsEqual($left, $right, $value, false);
        }
        else {
            $left = _pure($fn1)
                        ->cmb( $formlet->cmb(_pure($value)) ); 
            $right = _pure(_composition())
                        ->cmb(_pure($fn1))
                        ->cmb($formlet)
                        ->cmb(_pure($value))
                        ;
            $this->assertFormletsEqual($left, $right, $value, false);
        }

    }


    public function formlets_and_values() {
        $data = array();
        $pure_val = _pure(_val(42));
        $pure_fn = _pure(_id());
        $formlets = array
            ( array($pure_val, false)
            , array($pure_fn, true)
            , array(_text("TEXT")->cmb($pure_val), false)
            , array(_text("TEXT")->cmb($pure_fn), true)
            , array($pure_val->cmb(_text("TEXT")), false)
            , array($pure_fn->cmb(_text("TEXT")), true)
            , array($pure_fn->cmb(_input("text")), false)
            , array($pure_fn->cmb(_textarea_raw()), false)
            , array($pure_fn->cmb(_text_input()), false)
            , array($pure_fn->cmb(_textarea()), false)
            , array($pure_fn->cmb(_checkbox()), false)
            , array(_submit("SUBMIT")->cmb($pure_val), false)
            , array(_submit("SUBMIT")->cmb($pure_fn), true)
            , array($pure_val->cmb(_submit("SUBMIT")), false)
            , array($pure_fn->cmb(_submit("SUBMIT")), true)
            );
        $functions = array
            ( _id()
            );
        $values = array(0,1,2);

        foreach ($formlets as $formlet) {
            foreach ($functions as $fn1) {
                foreach ($functions as $fn2) {
                    foreach ($values as $value) {
                        $data[] = array( $formlet[0], $fn1, $fn2
                                       , _val($value), $formlet[1]
                                       );
                    }
                }
            }
        }
        return $data;
    }


    protected function assertFormletsEqual(Formlet $a, Formlet $b, $value
                                          , $contains_applicable) {
        // Two formlets are considered equal, when their observable output
        // is equal (that is like extensional equality?)
        $ns_a = NameSource::unsafeInstantiate();
        $ns_b = NameSource::unsafeInstantiate();
        $ns = NameSource::unsafeInstantiate();
        $repr_a = $a->instantiate($ns_a);
        $repr_b = $b->instantiate($ns_b);

        $name_and_ns = $ns->getNameAndNext();
        $inp = array($name_and_ns["name"] => "val");

        $val_a = $repr_a["collector"]->collect($inp);
        $val_b = $repr_b["collector"]->collect($inp);
        // This will only work if equal works as expected on the result, that 
        // is the thing checked really is equality and not identity.
        if (!$contains_applicable) {
            $this->assertEquals($val_a->get(), $val_b->get());
        }
        else {
            $this->assertEquals( $val_a->apply($value)->get()
                               , $val_b->apply($value)->get()
                               );
        }

        $dict_a = new RenderDict($inp, $val_a);
        $dict_b = new RenderDict($inp, $val_b);

        $rendered_a = $repr_a["builder"]->build()->render();
        $rendered_b = $repr_b["builder"]->build()->render();
        $this->assertEquals($rendered_a, $rendered_b);

        $rendered_a2 = $repr_a["builder"]->buildWithDict($dict_a)->render();
        $rendered_b2 = $repr_b["builder"]->buildWithDict($dict_b)->render();
        $this->assertEquals($rendered_a2, $rendered_b2);
    } 
        
}

?>
