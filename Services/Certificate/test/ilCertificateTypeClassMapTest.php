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
        $this->expectException(ilException::class);

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
