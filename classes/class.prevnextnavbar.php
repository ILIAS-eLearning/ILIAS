<?php
/**
* classes/class.prevnextnavbar.php
* Diese Klasse erzeugt einen typischen Navigationsbalken mit
* "Previous"- und "Next"-Links und den entsprechenden Seitenzahlen
*
* die komplette LinkBar wird zurückgegeben
* der Variablenname für den offset ist "offset"
* Klasse wird nicht instanziert
* 
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
* 
* @package ilias-core
*/
class TPrevNextNavBar 
{
	/**
	* Constructor
	* @return boolean 
	*/
	function TPrevNextNavBar ()
	{
		return true;
	}
		
	/**
	* Linkbar
	* @access	public
	* @param	integer		Name der Skriptdatei (z.B. test.php)
	* @param	integer		Anzahl der Elemente insgesamt
	* @param	integer		Anzahl der Elemente pro Seite
	* @param	integer		Das aktuelle erste Element in der Liste
	* @param	array		Die zu übergebenen Parameter in der Form $AParams["Varname"] = "Varwert" (optional)
	* @return	array		linkbar or false on error
	*/
	function Linkbar ($AScript,$AHits,$ALimit,$AOffset,$AParams = array())
	{
		$LinkBar = "";

		// Wenn Hits grösser Limit, zeige Links an
		if ($AHits > $ALimit)
		{
			if (!empty($AParams))
			{
				foreach ($AParams as $key => $value)
				{
					$params.= $key."=".$value."&";
				}
			}
			// if ($params) $params = substr($params,0,-1);
			$link = $AScript."?".$params."offset=";

			// übergehe "zurck"-link, wenn offset 0 ist.
			if ($AOffset >= 1)
			{
				$prevoffset = $AOffset - $ALimit;
				$LinkBar .= "<a class=\"inlist\" href=\"".$link.$prevoffset."\">&lt;&lt;&lt;&nbsp;</a>";
			}

			// Benötigte Seitenzahl kalkulieren
			$pages=intval($AHits/$ALimit);

			// Wenn ein Rest bleibt, addiere eine Seite
			if (($AHits % $ALimit))
				$pages++;

// Bei Offset = 0 keine Seitenzahlen anzeigen : DEAKTIVIERT
//			if ($AOffset != 0) {

				// ansonsten zeige Links zu den anderen Seiten an
				for ($i = 1 ;$i <= $pages ; $i++)
				{
					$newoffset=$ALimit*($i-1);
					
					if ($newoffset == $AOffset)
					{
						$LinkBar .= "&nbsp;".$i."&nbsp;";
					}
					else
					{
						$LinkBar .= "[<a class=\"inlist\" href=\"".$link.$newoffset."\">$i</a>]";
					}
				}
//			}

			// Checken, ob letze Seite erreicht ist
			// Wenn nicht, gebe einen "Weiter"-Link aus
			if (! ( ($AOffset/$ALimit)==($pages-1) ) && ($pages!=1) )
			{
				$newoffset=$AOffset+$ALimit;
				$LinkBar .= "<a class=\"inlist\" href=\"".$link.$newoffset."\">&nbsp;&gt;&gt;&gt;</a>";
			}

			return $LinkBar;
		}
		else
		{
			return false;
		}
    }
} // class TPrevNextNavBar ENDE

?>