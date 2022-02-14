<?php
declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Class ilScorm2004SeqTest
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilScorm2004SeqTest extends TestCase
{
    protected function setUp() : void
    {
        global $DIC;

        $DIC = new Container();
        $DIC['ilias'] = null; // not used just added received
        $DIC['ilDB'] = $this->getMockBuilder(\ilDBInterface::class)->getMock();
    }

//    public function testSeqNodeConditions() : void
//    {
//        $seqNode = new ilSCORM2004SeqNode();
//        $seqNode->setNodeName('condition');
//        $condition = new ilSCORM2004Condition();
//
//        $this->assertEquals($seqNode->getNodeName(), $condition->getNodeName());
//    }
}
