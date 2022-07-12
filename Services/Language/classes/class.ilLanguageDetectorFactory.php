<?php declare(strict_types=1);

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
 ********************************************************************
 */
 
/**
 * Class ilLanguageDetectorFactory
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup Services/Language
 */
class ilLanguageDetectorFactory
{
    private const DEFAULT_DETECTOR = 1;
    private const HTTP_REQUEST_DETECTOR = 2;

    protected ilIniFile $client_ini;
    protected array $request_information = array();
    protected ilSetting $settings;

    public function __construct()
    {
        global $DIC;

        $this->client_ini = $DIC->clientIni();
        $this->request_information = $_SERVER;
        $this->settings = $DIC->settings();
    }

    /**
     * @throws ilLanguageException
     */
    public function getValidInstances() : array
    {
        $detectors = array(
            $this->createDetectorByType(self::DEFAULT_DETECTOR)
        );

        if ($this->settings->get("lang_detection") &&
            ilContext::usesHTTP()
        ) {
            $detectors[] = $this->createDetectorByType(self::HTTP_REQUEST_DETECTOR);
        }
        
        return $detectors;
    }

    /**
     * @throws ilLanguageException
     */
    public function createDetectorByType(int $type)
    {
        switch ($type) {
            case self::HTTP_REQUEST_DETECTOR:
                require_once "Services/Language/classes/class.ilHttpRequestsLanguageDetector.php";
                return new ilHttpRequestsLanguageDetector($this->request_information["HTTP_ACCEPT_LANGUAGE"]);

            case self::DEFAULT_DETECTOR:
                require_once "Services/Language/classes/class.ilDefaultLanguageDetector.php";
                return new ilDefaultLanguageDetector($this->client_ini);
        }

        require_once "Services/Language/exceptions/class.ilLanguageException.php";
        throw new ilLanguageException(__METHOD__ . sprintf("Cannot create language detector instance for type %s!", $type));
    }
}
