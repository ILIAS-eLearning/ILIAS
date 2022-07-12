<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Analyzes external media locations and extracts important information
 * into parameter field.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExternalMediaAnalyzer
{
    /**
     * Identify YouTube links
     */
    public static function isYouTube(
        string $a_location
    ) : bool {
        if (strpos($a_location, "youtube.com") > 0 ||
                strpos($a_location, "youtu.be") > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * Extract YouTube Parameter
     * @return array<string,string>
     */
    public static function extractYouTubeParameters(
        string $a_location
    ) : array {
        $par = array();
        $pos1 = strpos($a_location, "v=");
        $pos2 = strpos($a_location, "&", $pos1);
        if ($pos1 > 0) {
            $len = ($pos2 > 0)
                ? $pos2
                : strlen($a_location);
            $par["v"] = substr($a_location, $pos1 + 2, $len - ($pos1 + 2));
        } elseif (strpos($a_location, "youtu.be") > 0) {
            $par["v"] = substr($a_location, strrpos($a_location, "/") + 1);
        }

        return $par;
    }

    /**
     * Identify Flickr links
     */
    public static function isFlickr(
        string $a_location
    ) : bool {
        if (strpos($a_location, "flickr.com") > 0) {
            return true;
        }
        return false;
    }

    /**
     * Extract Flickr Parameter
     * @return array<string,string>
     */
    public static function extractFlickrParameters(
        string $a_location
    ) : array {
        $par = array();
        $pos1 = strpos($a_location, "flickr.com/photos/");
        $pos2 = strpos($a_location, "/", $pos1 + 18);
        if ($pos1 > 0) {
            $len = ($pos2 > 0)
                ? $pos2
                : $a_location;
            $par["user_id"] = substr($a_location, $pos1 + 18, $len - ($pos1 + 18));
        }
        
        // tags
        $pos1 = strpos($a_location, "/tags/");
        $pos2 = strpos($a_location, "/", $pos1 + 6);
        if ($pos1 > 0) {
            $len = ($pos2 > 0)
                ? $pos2
                : strlen($a_location);
            $par["tags"] = substr($a_location, $pos1 + 6, $len - ($pos1 + 6));
        }

        // sets
        $pos1 = strpos($a_location, "/sets/");
        $pos2 = strpos($a_location, "/", $pos1 + 6);
        if ($pos1 > 0) {
            $len = ($pos2 > 0)
                ? $pos2
                : $a_location;
            $par["sets"] = substr($a_location, $pos1 + 6, $len - ($pos1 + 6));
        }

        return $par;
    }

    /**
     * Identify GoogleVideo links
     */
    public static function isGoogleVideo(
        string $a_location
    ) : bool {
        if (strpos($a_location, "video.google") > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * Extract GoogleVideo Parameter
     * @return array<string,string>
     */
    public static function extractGoogleVideoParameters(
        string $a_location
    ) : array {
        $par = array();
        $pos1 = strpos($a_location, "docid=");
        $pos2 = strpos($a_location, "&", $pos1 + 6);
        if ($pos1 > 0) {
            $len = ($pos2 > 0)
                ? $pos2
                : strlen($a_location);
            $par["docid"] = substr($a_location, $pos1 + 6, $len - ($pos1 + 6));
        }

        return $par;
    }

    /**
     * Identify Vimeo links
     */
    public static function isVimeo(
        string $a_location
    ) : bool {
        if (strpos($a_location, "vimeo.com") > 0) {
            return true;
        }
        return false;
    }

    /**
     * Extract Vimeo Parameter
     * @return array<string,string>
     */
    public static function extractVimeoParameters(
        string $a_location
    ) : array {
        $par = array();
        $pos1 = strpos($a_location, "vimeo.com/");
        $pos2 = strpos($a_location, "&", $pos1 + 10);
        if ($pos1 > 0) {
            $len = ($pos2 > 0)
                ? $pos2
                : strlen($a_location);
            $par["id"] = substr($a_location, $pos1 + 10, $len - ($pos1 + 10));
        }

        return $par;
    }

    public static function getVimeoMetadata(string $vid) : array
    {
        $json_url = 'https://vimeo.com/api/oembed.json?url=https%3A//vimeo.com/' . $vid;

        $curl = curl_init($json_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_REFERER, ILIAS_HTTP_PATH);

        $return = curl_exec($curl);
        curl_close($curl);

        $r = json_decode($return, true);

        if ($return === false || is_null($r)) {
            throw new ilExternalMediaApiException("Could not connect to vimeo API at $json_url.");
        }
        return $r;
    }

    public static function getYoutubeMetadata(string $vid) : array
    {
        $json_url = 'https://www.youtube.com/oembed?url=http%3A//youtube.com/watch%3Fv%3D' . $vid . '&format=json';

        $curl = curl_init($json_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_REFERER, ILIAS_HTTP_PATH);

        $return = curl_exec($curl);
        curl_close($curl);

        $r = json_decode($return, true);

        if ($return === false || is_null($r)) {
            throw new ilExternalMediaApiException("Could not connect to vimeo API at $json_url.");
        }
        return $r;
    }

    /**
     * Identify Google Document links
     */
    public static function isGoogleDocument(
        string $a_location
    ) : bool {
        if (strpos($a_location, "docs.google") > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * Extract GoogleDocument Parameter
     * @return array<string,string>
     */
    public static function extractGoogleDocumentParameters(
        string $a_location
    ) : array {
        $par = array();
        $pos1 = strpos($a_location, "id=");
        $pos2 = strpos($a_location, "&", $pos1 + 3);
        if ($pos1 > 0) {
            $len = ($pos2 > 0)
                ? $pos2
                : strlen($a_location);
            $par["docid"] = substr($a_location, $pos1 + 3, $len - ($pos1 + 3));
        }
        $pos1 = strpos($a_location, "docID=");
        $pos2 = strpos($a_location, "&", $pos1 + 6);
        if ($pos1 > 0) {
            $len = ($pos2 > 0)
                ? $pos2
                : strlen($a_location);
            $par["docid"] = substr($a_location, $pos1 + 6, $len - ($pos1 + 6));
        }
        if (strpos($a_location, "Presentation?") > 0) {
            $par["type"] = "Presentation";
        }
        if (strpos($a_location, "View?") > 0) {
            $par["type"] = "Document";
        }

        return $par;
    }
    
    /**
     * Extract URL information to parameter array
     * @return array<string,string>
     */
    public static function extractUrlParameters(
        string $a_location,
        array $a_parameter
    ) : array {
        $ext_par = array();
        
        // YouTube
        if (ilExternalMediaAnalyzer::isYouTube($a_location)) {
            $ext_par = ilExternalMediaAnalyzer::extractYouTubeParameters($a_location);
            $a_parameter = array();
        }

        // Flickr
        if (ilExternalMediaAnalyzer::isFlickr($a_location)) {
            $ext_par = ilExternalMediaAnalyzer::extractFlickrParameters($a_location);
            $a_parameter = array();
        }

        // GoogleVideo
        if (ilExternalMediaAnalyzer::isGoogleVideo($a_location)) {
            $ext_par = ilExternalMediaAnalyzer::extractGoogleVideoParameters($a_location);
            $a_parameter = array();
        }

        // GoogleDocs
        if (ilExternalMediaAnalyzer::isGoogleDocument($a_location)) {
            $ext_par = ilExternalMediaAnalyzer::extractGoogleDocumentParameters($a_location);
            $a_parameter = array();
        }

        foreach ($ext_par as $name => $value) {
            $a_parameter[$name] = $value;
        }

        return $a_parameter;
    }
}
