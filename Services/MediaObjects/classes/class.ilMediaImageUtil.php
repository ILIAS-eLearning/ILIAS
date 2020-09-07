<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Image utility class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilMediaImageUtil
{
    /**
     * Get image size from location
     *
     * @param string $a_location
     * @return array
     */
    public static function getImageSize($a_location)
    {
        if (substr($a_location, 0, 4) == "http") {
            include_once("./Services/WebServices/Curl/classes/class.ilCurlConnection.php");
            if (ilCurlConnection::_isCurlExtensionLoaded()) {
                $dir = ilUtil::getDataDir() . "/temp/mob/remote_img";
                ilUtil::makeDirParents($dir);
                $filename = $dir . "/" . uniqid();
                $file = fopen($filename, "w");
                $c = new ilCurlConnection($a_location);
                $c->init();
                require_once './Services/Http/classes/class.ilProxySettings.php';
                if (ilProxySettings::_getInstance()->isActive()) {
                    $proxy = ilProxySettings::_getInstance()->getHost();
                    if (($p = ilProxySettings::_getInstance()->getPort()) != "") {
                        $proxy .= ":" . $p;
                    }
                    $c->setOpt(CURLOPT_PROXY, $proxy);
                }
                $c->setOpt(CURLOPT_SSL_VERIFYHOST, 0);
                $c->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
                $c->setOpt(CURLOPT_MAXREDIRS, 3);
                $c->setOpt(CURLOPT_HEADER, 0);
                $c->setOpt(CURLOPT_RETURNTRANSFER, 1);
                $c->setOpt(CURLOPT_FILE, $file);
                try {
                    $c->exec();
                    $size = @getimagesize($filename);
                } catch (ilCurlConnectionException $e) {
                    $size = false;
                }
                $c->close();
                fclose($file);
                unlink($filename);
            } else {
                $size = @getimagesize($a_location);
            }
        } else {
            $size = @getimagesize($a_location);
        }
        return $size;
    }
}
