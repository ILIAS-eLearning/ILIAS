<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCloudUtil
 * Some utility function, mostly for path handling.
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @ingroup ModulesCloud
 */
class ilCloudUtil
{
    public static function normalizePath(string $path): string
    {
        if ($path == "." || $path == "/" || $path == "") {
            $path = "/";
        } else {
            $path = "/" . rtrim(ltrim(str_replace('//', '/', $path), "/"), "/");
        }

        return $path;
    }

    public static function joinPaths(string $path1, string $path2): string
    {
        $path1 = ilCloudUtil::normalizePath($path1);
        $path2 = ilCloudUtil::normalizePath($path2);

        return ilCloudUtil::normalizePath(str_replace('//', '/', $path1 . $path2));
    }

    /**
     * With trailing and leading slashes
     */
    public static function joinPathsAbsolute(string $path1, string $path2): string
    {
        $path = ilCloudUtil::normalizePath(str_replace('//', '/', $path1 . $path2));
        if ($path == "/") {
            return $path;
        } else {
            return "/" . ltrim($path, "/") . "/";
        }
    }
}
