<?php

use ILIAS\BackgroundTasks\Types\ListType;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\TupleType;
use PHPUnit\Framework\TestCase;

require_once("libs/composer/vendor/autoload.php");
require_once("./Services/User/classes/class.ilObjUser.php");

/**
 * Class BackgroundTaskTest
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class TypeTest extends TestCase
{
    public function testAncestors()
    {
        $integer = new SingleType(\ilObjUser::class);
        $ancestors = $integer->getAncestors();

        $this->assertTrue($ancestors[0]->equals(new SingleType(\ilObject::class)));
        $this->assertTrue($ancestors[1]->equals(new SingleType(\ilObjUser::class)));
    }

    public function testListSubtypes()
    {
        $scalarList = new ListType(new SingleType(\ilObjUser::class));
        $scalarList3 = new ListType(new SingleType(\ilObjUser::class));

        $this->assertTrue($scalarList3->isExtensionOf($scalarList));
        $this->assertTrue($scalarList->isExtensionOf($scalarList));
    }

    public function testListAncestor()
    {
        $integerList = new ListType(new SingleType(\ilObjUser::class));
        $ancestors = $integerList->getAncestors();

        $this->assertTrue($ancestors[0]->equals(new ListType(\ilObject::class)));
        $this->assertTrue($ancestors[1]->equals(new ListType(\ilObjUser::class)));
    }

    public function testListOfLists()
    {
        $list = new ListType(\ilObjUser::class);
        $list1 = new ListType(\ilObject::class);
        $listlist = new ListType($list);
        $listlist1 = new ListType($list1);

        $this->assertTrue($listlist->equals(new ListType(new ListType(\ilObjUser::class))));
        $this->assertTrue($listlist->isExtensionOf($listlist1));
        $this->assertFalse($listlist1->isExtensionOf($listlist));
    }

    public function testTuple()
    {
        $tuple1 = new TupleType([\ilObjUser::class, new ListType(\ilObject::class)]);
        $tuple2 = new TupleType([\ilObjUser::class, new ListType(\ilObjUser::class)]);

        $this->assertEquals($tuple1->__toString(), '(ilObjUser, [ilObject])');
        $this->assertTrue($tuple2->isExtensionOf($tuple1));
        $this->assertFalse($tuple1->isExtensionOf($tuple2));
    }
}
