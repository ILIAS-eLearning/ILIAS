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
* Unit tests for tree table
* @group needsInstalledILIAS
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesTree
*/
class ilMembershipTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }
    
    /**
     * Waiting list tes
     * @group IL_Init
     * @param
     * @return
     */
    public function testMembership()
    {
        include_once './Services/Membership/classes/class.ilWaitingList.php';
        include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
        
        $wait = new ilCourseWaitingList(999999);
        $ret = $wait->addToList(111111);
        $this->assertEquals($ret, true);
        
        $wait->updateSubscriptionTime(111111, time());
        $wait->removeFromList(111111);
    
        $wait->addToList(111111);
        $ret = $wait->isOnList(111111);
        $this->assertEquals($ret, true);
        
        $wait->addToList(111111);
        ilWaitingList::_deleteAll(999999);
        
        $wait->addToList(111111);
        ilWaitingList::_deleteUser(111111);
    }
    
    /**
     * @group IL_Init
     * @param
     * @return
     */
    public function testSubscription()
    {
        include_once './Services/Membership/classes/class.ilParticipants.php';
        include_once './Modules/Course/classes/class.ilCourseParticipants.php';
        
        $part = ilCourseParticipants::_getInstanceByObjId(999999);
        $part->addSubscriber(111111);
        $part->updateSubscriptionTime(111111, time());
        $part->updateSubject(111111, 'hallo');
        
        $is = $part->isSubscriber(111111);
        $this->assertEquals($is, true);
        
        $is = ilParticipants::_isSubscriber(999999, 111111);
        $this->assertEquals($is, true);
        
        $part->deleteSubscriber(111111);
        $is = $part->isSubscriber(111111);
        $this->assertEquals($is, false);
    }
}
