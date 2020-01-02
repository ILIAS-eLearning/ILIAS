<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCloudUtil
 *
 * Some utility function, mostly for path handling.
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 *
 * @ingroup ModulesCloud
 */
class ilCloudUtil
{
    /**
     * @param $path
     * @return string
     */
    public static function normalizePath($path)
    {
        if ($path == "." || $path == "/" || $path == "") {
            $path = "/";
        } else {
            $path = "/" . rtrim(ltrim(str_replace('//', '/', $path), "/"), "/");
        }

        return $path;
    }

    /**
     * @param $path1
     * @param $path2
     * @return string
     */
    public static function joinPaths($path1, $path2)
    {
        $path1 = ilCloudUtil::normalizePath($path1);
        $path2 = ilCloudUtil::normalizePath($path2);
        return ilCloudUtil::normalizePath(str_replace('//', '/', $path1 . $path2));
    }

    /**
     * With trailing and leading slashes
     * @param $path1
     * @param $path2
     * @return string
     */
    public static function joinPathsAbsolute($path1, $path2)
    {
        $path = ilCloudUtil::normalizePath(str_replace('//', '/', $path1 . $path2));
        if ($path == "/") {
            return $path;
        } else {
            return "/" . ltrim($path, "/") . "/";
        }
    }
}
