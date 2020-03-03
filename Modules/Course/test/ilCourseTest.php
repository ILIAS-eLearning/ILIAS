<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * Class ilCourseTest
 * @group needsInstalledILIAS
 */
class ilCourseTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;
    protected $preserveGlobalState = false;

    protected function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }
    
    /**
     * Test member agreement
     * @group IL_Init
     */
    public function testMemberAgreement()
    {
        include_once 'Services/Membership/classes/class.ilMemberAgreement.php';
        
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        
        $agree = new ilMemberAgreement(9999, 8888);
        $agree->read();
        $agree->setAccepted(true);
        $agree->save();
        
        $agree = new ilMemberAgreement(9999, 8888);
        $agree->read();
        $sta = $agree->isAccepted();
        $this->assertEquals($sta, true);
        $agree->delete();
        
        $agree = new ilMemberAgreement(9999, 8888);
        $agree->read();
        $sta = $agree->isAccepted();
        $this->assertEquals($sta, false);
        
        $sta = ilMemberAgreement::_hasAccepted(9999, 8888);
        $this->assertEquals($sta, false);
        
        $agree = new ilMemberAgreement(9999, 8888);
        $agree->read();
        $agree->setAccepted(true);
        $agree->save();
        
        $sta = ilMemberAgreement::_hasAgreementsByObjId(8888);
        $this->assertEquals($sta, true);
        
        $sta = ilMemberAgreement::_hasAgreements();
        $this->assertEquals($sta, true);
        
        ilMemberAgreement::_deleteByUser(9999);
    }
}
