<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Helper class for user agreement
*  
* @author	Alex Killing <alex.killing@gmx.de>
* @version $Id$
* 
*/
class ilUserAgreement
{
	/**
	* ilias object
	* @var object Ilias
	* @access public
	*/
	var $ilias;


	/**
	* Constructor
	* @access	public
	*/
	function ilUserAgreement()
	{
		global $ilias;

		$this->ilias = &$ilias;
	}

	/**
	* get user agreement text  (static)
	*
	* @access	public
	*/
	function _getText()
	{
		global $lng, $ilias, $ilLog;
	
		
		// 1st try: client specific / user language agreement
		$agreement = "./Customizing/clients/".CLIENT_ID."/agreement/".
			"agreement_".$lng->lang_key.".html";
	
		// 2nd try: client specific / default language
		if (!file_exists($agreement))
		{
			$agreement = "./Customizing/clients/".CLIENT_ID."/agreement/".
				"agreement_".$lng->lang_default.".html";
		}
	
		// 3rd try: client specific / english
		if (!file_exists($agreement))
		{
			$agreement = "./Customizing/clients/".CLIENT_ID."/agreement/".
				"agreement_en.html";
		}
		
		// 4th try: global / user language
		if (!file_exists($agreement))
		{
			$agreement = "./Customizing/global/agreement/".
				"agreement_".$lng->lang_key.".html";
		}
	
		// 5th try: global / default language
		if (!file_exists($agreement))
		{
			$agreement = "./Customizing/global/agreement/".
				"agreement_".$lng->lang_default.".html";
		}
	
		// last try: global / english
		if (!file_exists($agreement))
		{
			$ilLog->write("view_usr_agreement.php: Agreement file "."agreement_".$lng->lang_default.".html"." has not been found (system language).");
			$agreement = "./Customizing/global/agreement/".
				"agreement_en.html";
		}
		
		if (file_exists($agreement))
		{
			if ($content = file($agreement))
			{
				foreach ($content as $key => $val)
				{
					$text .= trim(nl2br($val));
				}
				return $text;
			}
		}
		
		return "<br />".$lng->txt("no_agreement_description")."<br /><br />";
	}
}
?>