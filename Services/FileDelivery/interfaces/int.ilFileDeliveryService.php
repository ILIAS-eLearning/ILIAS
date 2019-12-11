<?php

namespace ILIAS\FileDelivery;

/**
 * Interface ilFileDeliveryService
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilFileDeliveryService
{

    /**
     * @param        $path_to_file
     * @param string $download_file_name
     * @param string $mime_type
     * @param bool   $delete_file
     *
     * @return void
     */
    public static function deliverFileAttached($path_to_file, $download_file_name = '', $mime_type = '', $delete_file = false);


    /**
     * @param        $path_to_file
     * @param string $download_file_name
     *
     * @return void
     */
    public static function streamVideoInline($path_to_file, $download_file_name = '');


    /**
     * @param        $path_to_file
     * @param string $download_file_name
     *
     * @return void
     */
    public static function deliverFileInline($path_to_file, $download_file_name = '');


    /**
     * Converts a UTF-8 filename to ASCII
     *
     * @param string $original_filename UFT8-Filename
     *
     * @return string ASCII-Filename
     */
    public static function returnASCIIFileName($original_filename);
}
