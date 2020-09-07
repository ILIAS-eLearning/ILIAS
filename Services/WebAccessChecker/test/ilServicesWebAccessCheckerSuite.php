<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
 * ilServicesWebAccessCheckerSuite
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilServicesWebAccessCheckerSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * @return \ilServicesWebAccessCheckerSuite
     */
    public static function suite()
    {
        $suite = new self();

        require_once('./Services/WebAccessChecker/test/Token/ilWACTokenTest.php');
        $suite->addTestSuite('ilWACTokenTest');

        require_once('./Services/WebAccessChecker/test/CheckingInstance/ilWACCheckingInstanceTest.php');
        $suite->addTestSuite('ilWACCheckingInstanceTest');

        require_once('./Services/WebAccessChecker/test/Path/ilWACPathTest.php');
        $suite->addTestSuite('ilWACPathTest');

        return $suite;
    }
}
