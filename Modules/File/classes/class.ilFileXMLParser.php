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
 * Exercise XML Parser which completes/updates a given file by an xml string.
 *
 * @author  Roland Küstermann <roland@kuestermann.com>
 * @version $Id: class.ilObjectXMLParser.php 12811 2006-12-08 18:37:44Z akill $
 *
 * @ingroup ModulesExercise
 *
 * @extends ilSaxParser
 */
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\MimeType;

class ilFileXMLParser extends ilSaxParser
{
    public static int $CONTENT_NOT_COMPRESSED = 0;
    public static int $CONTENT_GZ_COMPRESSED = 1;
    public static int $CONTENT_ZLIB_COMPRESSED = 2;
    public static int $CONTENT_COPY = 4;
    // begin-patch fm
    public static int $CONTENT_REST = 5;
    // end-patch fm
    /**
     * Exercise object which has been parsed
     */
    public \ilObjFile $file;
    /**
     * this will be matched against the id in the xml
     * in case we want to update an exercise
     */
    public int $obj_id;
    /**
     * result of parsing and updating
     */
    public bool $result;
    /**
     * Content compression mode, defaults to no compression
     */
    public int $mode;
    /**
     *    file of temporary file where we store the file content instead of in memory
     */
    public ?string $tmpFilename = null;

    protected ?int $version = null;
    protected ?string $action = null;
    protected ?int $max_version = null;
    protected ?int $date = null;
    protected ?int $usr_id = null;
    protected array $versions = [];
    protected ?string $import_directory = null;
    protected ?string $cdata = null;

    /**
     * Constructor
     */
    public function __construct(ilObjFile $file, string $a_xml_data, int $obj_id = -1, int $mode = 0)
    {
        parent::__construct();
        $this->file = $file;
        $this->setXMLContent($a_xml_data);
        $this->obj_id = $obj_id;
        $this->result = false;
        $this->mode = $mode;
    }

    /**
     * Set import directory
     *
     * @param string    import directory
     */
    public function setImportDirectory(?string $a_val): void
    {
        $this->import_directory = $a_val;
    }

    /**
     * Get import directory
     *
     * @return mixed|null import directory
     */
    public function getImportDirectory(): ?string
    {
        return $this->import_directory;
    }

    /**
     * set event handlers
     *
     * @param resource    reference to the xml parser
     *
     * @access    private
     */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * handler for begin of element
     *
     * @param resource $a_xml_parser xml parser
     * @param string   $a_name       element name
     * @param array    $a_attribs    element attributes array
     *
     * @throws   ilFileException   when obj id != - 1 and if it it does not match the id in the xml
     *                              or deflation mode is not supported
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        global $DIC;

        global $DIC;

        switch ($a_name) {
            case 'File':
                if (isset($a_attribs["obj_id"])) {
                    $read_obj_id = ilUtil::__extractId($a_attribs["obj_id"], IL_INST_ID);
                    if ($this->obj_id != -1 && (int) $read_obj_id != -1 && $this->obj_id != (int) $read_obj_id) {
                        throw new ilFileException(
                            "Object IDs (xml $read_obj_id and argument " . $this->obj_id . ") do not match!",
                            ilFileException::$ID_MISMATCH
                        );
                    }
                }

                break;
            case 'Content': // Old import files
            case 'Version':
                if ($a_name === "Version" && !isset($a_attribs["mode"])) {
                    // Old import files
                    $this->version = null;
                    if ($this->date === null) {
                        // Version tag comes after Content tag. Take only first (= Should be latest)
                        $this->date = $a_attribs["date"];
                        $this->usr_id = $a_attribs["usr_id"];
                        $this->versions[0]["date"] = $this->date;
                        $this->versions[0]["usr_id"] = $this->usr_id;
                    }
                    break;
                }

                $this->mode = ilFileXMLParser::$CONTENT_NOT_COMPRESSED;
                #echo $a_attribs["mode"];
                if (isset($a_attribs["mode"])) {
                    if ($a_attribs["mode"] == "GZIP") {
                        if (!function_exists("gzread")) {
                            throw new ilFileException(
                                "Deflating with gzip is not supported",
                                ilFileException::$ID_DEFLATE_METHOD_MISMATCH
                            );
                        }

                        $this->mode = ilFileXMLParser::$CONTENT_GZ_COMPRESSED;
                    } elseif ($a_attribs["mode"] == "ZLIB") {
                        if (!function_exists("gzuncompress")) {
                            throw new ilFileException(
                                "Deflating with zlib (compress/uncompress) is not supported",
                                ilFileException::$ID_DEFLATE_METHOD_MISMATCH
                            );
                        }

                        $this->mode = ilFileXMLParser::$CONTENT_ZLIB_COMPRESSED;
                    } elseif ($a_attribs["mode"] == "COPY") {
                        $this->mode = ilFileXMLParser::$CONTENT_COPY;
                    } // begin-patch fm
                    elseif ($a_attribs['mode'] == 'REST') {
                        $this->mode = ilFileXMLParser::$CONTENT_REST;
                    }
                    // end-patch fm
                }

                if ($a_name === "Version") {
                    $this->version = (int) $a_attribs["version"];
                    $this->max_version = (int) $a_attribs["max_version"];
                    $this->date = (int) $a_attribs["date"];
                    $this->usr_id = (int) $a_attribs["usr_id"];
                    $this->action = (string) $a_attribs["action"];
                }
        }
    }

    /**
     * handler for end of element
     *
     * @param resource $a_xml_parser xml parser
     * @param string   $a_name       element name
     */
    public function handlerEndTag($a_xml_parser, string $a_name): void
    {
        $this->cdata = trim($this->cdata);

        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . $this->cdata);

        switch ($a_name) {
            case 'File':
                $this->result = true;
                break;
            case 'Filename':
                if ($this->cdata === '') {
                    throw new ilFileException("Filename ist missing!");
                }

                $this->file->setFilename($this->cdata);
                $this->file->setTitle($this->cdata);

                break;
            case 'Title':
                $this->file->setTitle(trim($this->cdata));
                break;
            case 'Description':
                $this->file->setDescription(trim($this->cdata));
                break;
            case 'Rating':
                $this->file->setRating((bool) $this->cdata);
                break;
            case 'Content': // Old import files
            case 'Version':
                if ($a_name === "Version" && $this->version === null) {
                    // Old import files
                    break;
                }

                $baseDecodedFilename = ilFileUtils::ilTempnam();
                if ($this->mode == ilFileXMLParser::$CONTENT_COPY) {
                    $this->tmpFilename = $this->getImportDirectory() . "/" . self::normalizeRelativePath($this->cdata);
                } // begin-patch fm
                elseif ($this->mode == ilFileXMLParser::$CONTENT_REST) {
                    $storage = new ilRestFileStorage();
                    $this->tmpFilename = $storage->getStoredFilePath(self::normalizeRelativePath($this->cdata));
                    if (!$this->fastBase64Decode($this->tmpFilename, $baseDecodedFilename)) {
                        throw new ilFileException("Base64-Decoding failed", ilFileException::$DECOMPRESSION_FAILED);
                    }
                    $this->tmpFilename = $baseDecodedFilename;
                } // end-patch fm
                else {
                    if (!$this->fastBase64Decode($this->tmpFilename, $baseDecodedFilename)) {
                        throw new ilFileException("Base64-Decoding failed", ilFileException::$DECOMPRESSION_FAILED);
                    }
                    if ($this->mode == ilFileXMLParser::$CONTENT_GZ_COMPRESSED) {
                        if (!$this->fastGunzip($baseDecodedFilename, $this->tmpFilename)) {
                            throw new ilFileException(
                                "Deflating with fastzunzip failed",
                                ilFileException::$DECOMPRESSION_FAILED
                            );
                        }
                        unlink($baseDecodedFilename);
                    } elseif ($this->mode == ilFileXMLParser::$CONTENT_ZLIB_COMPRESSED) {
                        if (!$this->fastGunzip($baseDecodedFilename, $this->tmpFilename)) {
                            throw new ilFileException(
                                "Deflating with fastDecompress failed",
                                ilFileException::$DECOMPRESSION_FAILED
                            );
                        }
                        unlink($baseDecodedFilename);
                    } else {
                        $this->tmpFilename = $baseDecodedFilename;
                    }
                }

                //$this->content = $content;
                // see #17211

                if ($this->version == $this->file->getVersion()) {
                    if (is_file($this->tmpFilename)) {
                        $this->file->setFileSize(filesize($this->tmpFilename)); // strlen($this->content));
                    }

                    // if no file type is given => lookup mime type
                    if (!$this->file->getFileType()) {
                        global $DIC;
                        $this->file->setFileType(MimeType::getMimeType($this->tmpFilename));
                    }
                }

                $this->versions[] = [
                    "version" => $this->version,
                    "max_version" => $this->max_version,
                    "tmpFilename" => $this->tmpFilename,
                    "date" => $this->date,
                    "usr_id" => $this->usr_id,
                    "action" => $this->action,
                ];
                $this->version = null;
                $this->date = null;
                $this->usr_id = null;
                break;
        }

        $this->cdata = '';
    }

    /**
     * handler for character data
     *
     * @param resource $a_xml_parser xml parser
     * @param string   $a_data       character data
     */
    public function handlerCharacterData($a_xml_parser, string $a_data): void
    {
        if ($a_data != "\n") {
            // begin-patch fm
            if ($this->mode != ilFileXMLParser::$CONTENT_COPY
                && $this->mode != ilFileXMLParser::$CONTENT_REST
            ) { // begin-patch fm
                $this->cdata .= $a_data;
            } else {
                $this->cdata .= $a_data;
            }
        }
    }

    /**
     * update file according to filename and version, does not update history
     * has to be called after (!) file save for new objects, since file storage will be initialised with obj id.
     *
     */
    public function setFileContents(): void
    {
        // Delete exists version 1 history
        ilHistory::_removeEntriesForObject($this->file->getId());

        foreach ($this->versions as $version) {
            if (!file_exists($version["tmpFilename"])) {
                if (!isset($version["tmpFilename"])) {
                    continue;
                }
                // try to get first file of directory
                $files = scandir(dirname($version["tmpFilename"]));
                $version["tmpFilename"] = rtrim(
                    dirname($version["tmpFilename"]),
                    "/"
                ) . "/" . $files[2];// because [0] = "." [1] = ".."
                if (!file_exists($version["tmpFilename"])) {
                    ilLoggerFactory::getLogger('file')->error(__METHOD__ . ' "' . ($version["tmpFilename"]) . '" file not found.');

                    continue;
                }
            }

            if (filesize($version["tmpFilename"]) == 0) {
                continue;
            }

            // imported file version
            $import_file_version_path = $version["tmpFilename"];

            $stream = Streams::ofResource(fopen($import_file_version_path, 'rb'));
            $this->file->appendStream($stream, $this->file->getTitle());
        }
    }

    /**
     * update file according to filename and version and create history entry
     * has to be called after (!) file save for new objects, since file storage will be initialised with obj id.
     *
     */
    public function updateFileContents(): void
    {
        // removed
    }

    /**
     * starts parsing an changes object by side effect.
     *
     * @return boolean true, if no errors happend.
     *
     * @throws ilFileException when obj id != - 1 and if it it does not match the id in the xml
     */
    public function start(): bool
    {
        $this->startParsing();

        return $this->result > 0;
    }

    /**
     * Normalize relative directories in a path.
     *
     * Source: https://github.com/thephpleague/flysystem/blob/master/src/Util.php#L96
     *  Workaround until we have
     *
     *
     * @throws LogicException
     *
     */
    public static function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        while (preg_match('#\p{C}+|^\./#u', $path)) {
            $path = preg_replace('#\p{C}+|^\./#u', '', $path);
        }

        $parts = [];
        foreach (explode('/', $path) as $part) {
            switch ($part) {
                case '':
                case '.':
                    break;
                case '..':
                    array_pop($parts);
                    break;
                default:
                    $parts[] = $part;
                    break;
            }
        }

        return implode('/', $parts);
    }

    private function fastBase64Decode(string $filein, string $fileout): bool
    {
        $fh = fopen($filein, 'rb');
        $fh2 = fopen($fileout, 'wb');
        stream_filter_append($fh2, 'convert.base64-decode');

        while (!feof($fh)) {
            $chunk = fgets($fh);
            if ($chunk === false) {
                break;
            }
            fwrite($fh2, $chunk);
        }
        fclose($fh);
        fclose($fh2);

        return true;
    }

    private function fastGunzip(string $in, string $out): bool
    {
        if (!file_exists($in) || !is_readable($in)) {
            return false;
        }
        if ((!file_exists($out) && !is_writable(dirname($out)) || (file_exists($out) && !is_writable($out)))) {
            return false;
        }

        $in_file = gzopen($in, "rb");
        $out_file = fopen($out, "wb");

        while (!gzeof($in_file)) {
            $buffer = gzread($in_file, 4096);
            fwrite($out_file, $buffer, 4096);
        }

        gzclose($in_file);
        fclose($out_file);

        return true;
    }
}
