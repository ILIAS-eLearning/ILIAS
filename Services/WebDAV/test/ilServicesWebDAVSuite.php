<?php declare(strict_types=1);
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

use PHPUnit\Framework\TestSuite;

require_once __DIR__ . "/../../../libs/composer/vendor/autoload.php";

/**
 * @author Stephan Winiker <stephan.winiker@hslu.ch>
 * @version 1.0.0
 */
class ilServicesWebDAVSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesWebDAVSuite();
        
        require_once "./Services/WebDAV/test/traits/ilWebDAVCheckValidTitleTraitTest.php";
        $suite->addTestSuite("ilWebDAVCheckValidTitleTraitTest");
        
        require_once "./Services/WebDAV/test/lock/ilWebDAVLockUriPathResolverTest.php";
        $suite->addTestSuite("ilWebDAVLockUriPathResolverTest");
        
        require_once "./Services/WebDAV/test/dav/class.ilDAVContainerTest.php";
        $suite->addTestSuite("ilDAVContainerTest");
        
        require_once "./Services/WebDAV/test/dav/class.ilDAVClientNodeTest.php";
        $suite->addTestSuite("ilDAVClientNodeTest");

        return $suite;
    }
}
