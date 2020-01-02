<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * FFmpeg wrapper
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup Services/MediaObjects
 */
class ilFFmpeg
{
    /**
     * Formats handled by ILIAS. Note: In general the mime types
     * do not reflect the complexity of media container/codec variants.
     * For source formats no specification is needed here. For target formats
     * we use fixed parameters that should result in best web media practice.
     */
    public static $formats = array(
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
    
    public static $last_return = array();
    
    /**
     * Checks, whether FFmpeg support is enabled (path is set in the setup)
     *
     * @param
     * @return
     */
    public static function enabled()
    {
        if (defined("PATH_TO_FFMPEG") && PATH_TO_FFMPEG != "") {
            return true;
        }
        return false;
    }
    
    /**
     * Get target mime types
     *
     * (Please note, that we do not list all possible encoders here,
     * only the ones that are desired for the use in ILIAS)
     *
     * @param
     * @return
     */
    public static function getTargetMimeTypes()
    {
        $ttypes = array();
        foreach (self::$formats as $k => $f) {
            if ($f["target"] == true) {
                $ttypes[] = $k;
            }
        }
        return $ttypes;
    }
    
    /**
     * Get source mime types
     *
     * @param
     * @return
     */
    public static function getSourceMimeTypes()
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
     *
     * @param string $a_mime mime type
     */
    public static function supportsImageExtraction($a_mime)
    {
        if (in_array($a_mime, self::getSourceMimeTypes())) {
            return true;
        }
        return false;
    }
    
    /**
     * Get possible target formats
     *
     * @param
     * @return
     */
    public static function getPossibleTargetMimeTypes($a_source_mime_type)
    {
        $pt = array();
        if (in_array($a_source_mime_type, self::getSourceMimeTypes())) {
            foreach (self::getTargetMimeTypes() as $tm) {
                if ($tm != $a_source_mime_type) {
                    $pt[$tm] = $tm;
                }
            }
        }
        return $pt;
    }
    
    
    /**
     * Get ffmpeg command
     */
    private static function getCmd()
    {
        return PATH_TO_FFMPEG;
    }

    /**
     * Execute ffmpeg
     *
     * @param
     * @return
     */
    public static function exec($args)
    {
        return ilUtil::execQuoted(self::getCmd(), $args);
    }
    
    /**
     * Get all supported codecs
     *
     * @return
     */
    public static function getSupportedCodecsInfo()
    {
        $codecs = self::exec("-codecs");
        
        return $codecs;
    }

    /**
     * Get all supported formats
     *
     * @return
     */
    public static function getSupportedFormatsInfo()
    {
        $formats = self::exec("-formats");
        
        return $formats;
    }
    
    /**
     * Get file info
     *
     * @param
     * @return
     */
    public function getFileInfo()
    {
        //$info = `ffmpeg -i $path$file 2>&1 /dev/null`;
        //@fields = split(/\n/, $info);
    }
    
    /**
     * Convert file to target mime type
     *
     * @param string $a_file source file (full path included)
     * @param string $a_target_mime target mime type
     * @param string $a_target_dir target directory (no trailing "/")
     * @param string $a_target_filename target file name (no path!)
     *
     * @return string new file (full path)
     */
    public static function convert($a_file, $a_target_mime, $a_target_dir = "", $a_target_filename = "")
    {
        return; // currently not supported

        if (self::$formats[$a_target_mime]["target"] != true) {
            include_once("./Services/MediaObjects/exceptions/class.ilFFmpegException.php");
            throw new ilFFmpegException("Format " . $a_target_mime . " is not supported");
        }
        $pars = self::$formats[$a_target_mime]["parameters"];
        $spi = pathinfo($a_file);
        
        // use source directory if no target directory is passed
        $target_dir = ($a_target_dir != "")
            ? $a_target_dir
            : $spi['dirname'];
            
        // use source filename if no target filename is passed
        $target_filename = ($a_target_filename != "")
            ? $a_target_filename
            : $spi['filename'] . "." . self::$formats[$a_target_mime]["suffix"];
        
        $target_file = $target_dir . "/" . $target_filename;
        
        $cmd = "-y -i " . ilUtil::escapeShellArg($a_file) . " " . $pars . " " . ilUtil::escapeShellArg($target_file);

        $ret = self::exec($cmd . " 2>&1");
        self::$last_return = $ret;
        
        if (is_file($target_file)) {
            return $target_file;
        } else {
            include_once("./Services/MediaObjects/exceptions/class.ilFFmpegException.php");
            throw new ilFFmpegException("It was not possible to convert file " . basename($a_file) . ".");
        }
        //ffmpeg -i MOV012.3gp -vcodec libx264 -strict experimental -acodec aac -sameq -ab 64k -ar 44100 MOV012.mp4
    }
    
    /**
     * Get last return values
     *
     * @param
     * @return
     */
    public static function getLastReturnValues()
    {
        return self::$last_return;
    }
    
    /**
     * Extract image from video file
     *
     * @param string $a_file source file (full path included)
     * @param string $a_target_dir target directory (no trailing "/")
     * @param string $a_target_filename target file name (no path!)
     *
     * @return string new file (full path)
     */
    public static function extractImage(
        $a_file,
        $a_target_filename,
        $a_target_dir = "",
        $a_sec = 1
    ) {
        //echo "-$a_file-$a_target_filename-$a_target_dir-$a_sec-<br>";

        $spi = pathinfo($a_file);
        
        // use source directory if no target directory is passed
        $target_dir = ($a_target_dir != "")
            ? $a_target_dir
            : $spi['dirname'];
        
        $target_file = $target_dir . "/" . $a_target_filename;
        
        $sec = (int) $a_sec;
        $cmd = "-y -i " . ilUtil::escapeShellArg($a_file) . " -r 1 -f image2 -vframes 1 -ss " . $sec . " " . ilUtil::escapeShellArg($target_file);
        //echo "-$cmd-"; exit;
        $ret = self::exec($cmd . " 2>&1");
        self::$last_return = $ret;
        
        if (is_file($target_file)) {
            return $target_file;
        } else {
            include_once("./Services/MediaObjects/exceptions/class.ilFFmpegException.php");
            throw new ilFFmpegException("It was not possible to extract an image from " . basename($a_file) . ".");
        }
    }
}
