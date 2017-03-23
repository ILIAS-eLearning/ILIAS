<?php

/**
 * Interface ilFileDeliveryService
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilFileDeliveryService {

	/**
	 * @param $path_to_file
	 * @param null $download_file_name
	 * @param null $mime_type
	 * @param bool $delete_file
	 */
	public static function deliverFileAttached($path_to_file, $download_file_name = null, $mime_type = null, $delete_file = false);


	/**
	 * @param $path_to_file
	 * @param null $download_file_name
	 */
	public static function streamVideoInline($path_to_file, $download_file_name = null);


	/**
	 * @param $path_to_file
	 * @param null $download_file_name
	 */
	public static function deliverFileInline($path_to_file, $download_file_name = null);

	/**
	 * Converts a UTF-8 filename to ASCII
	 *
	 * @param $original_filename string UFT8-Filename
	 * @return string ASCII-Filename
	 */
	public static function returnASCIIFileName($original_filename);
}
