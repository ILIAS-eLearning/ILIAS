<?php
/*
+---------------------------------------------------------------+
/ classes/class.prevnextnavbar.php                              /
/                                                               /
/ Hilfsmodul-Klasse von ILIAS3                                  /
/ Funktion: siehe unten                                         /
/ ´                                                             /
+---------------------------------------------------------------+
*/

////////////////////////////////////////////////////////////////////////////////
// Name: class.prevnextnavbar.php
// Appl: ILIAS 3 RBAC
// Func: siehe unten
//
// (c) 2001 Sascha Hofmann
//
// Autor: Sascha Hofmann
//        Hohenstaufenring 23, 50674 K÷ln
//        +49-179-1305023
//        saschahofmann@gmx.de
//
// Last change: 17.Januar 2002
//
// Desc:
// Diese Klasse erzeugt einen typischen Navigationsbalken mit
// "Previous"- und "Next"-Links und den entsprechenden Seitenzahlen
// Ben÷tigte Parameter:
// $AScript  (string)  : Name der Skriptdatei (z.B. test.php)
// $AHits    (integer) : Anzahl der Elemente insgesamt
// $ALimit   (integer) : Anzahl der Elemente pro Seite
// $AOffset  (integer) : Das aktuelle erste Element in der Liste
// $AParams  (array)   : Die zu bergebenen Parameter in der Form (optional)
//                       $AParams["Varname"] = "Varwert"
//
// die komplette LinkBar wird zurckgegeben
// der Variablenname fr den offset ist "offset"
// Klasse wird nicht instanziert
////////////////////////////////////////////////////////////////////////////////

class TPrevNextNavBar {

    function TPrevNextNavBar ()
	{
		return true;
	}
		
	
	function Linkbar ($AScript,$AHits,$ALimit,$AOffset,$AParams = "")
    {
        $LinkBar = "";

        // Wenn Hits gr÷sser Limit, zeige Links an
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

            // šbergehe "zurck"-link, wenn offset 0 ist.
            if ($AOffset >= 1)
			{
                $prevoffset = $AOffset - $ALimit;
                $LinkBar .= "<a class=\"inlist\" href=\"".$link.$prevoffset."\">&lt;&lt;&lt;&nbsp;</a>";
            }

            // Ben÷tigte Seitenzahl kalkulieren
            $pages=intval($AHits/$ALimit);

            // Wenn ein Rest bleibt, addiere eine Seite
            if (($AHits % $ALimit))
				$pages++;

// Bei Offset = 0 keine Seitenzahlen anzeigen : DEAKTIVIERT
//            if ($AOffset != 0) {

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
//            }

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