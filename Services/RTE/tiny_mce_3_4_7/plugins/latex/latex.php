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

while( !file_exists('./ilias.ini.php') ) chdir('..');

require_once 'include/inc.get_pear.php';

include "Services/Init/classes/class.ilIniFile.php";
$ini = new ilIniFile("ilias.ini.php");
$ini->read();
$latex_converter = $ini->readVariable("tools", "latex");

require_once "HTML/Template/ITX.php";
$tpl = new HTML_Template_ITX();
$tpl->loadTemplatefile(dirname(__FILE__)."/tpl.latex.html", TRUE, TRUE);
$tpl->setVariable("LATEX_CODE", "");
$tpl->setVariable("URL_PREVIEW", $latex_converter);
$tpl->setVariable("HREF_LATEX_CONVERTER", $latex_converter);
$tpl->show();


?>
