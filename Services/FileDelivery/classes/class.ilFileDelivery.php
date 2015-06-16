<?php
require_once('./Services/Utilities/classes/class.ilMimeTypeUtil.php');
require_once('./Services/Utilities/classes/class.ilUtil.php');
require_once('./Services/Http/classes/class.ilHTTPS.php');

/**
 * Class ilFileDelivery
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilFileDelivery {

	/**
	 * @var bool
	 */
	public static $DEV = false;
	const DELIVERY_METHOD_XSENDFILE = 'mod_xsendfile';
	const DELIVERY_METHOD_XACCEL = 'x-accel-redirect';
	const DELIVERY_METHOD_PHP = 'php';
	const DELIVERY_METHOD_PHP_CHUNKED = 'php_chunked';
	const DISP_ATTACHMENT = 'attachment';
	const DISP_INLINE = 'inline';
	/**
	 * @var string
	 */
	protected $delivery_type = self::DELIVERY_METHOD_PHP;
	/**
	 * @var string
	 */
	protected $mime_type = '';
	/**
	 * @var string
	 */
	protected $path_to_file = '';
	/**
	 * @var string
	 */
	protected $download_file_name = '';
	/**
	 * @var string
	 */
	protected $disposition = self::DISP_ATTACHMENT;
	/**
	 * @var bool
	 */
	protected $send_mime_type = true;
	/**
	 * @var bool
	 */
	protected $exit_after = true;
	/**
	 * @var bool
	 */
	protected $convert_file_name_to_asci = false;
	/**
	 * @var string
	 */
	protected $etag = '';
	/**
	 * @var bool
	 */
	protected $show_last_modified = false;


	/**
	 * @param      $path_to_file
	 * @param null $download_file_name
	 */
	public static function deliverFileAttached($path_to_file, $download_file_name = NULL) {
		$obj = new self($path_to_file);
		if ($download_file_name) {
			$obj->setDownloadFileName($download_file_name);
		}
		$obj->setDisposition(self::DISP_ATTACHMENT);
		$obj->deliver();
	}


	/**
	 * @param      $path_to_file
	 * @param null $download_file_name
	 */
	public static function deliverFileInline($path_to_file, $download_file_name = NULL) {
		$obj = new self($path_to_file);
		if ($download_file_name) {
			$obj->setDownloadFileName($download_file_name);
		}
		$obj->setDisposition(self::DISP_INLINE);
		$obj->deliver();
	}


	/**
	 * @param $path_to_file
	 */
	public function __construct($path_to_file) {
		$this->setPathToFile($path_to_file);
		$this->setDownloadFileName(basename($path_to_file));
		$this->detemineDeliveryType();
		$this->detemineMimeType();
	}


	public function stream() {
		$this->setDeliveryType(self::DELIVERY_METHOD_PHP_CHUNKED);
		$this->deliver();
	}


	public function deliver() {
		if (self::$DEV) {
			global $ilLog;
			$ilLog->write(print_r($this, true));
		}
		$this->setGeneralHeaders();
		switch ($this->getDeliveryType()) {
			default:
				$this->deliverPHP();
				break;
			case self::DELIVERY_METHOD_XSENDFILE:
				$this->deliverXSendfile();
				break;
			case self::DELIVERY_METHOD_PHP_CHUNKED:
				$this->deliverPHPChunked();
				break;
		}
		if ($this->isExitAfter()) {
			exit;
		}
	}


	protected function deliverXSendfile() {
		//		echo $this->getPathToFile();
		header('X-Sendfile: ' . $this->getPathToFile());
	}


	protected function deliverPHP() {
		set_time_limit(0);
		$file = @fopen($this->getPathToFile(), "rb");
		while (! feof($file)) {
			print(@fread($file, 1024 * 8));
			ob_flush();
			flush();
		}
	}


	protected function deliverPHPChunked() {
		$file = $this->getPathToFile();
		$fp = @fopen($file, 'rb');

		$size = filesize($file); // File size
		$length = $size;           // Content length
		$start = 0;               // Start byte
		$end = $size - 1;       // End byte
		// Now that we've gotten so far without errors we send the accept range header
		/* At the moment we only support single ranges.
		 * Multiple ranges requires some more work to ensure it works correctly
		 * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
		 *
		 * Multirange support annouces itself with:
		 * header('Accept-Ranges: bytes');
		 *
		 * Multirange content must be sent with multipart/byteranges mediatype,
		 * (mediatype = mimetype)
		 * as well as a boundry header to indicate the various chunks of data.
		 */
		header("Accept-Ranges: 0-$length");
		// header('Accept-Ranges: bytes');
		// multipart/byteranges
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
		if (isset($_SERVER['HTTP_RANGE'])) {
			$c_start = $start;
			$c_end = $end;

			// Extract the range string
			list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
			// Make sure the client hasn't sent us a multibyte range
			if (strpos($range, ',') !== false) {
				// (?) Shoud this be issued here, or should the first
				// range be used? Or should the header be ignored and
				// we output the whole content?
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header("Content-Range: bytes $start-$end/$size");
				// (?) Echo some info to the client?
				exit;
			} // fim do if
			// If the range starts with an '-' we start from the beginning
			// If not, we forward the file pointer
			// And make sure to get the end byte if spesified
			if ($range{0} == '-') {
				// The n-number of the last bytes is requested
				$c_start = $size - substr($range, 1);
			} else {
				$range = explode('-', $range);
				$c_start = $range[0];
				$c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
			} // fim do if
			/* Check the range and make sure it's treated according to the specs.
			 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
			 */
			// End bytes can not be larger than $end.
			$c_end = ($c_end > $end) ? $end : $c_end;
			// Validate the requested range and return an error if it's not correct.
			if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header("Content-Range: bytes $start-$end/$size");
				// (?) Echo some info to the client?
				exit;
			} // fim do if

			$start = $c_start;
			$end = $c_end;
			$length = $end - $start + 1; // Calculate new content length
			fseek($fp, $start);
			header('HTTP/1.1 206 Partial Content');
		} // fim do if

		// Notify the client the byte range we'll be outputting
		header("Content-Range: bytes $start-$end/$size");
		header("Content-Length: $length");

		// Start buffered download
		$buffer = 1024 * 8;
		while (! feof($fp) && ($p = ftell($fp)) <= $end) {
			if ($p + $buffer > $end) {
				// In case we're only outputtin a chunk, make sure we don't
				// read past the length
				$buffer = $end - $p + 1;
			} // fim do if

			set_time_limit(0); // Reset time limit for big files
			echo fread($fp, $buffer);
			flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
		} // fim do while

		fclose($fp);
	}


	protected function setGeneralHeaders() {
		if ($this->isSendMimeType()) {
			header("Content-type: " . $this->getMimeType());
		}
		$download_file_name = $this->getDownloadFileName();
		if ($this->isConvertFileNameToAsci()) {
			$download_file_name = ilUtil::getASCIIFilename($download_file_name);
		}
		header('Content-Disposition: ' . $this->getDisposition() . '; filename="' . $download_file_name . '"');
		header('Content-Description: ' . $download_file_name);
		header('Accept-Ranges: bytes');
		if ($this->getDeliveryType() == self::DELIVERY_METHOD_PHP) {
			header("Content-Length: " . (string)filesize($this->getPathToFile()));
		}

		if (ilHTTPS::getInstance()->isDetected()) {
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}

		if ($this->getEtag()) {
			header('ETag: "' . $this->getEtag() . '"');
		}
		if ($this->getShowLastModified()) {
			header('Last-Modified: ' . date("D, j M Y H:i:s", filemtime($this->getPathToFile())) . " GMT");
		}
		header("Connection: close");
	}


	public function generateEtag() {
		$this->setEtag(md5(filemtime($this->getPathToFile()) . filesize($this->getPathToFile())));
	}


	/**
	 * @return bool
	 */
	protected function detemineMimeType() {
		$info = ilMimeTypeUtil::getMimeType($this->getPathToFile(), $this->getDownloadFileName());
		if ($info) {
			$this->setMimeType($info);

			return true;
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$info = finfo_file($finfo, $this->getPathToFile());
		finfo_close($finfo);
		if ($info) {
			$this->setMimeType($info);

			return true;
		}

		return false;
	}


	protected function detemineDeliveryType() {
		if (function_exists('apache_get_modules') && in_array('mod_xsendfile', apache_get_modules())) {
			$this->setDeliveryType(self::DELIVERY_METHOD_XSENDFILE);
		}
	}


	/**
	 * @return string
	 */
	public function getDeliveryType() {
		return $this->delivery_type;
	}


	/**
	 * @param string $delivery_type
	 */
	public function setDeliveryType($delivery_type) {
		$this->delivery_type = $delivery_type;
	}


	/**
	 * @return string
	 */
	public function getMimeType() {
		return $this->mime_type;
	}


	/**
	 * @param string $mime_type
	 */
	public function setMimeType($mime_type) {
		$this->mime_type = $mime_type;
	}


	/**
	 * @return string
	 */
	public function getPathToFile() {
		return $this->path_to_file;
	}


	/**
	 * @param string $path_to_file
	 */
	public function setPathToFile($path_to_file) {
		$this->path_to_file = $path_to_file;
	}


	/**
	 * @return string
	 */
	public function getDownloadFileName() {
		return $this->download_file_name;
	}


	/**
	 * @param string $download_file_name
	 */
	public function setDownloadFileName($download_file_name) {
		$this->download_file_name = $download_file_name;
	}


	/**
	 * @return string
	 */
	public function getDisposition() {
		return $this->disposition;
	}


	/**
	 * @param string $disposition
	 */
	public function setDisposition($disposition) {
		$this->disposition = $disposition;
	}


	/**
	 * @return boolean
	 */
	public function isSendMimeType() {
		return $this->send_mime_type;
	}


	/**
	 * @param boolean $send_mime_type
	 */
	public function setSendMimeType($send_mime_type) {
		$this->send_mime_type = $send_mime_type;
	}


	/**
	 * @return boolean
	 */
	public function isExitAfter() {
		return $this->exit_after;
	}


	/**
	 * @param boolean $exit_after
	 */
	public function setExitAfter($exit_after) {
		$this->exit_after = $exit_after;
	}


	/**
	 * @return boolean
	 */
	public function isConvertFileNameToAsci() {
		return $this->convert_file_name_to_asci;
	}


	/**
	 * @param boolean $convert_file_name_to_asci
	 */
	public function setConvertFileNameToAsci($convert_file_name_to_asci) {
		$this->convert_file_name_to_asci = $convert_file_name_to_asci;
	}


	/**
	 * @return string
	 */
	public function getEtag() {
		return $this->etag;
	}


	/**
	 * @param string $etag
	 */
	public function setEtag($etag) {
		$this->etag = $etag;
	}


	/**
	 * @return boolean
	 */
	public function getShowLastModified() {
		return $this->show_last_modified;
	}


	/**
	 * @param boolean $show_last_modified
	 */
	public function setShowLastModified($show_last_modified) {
		$this->show_last_modified = $show_last_modified;
	}
}

?>
