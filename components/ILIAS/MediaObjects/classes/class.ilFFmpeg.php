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
 * FFmpeg wrapper
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFFmpeg
{
    public static ?array $last_return = array();

    /**
     * Formats handled by ILIAS. Note: In general the mime types
     * do not reflect the complexity of media container/codec variants.
     * For source formats no specification is needed here. For target formats
     * we use fixed parameters that should result in best web media practice.
     * @var array[]
     */
    public static array $formats = array(
        "video/3pgg" => array(
            "source" => true,
            "target" => false
            ),
        "video/x-flv" => array(
            "source" => true,
            "target" => false
            ),
        "video/mp4" => array(
            "source" => true,
            "target" => true,
            "parameters" => "-vcodec libx264 -strict experimental -acodec aac -sameq -ab 56k -ar 48000",
            "suffix" => "mp4"
            ),
        "video/webm" => array(
            "source" => true,
            "target" => true,
            "parameters" => "-strict experimental -vcodec libvpx -acodec vorbis -ac 2 -sameq -ab 56k -ar 48000",
            "suffix" => "webm"
            )
        );


    /**
     * Checks, whether FFmpeg support is enabled (path is set in the setup)
     */
    public static function enabled(): bool
    {
        if (defined("PATH_TO_FFMPEG") && PATH_TO_FFMPEG != "") {
            return true;
        }
        return false;
    }

    /**
     * @return string[]
     */
    public static function getSourceMimeTypes(): array
    {
        $ttypes = array();
        foreach (self::$formats as $k => $f) {
            if ($f["source"] == true) {
                $ttypes[] = $k;
            }
        }
        return $ttypes;
    }

    /**
     * Check if mime type supports image extraction
     */
    public static function supportsImageExtraction(
        string $a_mime
    ): bool {
        if (in_array($a_mime, self::getSourceMimeTypes(), true)) {
            return true;
        }
        return false;
    }


    /**
     * Get ffmpeg command
     */
    private static function getCmd(): string
    {
        return PATH_TO_FFMPEG;
    }

    protected static function exec(string $args): array
    {
        $win = (stripos(php_uname(), "win") === 0);
        $cmd = self::getCmd();
        if ($win && str_contains($cmd, " ") && $cmd[0] !== '"') {
            $cmd = '"' . $cmd . '"';
            if ($args) {
                $cmd .= " " . $args;
            }
        } elseif ($args) {
            $cmd .= " " . $args;
        }
        $arr = [];
        exec($cmd, $arr);
        return $arr;
    }

    protected static function escapeShellArg(string $a_arg): string
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
     * Get last return values
     */
    public static function getLastReturnValues(): ?array
    {
        return self::$last_return;
    }

    /**
     * Extract image from video file
     *
     * @param string $a_file source file (full path included)
     * @param string $a_target_dir target directory (no trailing "/")
     * @param string $a_target_filename target file name (no path!)
     * @param int    $a_sec
     * @return string new file (full path)
     * @throws ilFFmpegException
     */
    public static function extractImage(
        string $a_file,
        string $a_target_filename,
        string $a_target_dir = "",
        int $a_sec = 1
    ): string {
        $spi = pathinfo($a_file);

        // use source directory if no target directory is passed
        $target_dir = ($a_target_dir != "")
            ? $a_target_dir
            : $spi['dirname'];

        $target_file = $target_dir . "/" . $a_target_filename;

        $sec = $a_sec;
        $cmd = "-y -i " . ilShellUtil::escapeShellArg(
            $a_file
        ) . " -r 1 -f image2 -vframes 1 -ss " . $sec . " " . ilShellUtil::escapeShellArg($target_file);
        $ret = self::exec($cmd . " 2>&1");
        self::$last_return = $ret;

        if (is_file($target_file)) {
            return $target_file;
        } else {
            throw new ilFFmpegException("It was not possible to extract an image from " . basename($a_file) . ".");
        }
    }

    public static function extractPNGFromVideoInZip(
        string $zip,
        string $path,
        int $sec = 1
    ): string {
        $zip = self::escapeShellArg($zip);
        $path = self::escapeShellArg("/" . $path);

        $command = "unzip -p $zip $path | ffmpeg -i pipe:0 -f image2 -vframes 1 -ss $sec -vcodec png pipe:1";

        return (string) shell_exec($command);
    }
}
