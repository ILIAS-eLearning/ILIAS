<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLanguageDetectorFactory
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup Services/Language
 */
class ilLanguageDetectorFactory
{
    const DEFAULT_DETECTOR = 1;
    const HTTP_REQUEST_DETECTOR = 2;

    /**
     * @var ilIniFile
     */
    protected $client_ini;

    /**
     * @var array
     */
    protected $request_information = array();

    /**
     * @var $ilSettings
     */
    protected $settings;

    /**
     *
     */
    public function __construct()
    {
        global $DIC;

        $this->client_ini = $DIC->clientIni();
        $this->request_information = $_SERVER;
        $this->settings = $DIC->settings();
    }

    /**
     * @return ilLanguageDetector[]
     */
    public function getValidInstances()
    {
        $detectors = array(
            $this->createDetectorByType(self::DEFAULT_DETECTOR)
        );

        if (
            $this->settings->get('lang_detection') &&
            ilContext::usesHTTP()
        ) {
            $detectors[] = $this->createDetectorByType(self::HTTP_REQUEST_DETECTOR);
        }
        
        return $detectors;
    }

    /**
     * @param int $type
     * @throws ilLanguageException
     * @return ilLanguageDetector
     */
    public function createDetectorByType($type)
    {
        switch ($type) {
            case self::HTTP_REQUEST_DETECTOR:
                require_once 'Services/Language/classes/class.ilHttpRequestsLanguageDetector.php';
                return new ilHttpRequestsLanguageDetector($this->request_information['HTTP_ACCEPT_LANGUAGE']);

            case self::DEFAULT_DETECTOR:
                require_once 'Services/Language/classes/class.ilDefaultLanguageDetector.php';
                return new ilDefaultLanguageDetector($this->client_ini);
        }

        require_once 'Services/Language/exceptions/class.ilLanguageException.php';
        throw new ilLanguageException(__METHOD__ . sprintf('Cannot create language detector instance for type %s!', $type));
    }
}
