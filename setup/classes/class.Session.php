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

// Datei:       class.Session.inc
// Benoetigt:    mind. 4.0.1pl2

/**
*   "Manueller" Session-Fallback mit PHP4
*
*   @author     Daniel T. Gorski <daniel.gorski@bluemars.de>
*/

class Session
{
    public $version = 106;     // V1.06
    public $usesCookies = false;   // Client nimmt Cookies an
    public $transSID = false;   // Wurde mit --enable-trans-sid
                                // kompiliert

//---------------------------------------------------------

    /**
    *   Konstruktor - nimmt, wenn gewuenscht einen neuen
    *   Session-Namen entgegen
    */
    public function __construct($sessionName = "SESSID")
    {
        $this->sendNoCacheHeader();
        
        ini_set("session.cookie_httponly", 1);
        if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
            ini_set("session.sid_length", "32");
        } else {
            // force 4 hash bits per character for session_id	// Sascha Hofmann (2005-10-19)
            ini_set("session.hash_bits_per_character", "4");
        }

        //  Session-Namen setzen, Session initialisieren
        session_name(isset($sessionName)
            ? $sessionName
            : session_name());

        @session_start();
        
        //  Prüen ob die Session-ID die Standardlänge
        //  von 32 Zeichen hat,
        //  ansonsten Session-ID neu setzen
        if (strlen(session_id()) < 32) {
            mt_srand((double) microtime() * 1000000);
            session_id(md5(uniqid(mt_rand())));
        }
        
        //  Prüfen, ob eine Session-ID übergeben wurde
        //  (über Cookie, POST oder GET)
        $IDpassed = false;
        if (isset($_COOKIE[session_name()]) &&
                @strlen($_COOKIE[session_name()]) >= 32
            ) {
            $IDpassed = true;
        }

        if (!$IDpassed) {
            // Es wurde keine (gültige) Session-ID übergeben.
            // Script-Parameter der URL zufügen
                
            $query = @$_SERVER["QUERY_STRING"] != "" ? "?" . $_SERVER["QUERY_STRING"] : "";
             
            header("Status: 302 Found");
                
            // Script terminiert
            $this->redirectTo($_SERVER["PHP_SELF"] . $query);
        }
            
        // Wenn die Session-ID übergeben wurde, muss sie
        // nicht unbedingt gültig sein!
        
        // Für weiteren Gebrauch merken
        $this->usesCookies =
                       (isset($_COOKIE[session_name()]) &&
                        @strlen($_COOKIE[session_name()])
                        >= 32);
    }
 
    ### -------------------------------------------------------
    /**
    *   Cacheing unterbinden
    *
    *   Ergänze/Override "session.cache_limiter = nocache"
    *
    *   @param  void
    *   @return void
    */
    public function sendNoCacheHeader()
    {
        header("Expires: Sat, 05 Aug 2000 22:27:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Cache-Control: post-check=0, pre-check=0");
    }

    ### -------------------------------------------------------
    /**
    *   HTTP-Redirect ausführen (header("Location: ...")
    *
    *   Diese Methode berücksichtigt auch nicht-standard Ports
    *   und SSL. Ein GET-Parameter beim  wird bei Bedarf
    *   (Session-ID-Fallback) an die URI drangehängt. Nach
    *   dem Aufruf dieser Methode wird das aktive Script
    *   beendet und die Kontrolle wird an das Ziel-Script
    *   übergeben.
    *
    *   @param  string  Ziel-Datei (z.B. "index.php")
    *   @return void
    */
    public function redirectTo($pathInfo)
    {
        
        // Relativer Pfad?
        if ($pathInfo[0] != "/") {
            $pathInfo = substr(
                getenv("PATH_INFO"),
                0,
                strrpos(getenv("PATH_INFO"), "/") + 1
            )
                            . $pathInfo;
        }

        // Läuft dieses Script auf einem non-standard Port?
        $port = !preg_match(
            "/^(80|443)$/",
            getenv("SERVER_PORT"),
            $portMatch
        )
                   ? ":" . getenv("SERVER_PORT")
                   : "";
                                         
        // Redirect
        header("Location: "
               . (($portMatch[1] == 443) ? "https://" : "http://")
               . $_SERVER["HTTP_HOST"] . $port . $this->url($pathInfo));
        exit;
    }

    ### -------------------------------------------------------
    /**
    *   Entfernt mögliche abschließende "&" und "?"
    *
    *   @param  string  String
    *   @return string  String ohne abschließende "&" und "?"
    */
    public function removeTrail($pathInfo)
    {
        $dummy = preg_match("/(.*)(?<!&|\?)/", $pathInfo, $match);
        return $match[0];
    }

    ### -------------------------------------------------------
    /**
    *   Fallback via GET - wenn Cookies ausgeschaltet sind
    *
    *   @param  string  Ziel-Datei
    *   @return string  Ziel-Datei mit - bei Bedarf - angehängter Session-ID
    */
    public function url($pathInfo)
    {
        if ($this->usesCookies || $this->transSID) {
            return $pathInfo;
        }

        // Anchor-Fragment extrahieren
        $dummyArray = explode("#", $pathInfo);
        $pathInfo = $dummyArray[0];

        // evtl. (kaputte) Session-ID(s) aus dem Querystring entfernen
        $pathInfo = preg_replace(
            "/[?|&]" . session_name() . "=[^&]*/",
            "",
            $pathInfo
        );
        
        // evtl. Query-Delimiter korrigieren
        if (preg_match("/&/", $pathInfo) && !preg_match("/\?/", $pathInfo)) {
            // 4ter Parameter für "preg_replace()" erst ab 4.0.1pl2
            $pathInfo = preg_replace("/&/", "?", $pathInfo, 1);
        }
        
        // Restmüll entsorgen
        $pathInfo = $this->removeTrail($pathInfo);
        
        // Session-Name und Session-ID frisch hinzufügen
        $pathInfo .= preg_match("/\?/", $pathInfo) ? "&" : "?";
        
        // Anchor-Fragment wieder anfügen
        $pathInfo .= isset($dummyArray[1]) ? "#" . $dummyArray[1] : "";
        
        return $pathInfo;
    }
} // of class
