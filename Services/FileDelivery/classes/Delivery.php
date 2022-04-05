<?php
declare(strict_types=1);

namespace ILIAS\FileDelivery;

use ILIAS\HTTP\Services;
use ILIAS\FileDelivery\FileDeliveryTypes\DeliveryMethod;
use ILIAS\FileDelivery\FileDeliveryTypes\FileDeliveryTypeFactory;
use ILIAS\HTTP\Response\ResponseHeader;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    const EXPIRES_IN = '+5 days';
    private static ?string $delivery_type_static = null;
    private string $delivery_type = DeliveryMethod::PHP;
    private string $mime_type = '';
    private string $path_to_file = '';
    private string $download_file_name = '';
    private string $disposition = self::DISP_ATTACHMENT;
    private bool $send_mime_type = true;
    private bool $exit_after = true;
    private bool $convert_file_name_to_asci = true;
    private string $etag = '';
    private bool $show_last_modified = true;
    private bool $has_context = true;
    private bool $cache = false;
    private bool $hash_filename = false;
    private bool $delete_file = false;
    private static bool $DEBUG = false;
    private Services $http;
    private FileDeliveryTypeFactory $factory;


    /**
     * @param string   $path_to_file
     * @param Services $http
     */
    public function __construct(string $path_to_file, Services $http)
    {
        $this->http = $http;
        if ($path_to_file === self::DIRECT_PHP_OUTPUT) {
            $this->setPathToFile(self::DIRECT_PHP_OUTPUT);
        } else {
            $this->setPathToFile($path_to_file);
            $this->detemineDeliveryType();
            $this->determineMimeType();
            $this->determineDownloadFileName();
        }
        $this->setHasContext(\ilContext::getType() !== null);
        $this->factory = new FileDeliveryTypeFactory($http);
    }


    public function stream() : void
    {
        if (!$this->delivery()->supportsStreaming()) {
            $this->setDeliveryType(DeliveryMethod::PHP_CHUNKED);
        }
        $this->deliver();
    }


    private function delivery() : ilFileDeliveryType
    {
        return $this->factory->getInstance($this->getDeliveryType());
    }


    public function deliver() : void
    {
        $response = $this->http->response()->withHeader('X-ILIAS-FileDelivery-Method', $this->getDeliveryType());
        if (
            !$this->delivery()->doesFileExists($this->path_to_file)
            && $this->path_to_file !== self::DIRECT_PHP_OUTPUT
        ) {
            $response = $this->http->response()->withStatus(404);
            $this->http->saveResponse($response);
            $this->http->sendResponse();
            $this->close();
        }
        $this->http->saveResponse($response);

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


    public function setGeneralHeaders() : void
    {
        $this->checkExisting();
        if ($this->isSendMimeType()) {
            $response = $this->http->response()->withHeader(ResponseHeader::CONTENT_TYPE, $this->getMimeType());
            $this->http->saveResponse($response);
        }
        if ($this->isConvertFileNameToAsci()) {
            $this->cleanDownloadFileName();
        }
        if ($this->hasHashFilename()) {
            $this->setDownloadFileName(md5($this->getDownloadFileName()));
        }
        $this->setDispositionHeaders();
        $response = $this->http->response()->withHeader(ResponseHeader::ACCEPT_RANGES, 'bytes');
        $this->http->saveResponse($response);
        if ($this->getDeliveryType() === DeliveryMethod::PHP
            && $this->getPathToFile() !== self::DIRECT_PHP_OUTPUT
        ) {
            $response = $this->http->response()->withHeader(ResponseHeader::CONTENT_LENGTH, (string) filesize($this->getPathToFile()));
            $this->http->saveResponse($response);
        }
        $response = $this->http->response()->withHeader(ResponseHeader::CONNECTION, "close");
        $this->http->saveResponse($response);
    }


    public function setCachingHeaders() : void
    {
        $response = $this->http->response()->withHeader(ResponseHeader::CACHE_CONTROL, 'must-revalidate, post-check=0, pre-check=0')->withHeader(ResponseHeader::PRAGMA, 'public');

        $this->http->saveResponse($response->withHeader(ResponseHeader::EXPIRES, date("D, j M Y H:i:s", strtotime(self::EXPIRES_IN)) . " GMT"));
        $this->sendEtagHeader();
        $this->sendLastModified();
    }


    public function generateEtag() : void
    {
        $this->setEtag(md5(filemtime($this->getPathToFile()) . filesize($this->getPathToFile())));
    }


    public function close() : void
    {
        $this->http->close();
    }


    private function determineMimeType() : void
    {
        $info = \ILIAS\FileUpload\MimeType::lookupMimeType($this->getPathToFile(), \ILIAS\FileUpload\MimeType::APPLICATION__OCTET_STREAM);
        if ($info) {
            $this->setMimeType($info);

            return;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $info = finfo_file($finfo, $this->getPathToFile());
        finfo_close($finfo);
        if ($info) {
            $this->setMimeType($info);

            return;
        }
    }


    private function determineDownloadFileName() : void
    {
        if (!$this->getDownloadFileName()) {
            $download_file_name = basename($this->getPathToFile());
            $this->setDownloadFileName($download_file_name);
        }
    }


    private function detemineDeliveryType() : void
    {
        if (self::$delivery_type_static) {
            $this->setDeliveryType(self::$delivery_type_static);

            return;
        }

        if (function_exists('apache_get_modules')
            && in_array('mod_xsendfile', apache_get_modules(), true)
        ) {
            $this->setDeliveryType(DeliveryMethod::XSENDFILE);
        }

        if (is_file('./Services/FileDelivery/classes/override.php')) {
            $override_delivery_type = false;
            /** @noRector */
            require_once('./Services/FileDelivery/classes/override.php');
            if ($override_delivery_type) {
                $this->setDeliveryType($override_delivery_type);
            }
        }
        $ilRuntime = \ilRuntime::getInstance();
        if ((!$ilRuntime->isFPM() && !$ilRuntime->isHHVM())
            && $this->getDeliveryType() === DeliveryMethod::XACCEL
        ) {
            $this->setDeliveryType(DeliveryMethod::PHP);
        }

        if ($this->getDeliveryType() === DeliveryMethod::XACCEL
            && strpos($this->getPathToFile(), './data') !== 0
        ) {
            $this->setDeliveryType(DeliveryMethod::PHP);
        }

        self::$delivery_type_static = $this->getDeliveryType();
    }


    public function getDeliveryType() : string
    {
        return $this->delivery_type;
    }


    public function setDeliveryType(string $delivery_type) : void
    {
        $this->delivery_type = $delivery_type;
    }


    public function getMimeType() : string
    {
        return $this->mime_type;
    }


    public function setMimeType(string $mime_type) : void
    {
        $this->mime_type = $mime_type;
    }


    public function getPathToFile() : string
    {
        return $this->path_to_file;
    }


    public function setPathToFile(string $path_to_file) : void
    {
        $this->path_to_file = $path_to_file;
    }


    public function getDownloadFileName() : string
    {
        return $this->download_file_name;
    }


    public function setDownloadFileName(string $download_file_name) : void
    {
        $this->download_file_name = $download_file_name;
    }


    public function getDisposition() : string
    {
        return $this->disposition;
    }


    public function setDisposition(string $disposition) : void
    {
        $this->disposition = $disposition;
    }


    public function isSendMimeType() : bool
    {
        return $this->send_mime_type;
    }


    public function setSendMimeType(bool $send_mime_type) : void
    {
        $this->send_mime_type = $send_mime_type;
    }


    public function isExitAfter() : bool
    {
        return $this->exit_after;
    }


    public function setExitAfter(bool $exit_after) : void
    {
        $this->exit_after = $exit_after;
    }


    public function isConvertFileNameToAsci() : bool
    {
        return $this->convert_file_name_to_asci;
    }


    public function setConvertFileNameToAsci(bool $convert_file_name_to_asci) : void
    {
        $this->convert_file_name_to_asci = $convert_file_name_to_asci;
    }


    public function getEtag() : string
    {
        return $this->etag;
    }


    public function setEtag(string $etag) : void
    {
        $this->etag = $etag;
    }


    public function getShowLastModified() : bool
    {
        return $this->show_last_modified;
    }


    public function setShowLastModified(bool $show_last_modified) : void
    {
        $this->show_last_modified = $show_last_modified;
    }


    public function isHasContext() : bool
    {
        return $this->has_context;
    }


    public function setHasContext(bool $has_context) : void
    {
        $this->has_context = $has_context;
    }


    public function hasCache() : bool
    {
        return $this->cache;
    }


    public function setCache(bool $cache) : void
    {
        $this->cache = $cache;
    }


    public function hasHashFilename() : bool
    {
        return $this->hash_filename;
    }


    public function setHashFilename(bool $hash_filename) : void
    {
        $this->hash_filename = $hash_filename;
    }


    private function sendEtagHeader() : void
    {
        if ($this->getEtag()) {
            $response = $this->http->response()->withHeader('ETag', $this->getEtag());
            $this->http->saveResponse($response);
        }
    }


    private function sendLastModified() : void
    {
        if ($this->getShowLastModified()) {
            $response = $this->http->response()->withHeader(
                'Last-Modified',
                date("D, j M Y H:i:s", filemtime($this->getPathToFile()))
                               . " GMT"
            );
            $this->http->saveResponse($response);
        }
    }

    public static function isDEBUG() : bool
    {
        return self::$DEBUG;
    }


    public static function setDEBUG(bool $DEBUG) : void
    {
        self::$DEBUG = $DEBUG;
    }


    public function checkCache() : void
    {
        if ($this->hasCache()) {
            $this->generateEtag();
            $this->sendEtagHeader();
            $this->setShowLastModified(true);
            $this->setCachingHeaders();
        }
    }


    /**
     * @return bool
     */
    public function clearBuffer() : bool
    {
        try {
            $ob_get_contents = ob_get_contents();
            if ($ob_get_contents) {
                //			\ilWACLog::getInstance()->write(__CLASS__ . ' had output before file delivery: '
                //			                                . $ob_get_contents);
            }
            ob_end_clean(); // fixed 0016469, 0016467, 0016468
            return true;
        } catch (\Throwable $t) {
            return false;
        }
    }


    private function checkExisting() : void
    {
        if ($this->getPathToFile() !== self::DIRECT_PHP_OUTPUT
            && !file_exists($this->getPathToFile())
        ) {
            $this->close();
        }
    }


    /**
     * Converts the filename to ASCII
     */
    private function cleanDownloadFileName() : void
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
    public static function returnASCIIFileName(string $original_filename) : string
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


    public function isDeleteFile() : bool
    {
        return $this->delete_file;
    }


    public function setDeleteFile(bool $delete_file) : void
    {
        $this->delete_file = $delete_file;
    }


    private function setDispositionHeaders() : void
    {
        $response = $this->http->response();
        $response = $response->withHeader(
            ResponseHeader::CONTENT_DISPOSITION,
            $this->getDisposition()
                                               . '; filename="'
                                               . $this->getDownloadFileName()
                                               . '"'
        );
        $response = $response->withHeader('Content-Description', $this->getDownloadFileName());
        $this->http->saveResponse($response);
    }
}
