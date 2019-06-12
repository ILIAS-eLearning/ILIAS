<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Create PDF certificates
*
* Base class to create PDF certificates using XML-FO XML transformations
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup Services
*/
class ilCertificate
{
    /**
    * The reference to the ILIAS control class
    *
    * @var ilCtrl
    */
    protected $ctrl;

    /**
    * The reference to the ILIAS tree class
    *
    * @var ilTree
    */
    protected $tree;

    /**
    * The reference to the ILIAS class
    *
    * @var ILIAS
    */
    protected $ilias;

    /**
    * The reference to the Language class
    *
    * @var ilLanguage
    */
    protected $lng;

    /**
    * The certificate adapter
    *
    * @var ilCertificateAdapter
    */
    protected $adapter;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilLog
     */
    protected $log;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var bool
     */
    protected static $is_active;

    /**
     * @var ilCertificateTemplateRepository|null
     */
    private $templateRepository;

    /**
     * @var ilCertificatePlaceholderDescription
     */
    private $placeholderDescriptionObject;

    /**
     * @var integer
     */
    private $objectId;

    /**
     * @var ilUserCertificateRepository|null
     */
    private $certificateRepository;

    /**
     * @var string
     */
    private $certificatePath;

    /**
     * @var ilCertificatePlaceholderValues
     */
    private $placeholderValuesObject;

    /**
     * ilCertificate constructor
     * @param ilCertificateAdapter $adapter The certificate adapter needed to construct the certificate
     * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
     * @param ilCertificatePlaceholderValues $placeholderValuesObject
     * @param $objectId - Object ID of the current component (e.g. course, test, exercise)
     * @param $certificatePath - Path to certificate data like background images etc.
     * @param ilCertificateTemplateRepository|null $templateRepository
     * @param ilUserCertificateRepository|null $certificateRepository
     */
    public function __construct(
        ilCertificateAdapter $adapter,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilCertificatePlaceholderValues $placeholderValuesObject,
        $objectId,
        $certificatePath,
        ilCertificateTemplateRepository $templateRepository = null,
        ilUserCertificateRepository $certificateRepository = null
    ) {
        global $DIC;

        $this->lng      = $DIC['lng'];
        $this->ctrl     = $DIC['ilCtrl'];
        $this->ilias    = $DIC['ilias'];
        $this->tree     = $DIC['tree'];
        $this->settings = $DIC['ilSetting'];
        $this->log      = $DIC['ilLog'];
        $this->db       = $DIC['ilDB'];

        $this->adapter = $adapter;

        $this->placeholderDescriptionObject = $placeholderDescriptionObject;

        $this->placeholderValuesObject = $placeholderValuesObject;

        $this->objectId = $objectId;

        $this->certificatePath = $certificatePath;

        $logger = $DIC->logger()->cert();

        if ($templateRepository === null) {
            $templateRepository = new ilCertificateTemplateRepository($DIC->database(), $logger);
        }
        $this->templateRepository = $templateRepository;

        if ($certificateRepository === null) {
            $certificateRepository = new ilUserCertificateRepository($DIC->database(), $logger);
        }
        $this->certificateRepository = $certificateRepository;
    }

    /**
    * Returns the filesystem path of the background image
    * @param  bool $asRelative
    * @return string The filesystem path of the background image
    */
    public function getBackgroundImageDirectory($asRelative = false, $backgroundImagePath = '')
    {
        if ($asRelative) {
            return str_replace(
                array(CLIENT_WEB_DIR, '//'),
                array('[CLIENT_WEB_DIR]', '/'),
                $backgroundImagePath
            );
        }

        return $this->certificatePath;
    }

    /**
    * Returns the filename of the background image
    *
    * @return string The filename of the background image
    */
    public function getBackgroundImageName()
    {
        return "background.jpg";
    }

    /**
    * Returns the filesystem path of the background image thumbnail
    *
    * @return string The filesystem path of the background image thumbnail
    */
    public function getBackgroundImageThumbPath()
    {
        return CLIENT_WEB_DIR . $this->certificatePath . $this->getBackgroundImageName() . ".thumb.jpg";
    }

    /**
    * Returns the filesystem path of the background image temp file during upload
    *
    * @return string The filesystem path of the background image temp file
    */
    public function getBackgroundImageTempfilePath()
    {
        return CLIENT_WEB_DIR . $this->certificatePath . "background_upload.tmp";
    }

    /**
    * Deletes the background image of a certificate
    *
    * @return boolean TRUE if the process succeeds
    */
    public function deleteBackgroundImage($version)
    {
        $result = true;
        if (file_exists($this->getBackgroundImageThumbPath())) {
            $result = $result & unlink($this->getBackgroundImageThumbPath());
        }

        $filename = $this->getBackgroundImageDirectory() . 'background_' . $version . '.jpg';
        if (file_exists($filename)) {
            $result = $result & unlink($filename);
        }

        if (file_exists($this->getBackgroundImageTempfilePath())) {
            $result = $result & unlink($this->getBackgroundImageTempfilePath());
        }

        return $result;
    }

    /**
    * Checks the status of the certificate
    *
    * @return boolean Returns TRUE if the certificate is complete, FALSE otherwise
    */
    public function isComplete()
    {
        if (self::isActive()) {
            if ($this->objectId && !self::isObjectActive($this->objectId)) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
    * Gets the adapter
    *
    * @return ilCertificateAdapter Adapter
    */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /***************************************
    /* BULK CERTIFICATE PROCESSING METHODS *
    /***************************************

    /**
    * Creates a directory for a zip archive containing multiple certificates
    *
    * @return string The created archive directory
    */
    public function createArchiveDirectory()
    {
        $type = ilObject::_lookupType($this->objectId);
        $certificateId = $this->objectId;

        $dir = CLIENT_WEB_DIR . $this->certificatePath . time() . "__" . IL_INST_ID . "__" . $type . "__" . $certificateId . "__certificate/";
        ilUtil::makeDirParents($dir);
        return $dir;
    }

    /**
    * Adds PDF data as a file to a given directory
    *
    * @param binary $pdfdata Binary PDF data
    * @param string $dir Directory to contain the PDF data
    * @param string $filename The filename to save the PDF data
    */
    public function addPDFtoArchiveDirectory($pdfdata, $dir, $filename)
    {
        $fh = fopen($dir . $filename, "wb");
        fwrite($fh, $pdfdata);
        fclose($fh);
    }

    /**
    * Create a ZIP file from a directory with certificates
    *
    * @param string $dir Directory containing the certificates
    * @param boolean $deliver TRUE to deliver the ZIP file, FALSE to return the filename only
    * @return string The created ZIP archive path
    */
    public function zipCertificatesInArchiveDirectory($dir, $deliver = true)
    {
        $zipfile = time() . "__" . IL_INST_ID . "__" . $this->getAdapter()->getAdapterType() . "__" . $this->getAdapter()->getCertificateId() . "__certificates.zip";
        $zipfilePath = CLIENT_WEB_DIR . $this->certificatePath . $zipfile;
        ilUtil::zip($dir, $zipfilePath);
        ilUtil::delDir($dir);
        if ($deliver) {
            ilUtil::deliverFile($zipfilePath, $zipfile, "application/zip", false, true);
        }
        return $zipfilePath;
    }

    public static function isActive()
    {
        if (self::$is_active === null) {
            // basic admin setting active?
            $certificate_active = new ilSetting("certificate");
            $certificate_active = (bool)$certificate_active->get("active");

            // java/rtpc-server active?
            if ($certificate_active) {
                include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
                $certificate_active = ilRPCServerSettings::getInstance()->isEnabled();
            }

            self::$is_active = (bool)$certificate_active;
        }

        return self::$is_active;
    }

    /**
     * @param int $a_obj_id
     * @return bool
     */
    public static function isObjectActive($a_obj_id)
    {
        $chk = self::areObjectsActive(array($a_obj_id));
        return $chk[$a_obj_id];
    }

    /**
     * @param array $a_obj_ids
     * @return array
     */
    public static function areObjectsActive(array $a_obj_ids)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $all = array();
        foreach ($a_obj_ids as $id) {
            $all[$id] = false;
        }

        $set = $ilDB->query("SELECT obj_id FROM il_certificate WHERE " . $ilDB->in("obj_id", $a_obj_ids, "", "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[$row["obj_id"]] = true;
        }

        return $all;
    }
}
