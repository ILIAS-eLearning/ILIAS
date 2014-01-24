<?php
/* 
   +----------------------------------------------------------------------+
   | HTML/XML Validator                                                   |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004-2005 Nuno Lopes                                   |
   +----------------------------------------------------------------------+
   | This program is free software; you can redistribute it and/or        |
   | modify it under the terms of the GNU Lesser General Public           |
   | License as published by the Free Software Foundation; either         |
   | version 2.1 of the License, or (at your option) any later version.   |
   |                                                                      |
   | This program is distributed in the hope that it will be useful,      |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
   | Lesser General Public License for more details.                      |
   |                                                                      |
   | You should have received a copy of the GNU Lesser General Public     |
   | License along with this library; if not, write to the Free Software  |
   | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA             |
   | 02111-1307  USA.                                                     |
   +----------------------------------------------------------------------+
   |                     http://validator.aborla.net/                     |
   +----------------------------------------------------------------------+

     $Id$
*/

function common_header($title = '') {

    $title = $title ? ' :: ' . $title : '';

    echo <<< HEAD
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$GLOBALS['message'][22]}{$title}</title>
<link rel="stylesheet" href="../validator/style.css" type="text/css" />
</head>
<body>

<div class="header">
<h1>{$GLOBALS['message'][22]}</h1>
</div>

HEAD;

}


function common_footer() {
    @ob_end_flush(); // end rewrite of urls

    echo <<< FOOTER
<p>&nbsp;</p>
<div class="header">
<a href="/dev-zone.php">Dev Zone</a> |
<a href="http://www.php.net"><img src="php5.png" alt="PHP 5" /></a> |
<a href="http://tidy.sf.net">HTML Tidy</a>
</div>
</body></html>

FOOTER;

}

?>
