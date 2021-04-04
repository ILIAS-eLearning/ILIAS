<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Image utility class
 *
 * @author Alex Killing <alex.killing@gmx.de>
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
            if (ilCurlConnection::_isCurlExtensionLoaded()) {
                $dir = ilUtil::getDataDir() . "/temp/mob/remote_img";
                ilUtil::makeDirParents($dir);
                $filename = $dir . "/" . uniqid();
                $file = fopen($filename, "w");
                $c = new ilCurlConnection($a_location);
                $c->init();
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
