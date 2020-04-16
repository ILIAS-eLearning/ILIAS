<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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

include_once './Services/Xml/classes/class.ilSaxParser.php';
include_once 'Modules/File/classes/class.ilFileException.php';
include_once 'Services/Utilities/classes/class.ilFileUtils.php';

class ilFileXMLParser extends ilSaxParser
{
    public static $CONTENT_NOT_COMPRESSED = 0;
    public static $CONTENT_GZ_COMPRESSED = 1;
    public static $CONTENT_ZLIB_COMPRESSED = 2;
    public static $CONTENT_COPY = 4;
    // begin-patch fm
    public static $CONTENT_REST = 5;
    // end-patch fm
    /**
     * Exercise object which has been parsed
     *
     * @var ilObjFile
     */
    public $file;
    /**
     * this will be matched against the id in the xml
     * in case we want to update an exercise
     *
     * @var int
     */
    public $obj_id;
    /**
     * result of parsing and updating
     *
     * @var boolean
     */
    public $result;
    /**
     * Content compression mode, defaults to no compression
     *
     * @var int
     */
    public $mode;
    /**
     * file contents, base64 encoded
     *
     * @var string
     */
    //var $content;

    /**
     *    file of temporary file where we store the file content instead of in memory
     *
     * @var string
     */
    public $tmpFilename;
    /**
     * file contents, base64 encoded
     *
     * @var string
     */
    //var $content;

    /**
     * @var int
     */
    protected $version = null;
    /**
     * @var string
     */
    protected $action = null;
    /**
     * @var int
     */
    protected $rollback_version = null;
    /**
     * @var int
     */
    protected $rollback_user_id = null;
    /**
     * @var int
     */
    protected $max_version = null;
    /**
     * @var int
     */
    protected $date = null;
    /**
     * @var int
     */
    protected $usr_id = null;
    /**
     * @var array
     */
    protected $versions = [];


    /**
     * Constructor
     *
     * @param ilObjFile $file       existing file object
     * @param string    $a_xml_file xml data
     * @param int       $obj_id     obj id of exercise which is to be updated
     *
     * @access    public
     */
    public function __construct(&$file, $a_xml_data, $obj_id = -1, $mode = 0)
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
    public function setImportDirectory($a_val)
    {
        $this->importDirectory = $a_val;
    }


    /**
     * Get import directory
     *
     * @return    string    import directory
     */
    public function getImportDirectory()
    {
        return $this->importDirectory;
    }


    /**
     * set event handlers
     *
     * @param resource    reference to the xml parser
     *
     * @access    private
     */
    public function setHandlers($a_xml_parser)
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
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];

        global $DIC;
        $ilLog = $DIC['ilLog'];

        switch ($a_name) {
            case 'File':
                if (isset($a_attribs["obj_id"])) {
                    $read_obj_id = ilUtil::__extractId($a_attribs["obj_id"], IL_INST_ID);
                    if ($this->obj_id != -1 && (int) $read_obj_id != -1 && (int) $this->obj_id != (int) $read_obj_id) {
                        throw new ilFileException(
                            "Object IDs (xml $read_obj_id and argument " . $this->obj_id . ") do not match!",
                            ilFileException::$ID_MISMATCH
                        );
                    }
                }
                if (isset($a_attribs["type"])) {
                    $this->file->setFileType($a_attribs["type"]);
                }
                $this->file->setVersion($a_attribs["version"]); // Selected version
                $this->file->setMaxVersion($a_attribs["max_version"]);
                $this->file->setAction($a_attribs["action"]);
                $this->file->setRollbackVersion($a_attribs["rollback_version"]);
                $this->file->setRollbackUserId($a_attribs["rollback_user_id"]);
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
                $this->isReadingFile = true;
                $this->tmpFilename = ilUtil::ilTempnam();
                #echo $a_attribs["mode"];
                if (isset($a_attribs["mode"])) {
                    if ($a_attribs["mode"] == "GZIP") {
                        if (!function_exists("gzread")) {
                            throw new ilFileException("Deflating with gzip is not supported", ilFileException::$ID_DEFLATE_METHOD_MISMATCH);
                        }

                        $this->mode = ilFileXMLParser::$CONTENT_GZ_COMPRESSED;
                    } elseif ($a_attribs["mode"] == "ZLIB") {
                        if (!function_exists("gzuncompress")) {
                            throw new ilFileException("Deflating with zlib (compress/uncompress) is not supported", ilFileException::$ID_DEFLATE_METHOD_MISMATCH);
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
                    $this->version = $a_attribs["version"];
                    $this->max_version = $a_attribs["max_version"];
                    $this->date = $a_attribs["date"];
                    $this->usr_id = $a_attribs["usr_id"];
                    $this->action = $a_attribs["action"];
                    $this->rollback_version = $a_attribs["rollback_version"];
                    $this->rollback_user_id = $a_attribs["rollback_user_id"];
                } else {
                    // Old import files
                    //$this->version = $this->file->getVersion();
                    $this->version = 1;
                    $this->file->setVersion($this->version);
                }
        }
    }


    /**
     * handler for end of element
     *
     * @param resource $a_xml_parser xml parser
     * @param string   $a_name       element name
     */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        $this->cdata = trim($this->cdata);

        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . $this->cdata);

        switch ($a_name) {
            case 'File':
                $this->result = true;
                break;
            case 'Filename':
                if (strlen($this->cdata) == 0) {
                    throw new ilFileException("Filename ist missing!");
                }

                $this->file->setFilename(basename(self::normalizeRelativePath($this->cdata)));
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

                $GLOBALS['DIC']['ilLog']->write($this->mode);
                $this->isReadingFile = false;
                $baseDecodedFilename = ilUtil::ilTempnam();
                if ($this->mode == ilFileXMLParser::$CONTENT_COPY) {
                    $this->tmpFilename = $this->getImportDirectory() . "/" . self::normalizeRelativePath($this->cdata);
                } // begin-patch fm
                elseif ($this->mode == ilFileXMLParser::$CONTENT_REST) {
                    include_once './Services/WebServices/Rest/classes/class.ilRestFileStorage.php';
                    $storage = new ilRestFileStorage();
                    $this->tmpFilename = $storage->getStoredFilePath(self::normalizeRelativePath($this->cdata));
                    if (!ilFileUtils::fastBase64Decode($this->tmpFilename, $baseDecodedFilename)) {
                        throw new ilFileException("Base64-Decoding failed", ilFileException::$DECOMPRESSION_FAILED);
                    }
                    $this->tmpFilename = $baseDecodedFilename;
                } // end-patch fm
                else {
                    $this->tmpFilename = ilUtil::ilTempnam();
                    if (!ilFileUtils::fastBase64Decode($this->tmpFilename, $baseDecodedFilename)) {
                        throw new ilFileException("Base64-Decoding failed", ilFileException::$DECOMPRESSION_FAILED);
                    }
                    if ($this->mode == ilFileXMLParser::$CONTENT_GZ_COMPRESSED) {
                        if (!ilFileUtils::fastGunzip($baseDecodedFilename, $this->tmpFilename)) {
                            throw new ilFileException("Deflating with fastzunzip failed", ilFileException::$DECOMPRESSION_FAILED);
                        }
                        unlink($baseDecodedFilename);
                    } elseif ($this->mode == ilFileXMLParser::$CONTENT_ZLIB_COMPRESSED) {
                        if (!ilFileUtils::fastGunzip($baseDecodedFilename, $this->tmpFilename)) {
                            throw new ilFileException("Deflating with fastDecompress failed", ilFileException::$DECOMPRESSION_FAILED);
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
                        $ilLog = $DIC['ilLog'];

                        #$ilLog->write(__METHOD__.': Trying to detect mime type...');
                        include_once('./Services/Utilities/classes/class.ilFileUtils.php');
                        $this->file->setFileType(ilFileUtils::_lookupMimeType($this->tmpFilename));
                    }
                }

                $this->versions[] = [
                    "version" => $this->version,
                    "max_version" => $this->max_version,
                    "tmpFilename" => $this->tmpFilename,
                    "date" => $this->date,
                    "usr_id" => $this->usr_id,
                    "action" => $this->action,
                    "rollback_version" => $this->rollback_version,
                    "rollback_user_id" => $this->rollback_user_id,
                ];
                $this->version = null;
                $this->date = null;
                $this->usr_id = null;
                break;
        }

        $this->cdata = '';

        return;
    }


    /**
     * handler for character data
     *
     * @param resource $a_xml_parser xml parser
     * @param string   $a_data       character data
     */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        if ($a_data != "\n") {
            // begin-patch fm
            if ($this->isReadingFile && $this->mode != ilFileXMLParser::$CONTENT_COPY
                && $this->mode != ilFileXMLParser::$CONTENT_REST
            ) { // begin-patch fm
                $handle = fopen($this->tmpFilename, "a");
                fwrite($handle, $a_data);
                fclose($handle);
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
    public function setFileContents()
    {
        // Delete exists version 1 history
        ilHistory::_removeEntriesForObject($this->file->getId());

        foreach ($this->versions as $version) {
            if (!file_exists($version["tmpFilename"])) {
                ilLoggerFactory::getLogger('file')->error(__METHOD__ . ' "' . $version["tmpFilename"] . '" file not found.');

                continue;
            }

            if (filesize($version["tmpFilename"]) == 0) {
                continue;
            }

            $filedir = $this->file->getDirectory($version["version"]);

            if (!is_dir($filedir)) {
                $this->file->createDirectory();
                ilUtil::makeDir($filedir);
            }

            $filename = $filedir . "/" . $this->file->getFileName();

            if (file_exists($filename)) {
                unlink($filename);
            }

            ilFileUtils::rename($version["tmpFilename"], $filename);

            // Add version history
            // bugfix mantis 26236: add rollback info to version instead of max_version to ensure compatibility with older ilias versions
            if ($version["rollback_version"] != "" and $version["rollback_version"] != null
                and $version["rollback_user_id"] != "" and $version["rollback_user_id"] != null
            ) {
                ilHistory::_createEntry($this->file->getId(), $version["action"], basename($filename) . ","
                    . $version["version"] . "|"
                    . $version["rollback_version"] . "|"
                    . $version["rollback_user_id"] . ","
                    . $version["max_version"]);
            } else {
                if ($version["action"] != "" and $version["action"] != null) {
                    ilHistory::_createEntry($this->file->getId(), $version["action"], basename($filename) . ","
                        . $version["version"] . ","
                        . $version["max_version"]);
                } else {
                    ilHistory::_createEntry($this->file->getId(), "new_version", basename($filename) . ","
                        . $version["version"] . ","
                        . $version["max_version"]);
                }
            }
        }
    }


    /**
     * update file according to filename and version and create history entry
     * has to be called after (!) file save for new objects, since file storage will be initialised with obj id.
     *
     */
    public function updateFileContents()
    {
        if ($this->setFileContents()) {
            require_once("./Services/History/classes/class.ilHistory.php");
            if ($this->file->getRollbackVersion() != "" and $this->file->getRollbackVersion() != null
                and $this->file->getRollbackUserId() != "" and $this->file->getRollbackUserId() != null
            ) {
                ilHistory::_createEntry($this->file->getId(), $this->file->getAction(), $this->file->getFilename() . "," . $this->file->getVersion() . "," . $this->file->getMaxVersion()
                    . "|" . $this->file->getRollbackVersion() . "|" . $this->file->getRollbackUserId());
            } else {
                if ($this->file->getAction() != "" and $this->file->getAction() != null) {
                    ilHistory::_createEntry($this->file->getId(), $this->file->getAction(), $this->file->getFilename() . "," . $this->file->getVersion() . "," . $this->file->getMaxVersion());
                } else {
                    ilHistory::_createEntry($this->file->getId(), "replace", $this->file->getFilename() . "," . $this->file->getVersion() . "," . $this->file->getMaxVersion());
                }
            }
            $this->file->addNewsNotification("file_updated");
        }
    }


    /**
     * starts parsing an changes object by side effect.
     *
     * @return boolean true, if no errors happend.
     *
     * @throws ilFileException when obj id != - 1 and if it it does not match the id in the xml
     */
    public function start()
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
     * @param string $path
     *
     * @return string
     * @throws LogicException
     *
     */
    public static function normalizeRelativePath($path)
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
}
