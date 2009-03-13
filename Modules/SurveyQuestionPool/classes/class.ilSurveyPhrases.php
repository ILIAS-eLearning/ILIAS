<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Survey phrases class
*
* The ilSurveyPhrases class manages survey phrases (collections of survey categories)
* for ordinal survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurveyQuestionPool
*/
class ilSurveyPhrases 
{
/**
* ilSurveyPhrases constructor
*
* The constructor takes possible arguments an creates an instance of the ilSurveyPhrases object.
*
* @access public
*/
  function ilSurveyPhrases()
  {
	}
	
/**
* Gets the available phrases from the database
*
* @param boolean $useronly Returns only the user defined phrases if set to true. The default is false.
* @result array All available phrases as key/value pairs
* @access public
*/
	function &_getAvailablePhrases($useronly = 0)
	{
		global $ilUser;
		global $ilDB;
		global $lng;
		
		$phrases = array();
		$result = $ilDB->queryF("SELECT * FROM survey_phrase WHERE defaultvalue = %s OR owner_fi = %s ORDER BY title",
			array('text', 'integer'),
			array('1', $ilUser->getId())
		);
		while ($row = $ilDB->fetchObject($result))
		{
			if (($row->defaultvalue == 1) and ($row->owner_fi == 0))
			{
				if (!$useronly)
				{
					$phrases[$row->phrase_id] = array(
						"title" => $lng->txt($row->title),
						"owner" => $row->owner_fi
					);
				}
			}
			else
			{
				if ($ilUser->getId() == $row->owner_fi)
				{
					$phrases[$row->phrase_id] = array(
						"title" => $row->title,
						"owner" => $row->owner_fi
					);
				}
			}
		}
		return $phrases;
	}
	
/**
* Gets the available categories for a given phrase
*
* @param integer $phrase_id The database id of the given phrase
* @result array All available categories
* @access public
*/
	function &_getCategoriesForPhrase($phrase_id)
	{
		global $ilDB;
		global $lng;
		
		$categories = array();
		$result = $ilDB->queryF("SELECT survey_category.* FROM survey_category, survey_phrase_category WHERE survey_phrase_category.category_fi = survey_category.category_id AND survey_phrase_category.phrase_fi = %s ORDER BY survey_phrase_category.sequence",
			array('integer'),
			array($phrase_id)
		);
		while ($row = $ilDB->fetchObject($result))
		{
			if (($row->defaultvalue == 1) and ($row->owner_fi == 0))
			{
				$categories[$row->category_id] = $lng->txt($row->title);
			}
			else
			{
				$categories[$row->category_id] = $row->title;
			}
		}
		return $categories;
	}
	
/**
* Delete phrases from the database
*
* @param array $phrase_array An array containing phrase id's to delete
* @access public
*/
	function deletePhrases($phrase_array)
	{
		global $ilDB;
		
		if ((is_array($phrase_array)) && (count($phrase_array)))
		{
			$affectedRows = $ilDB->manipulate("DELETE FROM survey_phrase WHERE " . $ilDB->in('phrase_id', $phrase_array, false, 'integer'));
			$affectedRows = $ilDB->manipulate("DELETE FROM survey_phrase_category WHERE " . $ilDB->in('phrase_fi', $phrase_array, false, 'integer'));
		}
	}

}
?>
