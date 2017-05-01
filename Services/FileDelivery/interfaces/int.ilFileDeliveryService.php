<?php

namespace ILIAS\FileDelivery;

/**
 * Interface ilFileDeliveryService
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilFileDeliveryService {

	/**
	 * @param $path_to_file
	 * @param string $download_file_name
	 * @param string $mime_type
	 * @param bool $delete_file
	 *
	 * @return void
	 */
	public static function deliverFileAttached(string $path_to_file, string $download_file_name = '', string $mime_type = '', bool $delete_file = false);


	/**
	 * @param $path_to_file
	 * @param string $download_file_name
	 *
	 * @return void
	 */
	public static function streamVideoInline(string $path_to_file, string $download_file_name = '');


	/**
	 * @param $path_to_file
	 * @param string $download_file_name
	 *
	 * @return void
	 */
	public static function deliverFileInline(string $path_to_file, string $download_file_name = '');

	/**
	 * Converts a UTF-8 filename to ASCII
	 *
	 * @param string $original_filename UFT8-Filename
	 *
	 * @return string ASCII-Filename
	 */
	public static function returnASCIIFileName(string $original_filename) : string;
}
