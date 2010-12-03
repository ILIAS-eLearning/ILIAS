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
* Analyzes external media locations and extracts important information
* into parameter field.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilExternalMediaAnalyzer
{
	/**
	* Identify YouTube links
	*/
	static function isYouTube($a_location)
	{
		if (strpos($a_location, "youtube.com") > 0)
		{
			return true;
		}
		return false;
	}
	
	/**
	* Extract YouTube Parameter
	*/
	static function extractYouTubeParameters($a_location)
	{
		$par = array();
		$pos1 = strpos($a_location, "v=");
		$pos2 = strpos($a_location, "&", $pos1);
		if ($pos1 > 0)
		{
			$len = ($pos2 > 0)
				? $pos2
				: strlen($a_location);
			$par["v"] = substr($a_location, $pos1+2, $len - ($pos1+2));
		}

		return $par;
	}

	/**
	* Identify Flickr links
	*/
	static function isFlickr($a_location)
	{
		if (strpos($a_location, "flickr.com") > 0)
		{
			return true;
		}
		return false;
	}

	/**
	* Extract Flickr Parameter
	*/
	static function extractFlickrParameters($a_location)
	{
		$par = array();
		$pos1 = strpos($a_location, "flickr.com/photos/");
		$pos2 = strpos($a_location, "/", $pos1+18);
		if ($pos1 > 0)
		{
			$len = ($pos2 > 0)
				? $pos2
				: $a_location;
			$par["user_id"] = substr($a_location, $pos1+18, $len - ($pos1+18));
		}
		
		// tags
		$pos1 = strpos($a_location, "/tags/");
		$pos2 = strpos($a_location, "/", $pos1+6);
		if ($pos1 > 0)
		{
			$len = ($pos2 > 0)
				? $pos2
				: strlen($a_location);
			$par["tags"] = substr($a_location, $pos1+6, $len - ($pos1+6));
		}

		// sets
		$pos1 = strpos($a_location, "/sets/");
		$pos2 = strpos($a_location, "/", $pos1+6);
		if ($pos1 > 0)
		{
			$len = ($pos2 > 0)
				? $pos2
				: $a_location;
			$par["sets"] = substr($a_location, $pos1+6, $len - ($pos1+6));
		}

		return $par;
	}

	/**
	* Identify GoogleVideo links
	*/
	static function isGoogleVideo($a_location)
	{
		if (strpos($a_location, "video.google") > 0)
		{
			return true;
		}
		return false;
	}
	
	/**
	* Extract GoogleVideo Parameter
	*/
	static function extractGoogleVideoParameters($a_location)
	{
		$par = array();
		$pos1 = strpos($a_location, "docid=");
		$pos2 = strpos($a_location, "&", $pos1 + 6);
		if ($pos1 > 0)
		{
			$len = ($pos2 > 0)
				? $pos2
				: strlen($a_location);
			$par["docid"] = substr($a_location, $pos1+6, $len - ($pos1+6));
		}

		return $par;
	}

	/**
	 * Identify Vimeo links
	 */
	static function isVimeo($a_location)
	{
		if (strpos($a_location, "vimeo.com") > 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * Extract Vimeo Parameter
	 */
	static function extractVimeoParameters($a_location)
	{
		$par = array();
		$pos1 = strpos($a_location, "vimeo.com/");
		$pos2 = strpos($a_location, "&", $pos1 + 10);
		if ($pos1 > 0)
		{
			$len = ($pos2 > 0)
				? $pos2
				: strlen($a_location);
			$par["id"] = substr($a_location, $pos1+10, $len - ($pos1+10));
		}

		return $par;
	}

	/**
	* Identify Google Document links
	*/
	static function isGoogleDocument($a_location)
	{
		if (strpos($a_location, "docs.google") > 0)
		{
			return true;
		}
		return false;
	}
	
	/**
	* Extract GoogleDocument Parameter
	*/
	static function extractGoogleDocumentParameters($a_location)
	{
		$par = array();
		$pos1 = strpos($a_location, "id=");
		$pos2 = strpos($a_location, "&", $pos1 + 3);
		if ($pos1 > 0)
		{
			$len = ($pos2 > 0)
				? $pos2
				: strlen($a_location);
			$par["docid"] = substr($a_location, $pos1+3, $len - ($pos1+3));
		}
		$pos1 = strpos($a_location, "docID=");
		$pos2 = strpos($a_location, "&", $pos1 + 6);
		if ($pos1 > 0)
		{
			$len = ($pos2 > 0)
				? $pos2
				: strlen($a_location);
			$par["docid"] = substr($a_location, $pos1+6, $len - ($pos1+6));
		}
		if (strpos($a_location, "Presentation?") > 0)
		{
			$par["type"] = "Presentation";
		}
		if (strpos($a_location, "View?") > 0)
		{
			$par["type"] = "Document";
		}

		return $par;
	}
	
	/**
	* Extract URL information to parameter array
	*/
	static function extractUrlParameters($a_location, $a_parameter)
	{
		if (!is_array($a_parameter))
		{
			$a_parameter = array();
		}
		
		$ext_par = array();
		
		// YouTube
		if (ilExternalMediaAnalyzer::isYouTube($a_location))
		{
			$ext_par = ilExternalMediaAnalyzer::extractYouTubeParameters($a_location);
			$a_parameter = array();
		}

		// Flickr
		if (ilExternalMediaAnalyzer::isFlickr($a_location))
		{
			$ext_par = ilExternalMediaAnalyzer::extractFlickrParameters($a_location);
			$a_parameter = array();
		}

		// GoogleVideo
		if (ilExternalMediaAnalyzer::isGoogleVideo($a_location))
		{
			$ext_par = ilExternalMediaAnalyzer::extractGoogleVideoParameters($a_location);
			$a_parameter = array();
		}

		// GoogleDocs
		if (ilExternalMediaAnalyzer::isGoogleDocument($a_location))
		{
			$ext_par = ilExternalMediaAnalyzer::extractGoogleDocumentParameters($a_location);
			$a_parameter = array();
		}

		foreach($ext_par as $name => $value)
		{
			$a_parameter[$name] = $value;
		}

		return $a_parameter;
	}
}
?>
