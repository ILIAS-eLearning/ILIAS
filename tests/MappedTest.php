<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\Formlet as F;
use Lechimp\Formlets\Internal\Values as V;
use Lechimp\Formlets\Internal\HTML as H;
use Lechimp\Formlets\Internal\NameSource;
use Lechimp\Formlets\Internal\RenderDict;

class MappedTest extends PHPUnit_Framework_TestCase {
    use FormletTestTrait;

    public function formlets() {
        $id = V::fn(function($a) { return $a; });
        $id2 = V::fn(function($_, $a) { return $a; });
        $pure = F::pure(V::val(42));
        return array
            ( array($pure->map($id))
            , array($pure->mapHTML($id2))
            , array($pure->mapBC($id, $id))
            );
    }

    public function testMappedOnce() {
        $d = new RenderDict(array("foo" => "bar"), V::val(0));
        $n = NameSource::instantiate("test");
        $f = F::text("foobar");

        $rdict = null;
        $rhtml = null; 

        $transformation = V::fn(function ($dict, $html) use (&$rdict, &$rhtml) {
            $rdict = $dict;
            $rhtml = $html;
            return H::nop();
        });

        $f2 = $f->mapHTML($transformation);

        $i = $f2->instantiate($n);

        $r1 = $i["builder"]->build();
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $rhtml); 
        $this->assertEquals($rhtml->render(), "foobar");
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $r1); 

        $r2 = $i["builder"]->buildWithDict($d);
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict); 
        $this->assertEquals($d, $rdict); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $rhtml); 
        $this->assertEquals($rhtml->render(), "foobar");
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $r2); 
    }

    public function testMappedTwice() {
        $d = new RenderDict(array("foo" => "bar"), V::val(0));
        $n = NameSource::instantiate("test");
        $f = F::text("foobar");

        $rdict1 = null;
        $rhtml1 = null; 
        $rdict2 = null;
        $rhtml2 = null; 

        $transformation = V::fn(function ($dict, $html) use (&$rdict1, &$rhtml1) {
            $rdict1 = $dict;
            $rhtml1 = $html;
            return H::nop();
        });

        $transformation2 = V::fn(function ($dict, $html) use (&$rdict2, &$rhtml2) {
            $rdict2 = $dict;
            $rhtml2 = $html;
            return H::text("baz");
        });

        $f2 = $f->mapHTML($transformation)->mapHTML($transformation2);

        $i = $f2->instantiate($n);

        $r1 = $i["builder"]->build();
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict2); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $rhtml1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $rhtml2); 
        $this->assertEquals($rhtml1->render(), "foobar");
        $this->assertEquals($rhtml2->render(), "");
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $r1); 
        $this->assertEquals($r1->render(), "baz"); 

        $r2 = $i["builder"]->buildWithDict($d);
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict2); 
        $this->assertEquals($d, $rdict1); 
        $this->assertEquals($d, $rdict2); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $rhtml1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $rhtml2); 
        $this->assertEquals($rhtml1->render(), "foobar");
        $this->assertEquals($rhtml2->render(), "");
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $r2);
        $this->assertEquals($r2->render(), "baz");
    }
}

?>
