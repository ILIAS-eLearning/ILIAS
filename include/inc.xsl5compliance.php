<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2004 ILIAS open source, University of Cologne            |
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

function xslt_create()
{
	return new php4XSLTProcessor();
}


class php4XSLTProcessor
{
	var $myProc;

	function php4XSLTProcessor()
	{
		$this->myProc =& new XSLTProcessor;
	}
}

function xslt_process(&$proc, $xml_var, $xslt_var, $dummy, $args, $params,
	$a_no_warnings = false)
{
	$xslt = $proc->myProc;
//echo htmlentities($args[substr($xslt_var, 4)]);
	$xslt_domdoc = new DomDocument();
	$xslt_domdoc->loadXML($args[substr($xslt_var, 4)]);
	$xslt->importStyleSheet($xslt_domdoc);
	if (is_array($params))
	{
		foreach ($params as $key => $value)
		{
			$xslt->setParameter("", $key, $value);
		}
	}

	// supress warnings all the time. (some lib xslt bring warnings due to & in links)
	//if ($a_no_warnings)
	//{
		$xml_domdoc = new DomDocument();
		$xml_domdoc->loadXML($args[substr($xml_var, 4)]);
		// show warnings again due to discussion in #12866
		$result = $xslt->transformToXML($xml_domdoc);
	//}
	//else
	//{
	//	$result = $xslt->transformToXML(DomDocument::loadXML($args[substr($xml_var, 4)]));
	//}

//echo "<br><br><b>xslt_process</b>".htmlentities($result);
	return $result;
}

function xslt_free(&$proc)
{
	unset($proc->myProc);
	unset($proc);
}

function xslt_error(&$proc)
{
}

?>
