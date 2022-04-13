<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/Certificate/classes/Cron/class.ilCertificateTypeClassMap.php';
require_once 'Services/Exceptions/classes/class.ilException.php';

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTypeClassMapTest extends ilCertificateBaseTestCase
{
    private ilCertificateTypeClassMap $classMap;

    protected function setUp() : void
    {
        $this->classMap = new ilCertificateTypeClassMap();
    }

    public function testFetchCoursePlaceHolderClass() : void
    {
        $class = $this->classMap->getPlaceHolderClassNameByType('crs');

        $this->assertSame(ilCoursePlaceholderValues::class, $class);
    }

    public function testFetchTestPlaceHolderClass() : void
    {
        $class = $this->classMap->getPlaceHolderClassNameByType('tst');

        $this->assertSame(ilTestPlaceholderValues::class, $class);
    }

    public function testFetchExercisePlaceHolderClass() : void
    {
        $class = $this->classMap->getPlaceHolderClassNameByType('exc');

        $this->assertSame(ilExercisePlaceholderValues::class, $class);
    }

    public function testFetchScormPlaceHolderClass() : void
    {
        $class = $this->classMap->getPlaceHolderClassNameByType('sahs');

        $this->assertSame(ilScormPlaceholderValues::class, $class);
    }

    public function testFetchUnknownClassWillResultInException() : void
    {
        $this->expectException(\ilException::class);

        $class = $this->classMap->getPlaceHolderClassNameByType('something');

        $this->fail('Should never happen. No Exception thrown?');
    }

    public function testIsCourseExisting() : void
    {
        $result = $this->classMap->typeExistsInMap('crs');

        $this->assertTrue($result);
    }

    public function testIsTestExisting() : void
    {
        $result = $this->classMap->typeExistsInMap('tst');

        $this->assertTrue($result);
    }

    public function testIsExerciseExisting() : void
    {
        $result = $this->classMap->typeExistsInMap('exc');

        $this->assertTrue($result);
    }

    public function testUnknownTypeIsNotExisting() : void
    {
        $result = $this->classMap->typeExistsInMap('something');

        $this->assertFalse($result);
    }
}
