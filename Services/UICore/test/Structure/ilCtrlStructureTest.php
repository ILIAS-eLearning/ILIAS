<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlStructureTest
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureTest extends TestCase
{
    public function testStructureBaseClasses(): void
    {
        $structure = new ilCtrlStructure([
            'baseclass1' => [ilCtrlStructureInterface::KEY_CLASS_CID => 'cid1'],
            'baseclass2' => [ilCtrlStructureInterface::KEY_CLASS_CID => 'cid2'],
            'baseclass3' => [ilCtrlStructureInterface::KEY_CLASS_CID => 'cid3'],
        ], [
            'baseclass1',
            'baseclass2',
            'baseclass3',
        ], []);

        $this->assertTrue($structure->isBaseClass('baseclass1'));
        $this->assertTrue($structure->isBaseClass('baseclass2'));
        $this->assertTrue($structure->isBaseClass('baseclass3'));
        $this->assertFalse($structure->isBaseClass('baseclass4'));
        $this->assertFalse($structure->isBaseClass(''));
    }

    public function testStructureSecurityInfos(): void
    {
        $structure = new ilCtrlStructure([
            'cmdclass1' => [
                ilCtrlStructureInterface::KEY_CLASS_CID => 'cid1',
                ilCtrlStructureInterface::KEY_CLASS_NAME => 'cmdClass1',
            ],
            'cmdclass2' => [
                ilCtrlStructureInterface::KEY_CLASS_CID => 'cid2',
                ilCtrlStructureInterface::KEY_CLASS_NAME => 'cmdClass2',
            ],
        ], [], [
            'cmdclass1' => [
                ilCtrlStructureInterface::KEY_UNSAFE_COMMANDS => [],
                ilCtrlStructureInterface::KEY_SAFE_COMMANDS => [
                    'postCmd1',
                ],
            ],
            'cmdclass2' => [
                ilCtrlStructureInterface::KEY_UNSAFE_COMMANDS => [
                    'getCmd1',
                ],
                ilCtrlStructureInterface::KEY_SAFE_COMMANDS => [],
            ],
        ]);

        $this->assertEmpty($structure->getUnsafeCommandsByCid('cid1'));
        $this->assertEmpty($structure->getUnsafeCommandsByName('cmdClass1'));

        $this->assertEquals(['postCmd1'], $structure->getSafeCommandsByCid('cid1'));
        $this->assertEquals(['postCmd1'], $structure->getSafeCommandsByName('cmdClass1'));

        $this->assertEquals(['getCmd1'], $structure->getUnsafeCommandsByCid('cid2'));
        $this->assertEquals(['getCmd1'], $structure->getUnsafeCommandsByName('cmdClass2'));

        $this->assertEmpty($structure->getSafeCommandsByCid('cid2'));
        $this->assertEmpty($structure->getSafeCommandsByName('cmdClass2'));

        $this->assertEmpty($structure->getUnsafeCommandsByName(''));
        $this->assertEmpty($structure->getUnsafeCommandsByCid(''));

        $this->assertEmpty($structure->getSafeCommandsByCid(''));
        $this->assertEmpty($structure->getSafeCommandsByName(''));
    }

    public function testStructureCtrlInfos(): void
    {
        $structure = new ilCtrlStructure([
            'class1' => [
                ilCtrlStructureInterface::KEY_CLASS_CID => 'cid1',
                ilCtrlStructureInterface::KEY_CLASS_NAME => 'Class1',
                ilCtrlStructureInterface::KEY_CLASS_PATH => './path/1',
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [],
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                    'class2',
                ],
            ],
            'class2' => [
                ilCtrlStructureInterface::KEY_CLASS_CID => 'cid2',
                ilCtrlStructureInterface::KEY_CLASS_NAME => 'Class2',
                ilCtrlStructureInterface::KEY_CLASS_PATH => './path/2',
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                    'class1',
                ],
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
            ],
        ], [], []);

        $this->assertEquals('Class1', $structure->getObjNameByCid('cid1'));
        $this->assertEquals('Class1', $structure->getObjNameByName('Class1'));
        $this->assertEquals('Class2', $structure->getObjNameByCid('cid2'));
        $this->assertEquals('Class2', $structure->getObjNameByName('Class2'));
        $this->assertNull($structure->getObjNameByCid('cid3'));
        $this->assertNull($structure->getObjNameByName('Class3'));

        $this->assertEquals('class1', $structure->getClassNameByCid('cid1'));
        $this->assertEquals('cid1', $structure->getClassCidByName('Class1'));
        $this->assertEquals('class2', $structure->getClassNameByCid('cid2'));
        $this->assertEquals('cid2', $structure->getClassCidByName('Class2'));
        $this->assertNull($structure->getClassNameByCid('cid3'));
        $this->assertNull($structure->getClassCidByName('Class3'));

        $this->assertEquals('./path/1', $structure->getRelativePathByCid('cid1'));
        $this->assertEquals('./path/1', $structure->getRelativePathByName('Class1'));
        $this->assertEquals('./path/2', $structure->getRelativePathByCid('cid2'));
        $this->assertEquals('./path/2', $structure->getRelativePathByName('Class2'));
        $this->assertNull($structure->getRelativePathByCid('cid3'));
        $this->assertNull($structure->getRelativePathByName('Class3'));

        $this->assertEquals(['class2'], $structure->getChildrenByCid('cid1'));
        $this->assertEquals(['class2'], $structure->getChildrenByName('Class1'));
        $this->assertNull($structure->getChildrenByCid('cid2'));
        $this->assertNull($structure->getChildrenByName('Class2'));
        $this->assertNull($structure->getChildrenByCid('cid3'));
        $this->assertNull($structure->getChildrenByName('Class3'));

        $this->assertNull($structure->getParentsByCid('cid1'));
        $this->assertNull($structure->getParentsByName('Class1'));
        $this->assertEquals(['class1'], $structure->getParentsByCid('cid2'));
        $this->assertEquals(['class1'], $structure->getParentsByName('Class2'));
        $this->assertNull($structure->getParentsByCid('cid3'));
        $this->assertNull($structure->getParentsByName('Class3'));
    }

    public function testStructureSavedParameters(): void
    {
        $structure = new ilCtrlStructure([], [], []);

        $structure->setPermanentParameterByClass('Class1', 'param1');
        $structure->setPermanentParameterByClass('Class1', 'param2');
        $structure->setPermanentParameterByClass('Class2', 'param1');

        // parameters must be as set above
        $this->assertEquals(['param1', 'param2'], $structure->getPermanentParametersByClass('Class1'));
        $this->assertEquals(['param1'], $structure->getPermanentParametersByClass('Class2'));

        $structure->removeSingleParameterByClass('Class1', 'param1');

        // parameter 'param1' must no longer be contained and not
        // affect parameters of 'Class2'.
        $this->assertEquals(['param2'], $structure->getPermanentParametersByClass('Class1'));
        $this->assertEquals(['param1'], $structure->getPermanentParametersByClass('Class2'));

        $structure->removePermanentParametersByClass('Class1');

        // no parameters must be contained anymore for 'Class1',
        // but not affect parameters of 'Class2'
        $this->assertNull($structure->getPermanentParametersByClass('Class1'));
        $this->assertEquals(['param1'], $structure->getPermanentParametersByClass('Class2'));

        // parameter must not be set, it contains invalid characters.
        $parameter_name = '$param';
        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Cannot save parameter '$parameter_name', as it contains invalid characters.");
        $structure->setPermanentParameterByClass('Class2', $parameter_name);
    }

    /**
     * @dataProvider getProtectedParameters
     */
    public function testStructureSavedParametersWithProtectedKey($protected_parameter): void
    {
        $structure = new ilCtrlStructure([], [], []);
        $this->expectException(ilCtrlException::class);
        $structure->setPermanentParameterByClass('a_class', $protected_parameter);
    }

    public function testStructureTemporaryParameters(): void
    {
        $structure = new ilCtrlStructure([], [], []);

        $structure->setTemporaryParameterByClass('Class1', 'param1', 1);
        $structure->setTemporaryParameterByClass('Class1', 'param2', '2');
        $structure->setTemporaryParameterByClass('Class2', 'param1', true);

        // parameters must be as set above
        $this->assertEquals(['param1' => true], $structure->getTemporaryParametersByClass('Class2'));
        $this->assertEquals(
            ['param1' => 1, 'param2' => '2'],
            $structure->getTemporaryParametersByClass('Class1')
        );

        $structure->removeSingleParameterByClass('Class1', 'param1');

        // parameter 'param1' must no longer be contained and not
        // affect parameters of 'Class2'.
        $this->assertEquals(['param1' => true], $structure->getTemporaryParametersByClass('Class2'));
        $this->assertEquals(
            ['param2' => '2'],
            $structure->getTemporaryParametersByClass('Class1')
        );

        $structure->removeTemporaryParametersByClass('Class1');

        // no parameters must be contained anymore for 'Class1',
        // but not affect parameters of 'Class2'
        $this->assertEquals(['param1' => true], $structure->getTemporaryParametersByClass('Class2'));
        $this->assertNull($structure->getTemporaryParametersByClass('Class1'));

        // parameter must not be set, it contains invalid characters.
        $parameter_name = '$param';
        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Cannot save parameter '$parameter_name', as it contains invalid characters.");
        $structure->setTemporaryParameterByClass('Class3', $parameter_name, 0);
    }

    public function testStructureReturnTargets(): void
    {
        $structure = new ilCtrlStructure([], [], []);

        $test_url = 'https://domain.com/test/url';
        $structure->setReturnTargetByClass('Class1', $test_url);
        $this->assertEquals($test_url, $structure->getReturnTargetByClass('Class1'));
        $this->assertNull($structure->getReturnTargetByClass('Class2'));
    }

    public function getProtectedParameters(): array
    {
        return [
            ['baseClass'],
            ['cmdClass'],
            ['cmdNode'],
            ['rtoken'],
        ];
    }
}
