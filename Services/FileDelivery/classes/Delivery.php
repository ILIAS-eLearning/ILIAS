<?php

namespace ILIAS\FileDelivery;

require_once('./Services/Utilities/classes/class.ilMimeTypeUtil.php');
require_once('./Services/Utilities/classes/class.ilUtil.php'); // This include is needed since WAC can use ilFileDelivery without Initialisation
require_once('./Services/Context/classes/class.ilContext.php');
require_once('./Services/Http/classes/class.ilHTTPS.php');
require_once('./Services/FileDelivery/classes/FileDeliveryTypes/FileDeliveryTypeFactory.php');
require_once './Services/FileDelivery/classes/FileDeliveryTypes/DeliveryMethod.php';

use ILIAS\DI\HTTPServices;
use ILIAS\FileDelivery\FileDeliveryTypes\DeliveryMethod;
use ILIAS\FileDelivery\FileDeliveryTypes\FileDeliveryTypeFactory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\ResponseHeader;

/**
 * Class Delivery
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.0
 * @since   5.3
 *
 * @Internal
 */
final class Delivery
{
    const DIRECT_PHP_OUTPUT = 'php://output';
    const DISP_ATTACHMENT = 'attachment';
    const DISP_INLINE = 'inline';
    /**
     * @var integer
     */
    private static $delivery_type_static = null;
    /**
     * @var string
     */
    private $delivery_type = DeliveryMethod::PHP;
    /**
     * @var string
     */
    private $mime_type = '';
    /**
     * @var string
     */
    private $path_to_file = '';
    /**
     * @var string
     */
    private $download_file_name = '';
    /**
     * @var string
     */
    private $disposition = self::DISP_ATTACHMENT;
    /**
     * @var bool
     */
    private $send_mime_type = true;
    /**
     * @var bool
     */
    private $exit_after = true;
    /**
     * @var bool
     */
    private $convert_file_name_to_asci = true;
    /**
     * @var string
     */
    private $etag = '';
    /**
     * @var bool
     */
    private $show_last_modified = true;
    /**
     * @var bool
     */
    private $has_context = true;
    /**
     * @var bool
     */
    private $cache = false;
    /**
     * @var bool
     */
    private $hash_filename = false;
    /**
     * @var bool
     */
    private $delete_file = false;
    /**
     * @var bool
     */
    private static $DEBUG = false;
    /**
     * @var HTTPServices $httpService
     */
    private $httpService;
    /**
     * @var FileDeliveryTypeFactory $fileDeliveryTypeFactory
     */
    private $fileDeliveryTypeFactory;


    /**
     * @param string          $path_to_file
     * @param GlobalHttpState $httpState
     */
    public function __construct($path_to_file, GlobalHttpState $httpState)
    {
        assert(is_string($path_to_file));
        $this->httpService = $httpState;
        if ($path_to_file == self::DIRECT_PHP_OUTPUT) {
            $this->setPathToFile(self::DIRECT_PHP_OUTPUT);
        } else {
            $this->setPathToFile($path_to_file);
            $this->detemineDeliveryType();
            $this->determineMimeType();
            $this->determineDownloadFileName();
        }
        $this->setHasContext(\ilContext::getType() !== null);
        $this->fileDeliveryTypeFactory = new FileDeliveryTypeFactory($httpState);
    }


    public function stream()
    {
        if (!$this->delivery()->supportsStreaming()) {
            $this->setDeliveryType(DeliveryMethod::PHP_CHUNKED);
        }
        $this->deliver();
    }


    private function delivery()
    {
        return $this->fileDeliveryTypeFactory->getInstance($this->getDeliveryType());
    }


    public function deliver()
    {
        $response = $this->httpService->response()->withHeader('X-ILIAS-FileDelivery-Method', $this->getDeliveryType());
        if (!$this->delivery()->doesFileExists($this->path_to_file)) {
            $response = $this->httpService->response()->withStatus(404);
            $this->httpService->saveResponse($response);
            $this->httpService->sendResponse();
            $this->close();
        }
        $this->httpService->saveResponse($response);

        $this->clearBuffer();
        $this->checkCache();
        $this->setGeneralHeaders();
        $this->delivery()->prepare($this->getPathToFile());
        $this->delivery()->deliver($this->getPathToFile(), $this->isDeleteFile());
        if ($this->isDeleteFile()) {
            $this->delivery()->handleFileDeletion($this->getPathToFile());
        }
        if ($this->isExitAfter()) {
            $this->close();
        }
    }


    public function setGeneralHeaders()
    {
        $this->checkExisting();
        if ($this->isSendMimeType()) {
            $response = $this->httpService->response()->withHeader(ResponseHeader::CONTENT_TYPE, $this->getMimeType());
            $this->httpService->saveResponse($response);
        }
        if ($this->isConvertFileNameToAsci()) {
            $this->cleanDownloadFileName();
        }
        if ($this->hasHashFilename()) {
            $this->setDownloadFileName(md5($this->getDownloadFileName()));
        }
        $this->setDispositionHeaders();
        $response = $this->httpService->response()->withHeader(ResponseHeader::ACCEPT_RANGES, 'bytes');
        $this->httpService->saveResponse($response);
        if ($this->getDeliveryType() == DeliveryMethod::PHP
            && $this->getPathToFile() != self::DIRECT_PHP_OUTPUT
        ) {
            $response = $this->httpService->response()->withHeader(ResponseHeader::CONTENT_LENGTH, (string) filesize($this->getPathToFile()));
            $this->httpService->saveResponse($response);
        }
        $response = $this->httpService->response()->withHeader(ResponseHeader::CONNECTION, "close");
        $this->httpService->saveResponse($response);
    }


    public function setCachingHeaders()
    {
        $response = $this->httpService->response()->withHeader(ResponseHeader::CACHE_CONTROL, 'must-revalidate, post-check=0, pre-check=0')->withHeader(ResponseHeader::PRAGMA, 'public');

        $this->httpService->saveResponse($response);
        $this->sendEtagHeader();
        $this->sendLastModified();
    }


    public function generateEtag()
    {
        $this->setEtag(md5(filemtime($this->getPathToFile()) . filesize($this->getPathToFile())));
    }


    public function close()
    {
        exit;
    }


    /**
     * @return bool
     */
    private function determineMimeType()
    {
        $info = \ilMimeTypeUtil::lookupMimeType($this->getPathToFile(), \ilMimeTypeUtil::APPLICATION__OCTET_STREAM);
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


    /**
     * @return void
     */
    private function determineDownloadFileName()
    {
        if (!$this->getDownloadFileName()) {
            $download_file_name = basename($this->getPathToFile());
            $this->setDownloadFileName($download_file_name);
        }
    }


    /**
     * @return bool
     */
    private function detemineDeliveryType()
    {
        if (self::$delivery_type_static) {
            \ilWACLog::getInstance()->write('used cached delivery type');
            $this->setDeliveryType(self::$delivery_type_static);

            return true;
        }

        if (function_exists('apache_get_modules')
            && in_array('mod_xsendfile', apache_get_modules())
        ) {
            $this->setDeliveryType(DeliveryMethod::XSENDFILE);
        }

        if (is_file('./Services/FileDelivery/classes/override.php')) {
            $override_delivery_type = false;
            require_once('./Services/FileDelivery/classes/override.php');
            if ($override_delivery_type) {
                $this->setDeliveryType($override_delivery_type);
            }
        }

        require_once('./Services/Environment/classes/class.ilRuntime.php');
        $ilRuntime = \ilRuntime::getInstance();
        if ((!$ilRuntime->isFPM() && !$ilRuntime->isHHVM())
            && $this->getDeliveryType() == DeliveryMethod::XACCEL
        ) {
            $this->setDeliveryType(DeliveryMethod::PHP);
        }

        if ($this->getDeliveryType() == DeliveryMethod::XACCEL
            && strpos($this->getPathToFile(), './data') !== 0
        ) {
            $this->setDeliveryType(DeliveryMethod::PHP);
        }

        self::$delivery_type_static = $this->getDeliveryType();

        return true;
    }


    /**
     * @return string
     */
    public function getDeliveryType()
    {
        return $this->delivery_type;
    }


    /**
     * @param string $delivery_type
     */
    public function setDeliveryType($delivery_type)
    {
        $this->delivery_type = $delivery_type;
    }


    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }


    /**
     * @param string $mime_type
     */
    public function setMimeType($mime_type)
    {
        $this->mime_type = $mime_type;
    }


    /**
     * @return string
     */
    public function getPathToFile()
    {
        return $this->path_to_file;
    }


    /**
     * @param string $path_to_file
     */
    public function setPathToFile($path_to_file)
    {
        $this->path_to_file = $path_to_file;
    }


    /**
     * @return string
     */
    public function getDownloadFileName()
    {
        return $this->download_file_name;
    }


    /**
     * @param string $download_file_name
     */
    public function setDownloadFileName($download_file_name)
    {
        $this->download_file_name = $download_file_name;
    }


    /**
     * @return string
     */
    public function getDisposition()
    {
        return $this->disposition;
    }


    /**
     * @param string $disposition
     */
    public function setDisposition($disposition)
    {
        $this->disposition = $disposition;
    }


    /**
     * @return boolean
     */
    public function isSendMimeType()
    {
        return $this->send_mime_type;
    }


    /**
     * @param boolean $send_mime_type
     */
    public function setSendMimeType($send_mime_type)
    {
        $this->send_mime_type = $send_mime_type;
    }


    /**
     * @return boolean
     */
    public function isExitAfter()
    {
        return $this->exit_after;
    }


    /**
     * @param boolean $exit_after
     */
    public function setExitAfter($exit_after)
    {
        $this->exit_after = $exit_after;
    }


    /**
     * @return boolean
     */
    public function isConvertFileNameToAsci()
    {
        return $this->convert_file_name_to_asci;
    }


    /**
     * @param boolean $convert_file_name_to_asci
     */
    public function setConvertFileNameToAsci($convert_file_name_to_asci)
    {
        $this->convert_file_name_to_asci = $convert_file_name_to_asci;
    }


    /**
     * @return string
     */
    public function getEtag()
    {
        return $this->etag;
    }


    /**
     * @param string $etag
     */
    public function setEtag($etag)
    {
        $this->etag = $etag;
    }


    /**
     * @return boolean
     */
    public function getShowLastModified()
    {
        return $this->show_last_modified;
    }


    /**
     * @param boolean $show_last_modified
     */
    public function setShowLastModified($show_last_modified)
    {
        $this->show_last_modified = $show_last_modified;
    }


    /**
     * @return boolean
     */
    public function isHasContext()
    {
        return $this->has_context;
    }


    /**
     * @param boolean $has_context
     */
    public function setHasContext($has_context)
    {
        $this->has_context = $has_context;
    }


    /**
     * @return boolean
     */
    public function hasCache()
    {
        return $this->cache;
    }


    /**
     * @param boolean $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }


    /**
     * @return boolean
     */
    public function hasHashFilename()
    {
        return $this->hash_filename;
    }


    /**
     * @param boolean $hash_filename
     */
    public function setHashFilename($hash_filename)
    {
        $this->hash_filename = $hash_filename;
    }


    private function sendEtagHeader()
    {
        if ($this->getEtag()) {
            $response = $this->httpService->response()->withHeader('ETag', $this->getEtag());
            $this->httpService->saveResponse($response);
        }
    }


    private function sendLastModified()
    {
        if ($this->getShowLastModified()) {
            $response = $this->httpService->response()->withHeader(
                'Last-Modified',
                date("D, j M Y H:i:s", filemtime($this->getPathToFile()))
                               . " GMT"
            );
            $this->httpService->saveResponse($response);
        }
    }

    //	/**
    //	 * @return bool
    //	 */
    //	private function isNonModified() {
    //		if (self::$DEBUG) {
    //			return false;
    //		}
    //
    //		if (!isset($_SERVER['HTTP_IF_NONE_MATCH']) || !isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    //			return false;
    //		}
    //
    //		$http_if_none_match = $_SERVER['HTTP_IF_NONE_MATCH'];
    //		$http_if_modified_since = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
    //
    //		switch (true) {
    //			case ($http_if_none_match != $this->getEtag()):
    //				return false;
    //			case (@strtotime($http_if_modified_since) <= filemtime($this->getPathToFile())):
    //				return false;
    //		}
    //
    //		return true;
    //	}

    /**
     * @return bool
     */
    public static function isDEBUG()
    {
        return (bool) self::$DEBUG;
    }


    /**
     * @param bool $DEBUG
     */
    public static function setDEBUG($DEBUG)
    {
        assert(is_bool($DEBUG));
        self::$DEBUG = $DEBUG;
    }


    /**
     * @return void
     */
    public function checkCache()
    {
        if ($this->hasCache()) {
            $this->generateEtag();
            $this->sendEtagHeader();
            $this->setShowLastModified(true);
            $this->setCachingHeaders();
        }
    }


    /**
     * @return void
     */
    public function clearBuffer()
    {
        $ob_get_contents = ob_get_contents();
        if ($ob_get_contents) {
            //			\ilWACLog::getInstance()->write(__CLASS__ . ' had output before file delivery: '
            //			                                . $ob_get_contents);
        }
        ob_end_clean(); // fixed 0016469, 0016467, 0016468
    }


    /**
     * @return void
     */
    private function checkExisting()
    {
        if ($this->getPathToFile() != self::DIRECT_PHP_OUTPUT
            && !file_exists($this->getPathToFile())
        ) {
            $this->close();
        }
    }


    /**
     * Converts the filename to ASCII
     *
     * @return void
     */
    private function cleanDownloadFileName()
    {
        $download_file_name = self::returnASCIIFileName($this->getDownloadFileName());
        $this->setDownloadFileName($download_file_name);
    }


    /**
     * Converts a UTF-8 filename to ASCII
     *
     * @param $original_filename string UFT8-Filename
     *
     * @return string ASCII-Filename
     */
    public static function returnASCIIFileName($original_filename)
    {
        // The filename must be converted to ASCII, as of RFC 2183,
        // section 2.3.

        /// Implementation note:
        /// 	The proper way to convert charsets is mb_convert_encoding.
        /// 	Unfortunately Multibyte String functions are not an
        /// 	installation requirement for ILIAS 3.
        /// 	Codelines behind three slashes '///' show how we would do
        /// 	it using mb_convert_encoding.
        /// 	Note that mb_convert_encoding has the bad habit of
        /// 	substituting unconvertable characters with HTML
        /// 	entitities. Thats why we need a regular expression which
        /// 	replaces HTML entities with their first character.
        /// 	e.g. &auml; => a

        /// $ascii_filename = mb_convert_encoding($a_filename,'US-ASCII','UTF-8');
        /// $ascii_filename = preg_replace('/\&(.)[^;]*;/','\\1', $ascii_filename);

        // #15914 - try to fix german umlauts
        $umlauts = array(
            "Ä" => "Ae",
            "Ö" => "Oe",
            "Ü" => "Ue",
            "ä" => "ae",
            "ö" => "oe",
            "ü" => "ue",
            "ß" => "ss",
        );
        foreach ($umlauts as $src => $tgt) {
            $original_filename = str_replace($src, $tgt, $original_filename);
        }

        $ascii_filename = htmlentities($original_filename, ENT_NOQUOTES, 'UTF-8');
        $ascii_filename = preg_replace('/\&(.)[^;]*;/', '\\1', $ascii_filename);
        $ascii_filename = preg_replace('/[\x7f-\xff]/', '_', $ascii_filename);

        // OS do not allow the following characters in filenames: \/:*?"<>|
        $ascii_filename = preg_replace('/[:\x5c\/\*\?\"<>\|]/', '_', $ascii_filename);

        return (string) $ascii_filename;
        //		return iconv("UTF-8", "ASCII//TRANSLIT", $original_name); // proposal
    }


    /**
     * @return bool
     */
    public function isDeleteFile()
    {
        return (bool) $this->delete_file;
    }


    /**
     * @param bool $delete_file
     *
     * @return void
     */
    public function setDeleteFile($delete_file)
    {
        assert(is_bool($delete_file));
        $this->delete_file = $delete_file;
    }


    private function setDispositionHeaders()
    {
        $response = $this->httpService->response();
        $response = $response->withHeader(
            ResponseHeader::CONTENT_DISPOSITION,
            $this->getDisposition()
                                               . '; filename="'
                                               . $this->getDownloadFileName()
                                               . '"'
        );
        $response = $response->withHeader('Content-Description', $this->getDownloadFileName());
        $this->httpService->saveResponse($response);
    }
}
