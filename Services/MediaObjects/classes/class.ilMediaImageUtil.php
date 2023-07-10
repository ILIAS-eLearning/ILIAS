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
 * Image utility class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaImageUtil
{
    /**
     * Get image size from location
     * @throws ilCurlConnectionException
     */
    public static function getImageSize(string $a_location): ?array
    {
        if (substr($a_location, 0, 4) == "http") {
            if (ilCurlConnection::_isCurlExtensionLoaded()) {
                $dir = ilFileUtils::getDataDir() . "/temp/mob/remote_img";
                ilFileUtils::makeDirParents($dir);
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
                    $size = getimagesize($filename);
                } catch (ilCurlConnectionException $e) {
                    $size = null;
                }
                $c->close();
                fclose($file);
                unlink($filename);
            } else {
                $size = getimagesize($a_location);
            }
        } else {
            if (is_file($a_location)) {
                $size = getimagesize($a_location);
            }
        }
        if (!isset($size)) {
            $size = [0,0];
        }
        return $size;
    }
}
