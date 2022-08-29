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
 * Util class
 * various functions, usage as namespace
 *
 * @author     Sascha Hofmann <saschahofmann@gmx.de>
 * @author     Alex Killing <alex.killing@gmx.de>
 *
 * @deprecated The 2021 Technical Board has decided to mark the ilUtil class as deprecated. The ilUtil is a historically
 * grown helper class with many different UseCases and functions. The class is not under direct maintainership and the
 * responsibilities are unclear. In this context, the class should no longer be used in the code and existing uses
 * should be converted to their own service in the medium term. If you need ilUtil for the implementation of a new
 * function in ILIAS > 7, please contact the Technical Board.
 */
class ilShellUtil
{
    /**
     * resize image
     *
     * @param string $a_from   source file
     * @param string $a_to     target file
     * @param int    $a_width  target width
     * @param int    $a_height target height
     * @static
     *
     */
    public static function resizeImage(
        string $a_from,
        string $a_to,
        int $a_width,
        int $a_height,
        bool $a_constrain_prop = false
    ): void {
        if ($a_constrain_prop) {
            $size = " -geometry " . $a_width . "x" . $a_height . " ";
        } else {
            $size = " -resize " . $a_width . "x" . $a_height . "! ";
        }
        $convert_cmd = ilShellUtil::escapeShellArg($a_from) . " " . $size . ilShellUtil::escapeShellArg($a_to);

        ilShellUtil::execConvert($convert_cmd);
    }

    public static function escapeShellArg(string $a_arg): string
    {
        setlocale(
            LC_CTYPE,
            "UTF8",
            "en_US.UTF-8"
        ); // fix for PHP escapeshellcmd bug. See: http://bugs.php.net/bug.php?id=45132
        // see also ilias bug 5630
        return escapeshellarg($a_arg);
    }

    /**
     * Parse convert version string, e.g. 6.3.8-3, into integer
     *
     * @param string $a_version w.x.y-z
     * @return int
     */
    protected static function processConvertVersion(string $a_version): int
    {
        if (preg_match("/([0-9]+)\.([0-9]+)\.([0-9]+)([\.|\-]([0-9]+))?/", $a_version, $match)) {
            $version = str_pad($match[1], 2, "0", STR_PAD_LEFT) .
                str_pad($match[2], 2, "0", STR_PAD_LEFT) .
                str_pad($match[3], 2, "0", STR_PAD_LEFT) .
                str_pad($match[5], 2, "0", STR_PAD_LEFT);
            return (int) $version;
        }
        return 0;
    }

    /**
     * @deprecated
     */
    public static function escapeShellCmd(string $a_arg): string
    {
        if (ini_get('safe_mode') == 1) {
            return $a_arg;
        }
        setlocale(
            LC_CTYPE,
            "UTF8",
            "en_US.UTF-8"
        ); // fix for PHP escapeshellcmd bug. See: http://bugs.php.net/bug.php?id=45132
        return escapeshellcmd($a_arg);
    }

    /**
     * @deprecated
     */
    public static function execQuoted(string $cmd, ?string $args = null): array
    {
        global $DIC;

        if (ilUtil::isWindows() && strpos($cmd, " ") !== false && substr($cmd, 0, 1) !== '"') {
            // cmd won't work without quotes
            $cmd = '"' . $cmd . '"';
            if ($args) {
                // args are also quoted, workaround is to quote the whole command AGAIN
                // was fixed in php 5.2 (see php bug #25361)
                if (version_compare(phpversion(), "5.2", "<") && strpos($args, '"') !== false) {
                    $cmd = '"' . $cmd . " " . $args . '"';
                } // args are not quoted or php is fixed, just append
                else {
                    $cmd .= " " . $args;
                }
            }
        } // nothing todo, just append args
        elseif ($args) {
            $cmd .= " " . $args;
        }
        $arr = [];
        exec($cmd, $arr);
        $DIC->logger()->root()->debug("ilUtil::execQuoted: " . $cmd . ".");
        return $arr;
    }

    /**
     * Compare convert version numbers
     *
     * @param string $a_version w.x.y-z
     * @return bool
     */
    public static function isConvertVersionAtLeast(string $a_version): bool
    {
        $current_version = ilShellUtil::execQuoted(PATH_TO_CONVERT, "--version");
        $current_version = self::processConvertVersion($current_version[0]);
        $version = self::processConvertVersion($a_version);
        if ($current_version >= $version) {
            return true;
        }
        return false;
    }

    /**
     * get convert command
     *
     * @deprecated
     * @see ilShellUtil::execConvert()
     * @static
     *
     */
    public static function getConvertCmd(): string
    {
        return PATH_TO_CONVERT;
    }

    /**
     * convert image
     *
     * @param string $a_from          source file
     * @param string $a_to            target file
     * @param string $a_target_format target image file format
     * @static
     *
     */
    public static function convertImage(
        string $a_from,
        string $a_to,
        string $a_target_format = "",
        string $a_geometry = "",
        string $a_background_color = ""
    ): void {
        $format_str = ($a_target_format != "")
            ? strtoupper($a_target_format) . ":"
            : "";
        $geometry = "";
        if ($a_geometry != "") {
            if (is_int(strpos($a_geometry, "x"))) {
                $geometry = " -geometry " . $a_geometry . " ";
            } else {
                $geometry = " -geometry " . $a_geometry . "x" . $a_geometry . " ";
            }
        }

        $bg_color = ($a_background_color != "")
            ? " -background color " . $a_background_color . " "
            : "";
        $convert_cmd = ilShellUtil::escapeShellArg($a_from) . " " . $bg_color . $geometry . ilShellUtil::escapeShellArg(
            $format_str . $a_to
        );
        ilShellUtil::execConvert($convert_cmd);
    }

    /**
     * execute convert command
     *
     * @param string $args
     * @static
     *
     */
    public static function execConvert(string $args): void
    {
        $args = self::escapeShellCmd($args);
        ilShellUtil::execQuoted(PATH_TO_CONVERT, $args);
    }
}
