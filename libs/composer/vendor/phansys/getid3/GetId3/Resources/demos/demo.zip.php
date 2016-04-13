<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
//                                                             //
// /demo/demo.zip.php - part of getID3()                       //
// Sample script how to use getID3() to decompress zip files   //
// See readme.txt for more details                             //
//                                                            ///
/////////////////////////////////////////////////////////////////


function UnzipFileContents($filename, &$errors) {
	$errors = array();
	$DecompressedFileContents = array();
	if (include_once('getid3.module.archive.zip.php')) {
		ob_start();
		if ($fp_ziptemp = fopen($filename, 'rb')) {
			ob_end_clean();
			$ThisFileInfo['filesize'] = filesize($filename);
			$getid3_zip = new getid3_zip($fp_ziptemp, $ThisFileInfo);
			if (($ThisFileInfo['fileformat'] == 'zip') && !empty($ThisFileInfo['zip']['files'])) {
				if (!empty($ThisFileInfo['zip']['central_directory'])) {
					$ZipDirectoryToWalk = $ThisFileInfo['zip']['central_directory'];
				} elseif (!empty($ThisFileInfo['zip']['entries'])) {
					$ZipDirectoryToWalk = $ThisFileInfo['zip']['entries'];
				} else {
					$errors[] = 'failed to parse ZIP attachment "'.$piece_filename.'" (no central directory)<br>';
					fclose($fp_ziptemp);
					return false;
				}
				foreach ($ZipDirectoryToWalk as $key => $valuearray) {
					fseek($fp_ziptemp, $valuearray['entry_offset'], SEEK_SET);
					$LocalFileHeader = $getid3_zip->ZIPparseLocalFileHeader($fp_ziptemp);
					if ($LocalFileHeader['flags']['encrypted']) {
						// password-protected
						$DecompressedFileContents[$valuearray['filename']] = '';
					} else {
						fseek($fp_ziptemp, $LocalFileHeader['data_offset'], SEEK_SET);
						$compressedFileData = '';
						while ((strlen($compressedFileData) < $LocalFileHeader['compressed_size']) && !feof($fp_ziptemp)) {
							$compressedFileData .= fread($fp_ziptemp, 32768);
						}
						switch ($LocalFileHeader['raw']['compression_method']) {
							case 0:
								// store - great, do nothing at all
								$uncompressedFileData = $compressedFileData;
								break;

							case 8:
								ob_start();
								$uncompressedFileData = gzinflate($compressedFileData);
								$gzinflate_errors = ob_get_contents();
								ob_end_clean();
								if ($gzinflate_errors) {
									$errors[] = 'gzinflate() failed: "'.trim($gzinflate_errors).'"';
									continue 2;
								}
								break;

							default:
								$DecompressedFileContents[$valuearray['filename']] = '';
								$errors[] = 'unknown ZIP compression method ('.$LocalFileHeader['raw']['compression_method'].')';
								continue 2;
						}
						$DecompressedFileContents[$valuearray['filename']] = $uncompressedFileData;
						unset($compressedFileData);
					}
				}
			} else {
				$errors[] = $filename.' does not appear to be a zip file';
			}
		} else {
			$error_message = ob_get_contents();
			ob_end_clean();
			$errors[] = 'failed to fopen('.$filename.', rb): '.$error_message;
		}
	} else {
		$errors[] = 'failed to include_once(getid3.module.archive.zip.php)';
	}
	return $DecompressedFileContents;
}

?>