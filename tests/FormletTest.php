<?php

trait FormletTestTrait {
    protected function instantiateFormlet($formlet) {
        return $formlet->instantiate(NameSource::unsafeInstantiate());
    }

    /**
     * Thing has correct class.
     * @dataProvider formlets
     */
    public function testHasFormletClass($formlet) {
        $this->assertInstanceOf("Formlet", $formlet);
    }
     
    /**
     * Builder has correct class.
     * @dataProvider formlets
     */
    public function testBuilderHasBuilderClass($formlet) { 
        $res = $this->instantiateFormlet($formlet);
        $this->assertInstanceOf("Builder", $res["builder"]);
    }

    /**
     * Collector has correct class.
     * @dataProvider formlets
     */
    public function testCollectorHasCollectorClass($formlet) { 
        $res = $this->instantiateFormlet($formlet);
        $this->assertInstanceOf("Collector", $res["collector"]);
    }

    /**
     * Name source has correct class.
     * @dataProvider formlets
     */
    public function testNameSourceHasNameSourceClass($formlet) {
        $res = $this->instantiateFormlet($formlet);
        $this->assertInstanceOf("NameSource", $res["name_source"]);
    }
}

?>
