<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* user view of courses
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias-layout
*/
require_once "./include/inc.header.php";

$tpl = new ilTemplate("tpl.courses.html", true, true);

$tplbtn = new ilTemplate("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","lo_content.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("los"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("TXT_PAGEHEADLINE",$lng->txt("crs_available"));
$tpl->setVariable("BUTTONS",$tplbtn->get());

for ($i = 0; $i<2; $i++)
{
        for ($j = 0; $j<3; $j++)
        {
                $tpl->setCurrentBlock("subcategory");
                $tpl->setVariable("ROWCOL","tblrow".(($j%2)+1));
                $tpl->setVariable("TITLE","title".$j);
                $tpl->setVariable("IMG_AND_LINK","img".$j);
                $tpl->parseCurrentBlock();
        }
        $tpl->touchBlock("subcategory_others");
        $tpl->setCurrentBlock("category");
        $tpl->setVariable("CAT_TITLE","CATEGORY".$i);
        $tpl->parseCurrentBlock();

}

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>
