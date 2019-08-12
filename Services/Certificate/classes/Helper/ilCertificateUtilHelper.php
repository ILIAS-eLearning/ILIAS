<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Just a wrapper class to create Unit Test for other classes.
 * Can be remove when the static method calls have been removed
 *
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUtilHelper
{
    /**
     * @param string $data
     * @param string $fileName
     * @param string $mimeType
     */
    public function deliverData(string $data, string $fileName, string $mimeType)
    {
        ilUtil::deliverData(
            $data,
            $fileName,
            $mimeType
        );
    }

    /**
     * @param string $string
     * @return string
     */
    public function prepareFormOutput(string $string) : string
    {
        return ilUtil::prepareFormOutput($string);
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $targetFormat
     * @param string $geometry
     * @param string $backgroundColor
     */
    public function convertImage(
        string $from,
        string $to,
        string $targetFormat = '',
        string $geometry = '',
        string $backgroundColor = ''
    ) {
        return ilUtil::convertImage($from, $to, $targetFormat, $geometry, $backgroundColor);
    }

    /**
     * @param string $string
     * @return mixed|null|string|string[]
     */
    public function stripSlashes(string $string) : string
    {
        return ilUtil::stripSlashes($string);
    }

    /**
     * @param string $exportPath
     * @param string $zipPath
     */
    public function zip(string $exportPath, string $zipPath)
    {
        ilUtil::zip($exportPath, $zipPath);
    }

    /**
     * @param string $zipPath
     * @param string $zipFileName
     * @param string $mime
     */
    public function deliverFile(string $zipPath, string $zipFileName, string $mime)
    {
        ilUtil::deliverFile($zipPath, $zipFileName, $mime);
    }

    /**
     * @param string $copyDirectory
     * @return array
     */
    public function getDir(string $copyDirectory) : array
    {
        return ilUtil::getDir($copyDirectory);
    }

    /**
     * @param string $file
     * @param bool $overwrite
     */
    public function unzip(string $file, bool $overwrite)
    {
        ilUtil::unzip($file, $overwrite);
    }

    /**
     * @param string $path
     */
    public function delDir(string $path)
    {
        ilUtil::delDir($path);
    }

    /**
     * @param string $file
     * @param string $name
     * @param string $target
     * @param bool $raise_errors
     * @param string $mode
     * @return bool
     * @throws ilException
     */
    public function moveUploadedFile(
        string $file,
        string $name,
        string $target,
        bool $raise_errors = true,
        string $mode = 'move_uploaded'
    ) {
        return ilUtil::moveUploadedFile(
            $file,
            $name,
            $target,
            $raise_errors,
            $mode
        );
    }

    /**
     * @param $img
     * @param string $module_path
     * @param string $mode
     * @param bool $offline
     * @return string
     */
    public function getImagePath($img, $module_path = "", $mode = "output", $offline = false)
    {
        return ilUtil::getImagePath(
            $img,
            $module_path,
            $mode,
            $offline
        );
    }
}
