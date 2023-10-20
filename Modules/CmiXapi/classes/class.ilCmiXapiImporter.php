<?php

declare(strict_types=1);

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
 * Class ilCmiXapiImporter
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiImporter extends ilXmlImporter
{
    private array $_moduleProperties = [];

    public array $manifest = [];

    private \ilCmiXapiDataSet $_dataset;

    private ilObject $_cmixObj;

    private ?string $_newId = null;

    private string $_import_objId;

    private \ilImportMapping $_mapping;

    private ?string $_relWebDir = 'lm_data/lm_';

    private string $_relImportDir = '';

    private bool $_isSingleImport = false;

    private \ILIAS\DI\Container $dic;

    private \ILIAS\Filesystem\Filesystem $filesystemWeb;

    private \ILIAS\Filesystem\Filesystem $filesystemTemp;
    /**
     * ilCmiXapiImporter constructor.
     */
    public function __construct()
    {
        parent::__construct();
        global $DIC;
        $this->dic = $DIC;
        $this->filesystemWeb = $DIC->filesystem()->web();
        $this->filesystemTemp = $DIC->filesystem()->temp();
        $this->_dataset = new ilCmiXapiDataSet();
        $this->_dataset->_cmixSettingsProperties['Title'] = '';
        $this->_dataset->_cmixSettingsProperties['Description'] = '';
        //todo: at the moment restricted to one module in xml file, extend?
    }

    /**
     * Init the object creation from import
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        $this->_import_objId = $a_id;
        $this->_mapping = $a_mapping;

        if ($this->_newId = $a_mapping->getMapping('Services/Container', 'objs', (string) $this->_import_objId)) {
            // container content
            $this->prepareContainerObject();
            $this->getImportDirectoryContainer();
        } else {
            // single object
            $this->prepareSingleObject();
            $this->getImportDirectorySingle();
            $this->_isSingleImport = true;
        }
        $this->prepareLocalSourceStorage();
        $this->parseXmlFileProperties();
        $this->updateNewObj();
    }

    /**
     * Builds the CmiXapi Object
     */
    private function prepareSingleObject(): self
    {
        // create new cmix object
        $this->_cmixObj = new ilObjCmiXapi();
        // set type of questionpool object
        $this->_cmixObj->setType('cmix');
        // set title of questionpool object to "dummy"
        $this->_cmixObj->setTitle("dummy");
        // set description of questionpool object
        $this->_cmixObj->setDescription("test import");
        // create the questionpool class in the ILIAS database (object_data table)
        $this->_cmixObj->create(true);
        $this->_newId = (string) $this->_cmixObj->getId();
        $this->_mapping->addMapping('Modules/CmiXapi', 'cmix', (string) $this->_import_objId, (string) $this->_newId);
        //$this->getImport();
        $this->_cmixObj->update();

        return $this;
    }

    /**
     * Builds the CmiXapi Object
     */
    private function prepareContainerObject(): void
    {
        $this->_newId = $this->_mapping->getMapping('Services/Container', 'objs', (string) $this->_import_objId);
        if (!is_null($this->_newId) && $this->_newId != "") {
            // container content
            $this->_cmixObj = ilObjectFactory::getInstanceByObjId((int) $this->_newId, false);
            //$_SESSION['tst_import_subdir'] = $this->getImportPackageName();
            $this->_cmixObj->save(); // this generates test id first time
            //var_dump([$this->getImportDirectory(), $this->_import_dirname]); exit;
            $this->_mapping->addMapping("Modules/CmiXapi", "cmix", (string) $this->_import_objId, $this->_newId);
        }
        $this->_cmixObj->save();
        $this->_cmixObj->update();
    }

    /**
     * Creates a folder in the data directory of the document root
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    private function prepareLocalSourceStorage(): self
    {
        if (true === $this->filesystemTemp->has($this->_relImportDir . '/content.zip')) {
            //            $this->_hasContent = true;
            $this->_relWebDir = $this->_relWebDir . $this->_cmixObj->getId();
            if (false === $this->filesystemWeb->has($this->_relWebDir)) {
                $this->filesystemWeb->createDir($this->_relWebDir);
                $this->filesystemWeb->put($this->_relWebDir . '/content.zip', $this->filesystemTemp->read($this->_relImportDir . '/content.zip'));
                $webDataDir = ilFileUtils::getWebspaceDir();
                ilFileUtils::unzip($webDataDir . "/" . $this->_relWebDir . "/content.zip");
                $this->filesystemWeb->delete($this->_relWebDir . '/content.zip');
            }
        }
        return $this;
    }

    /**
      * Parse xml file and set properties
      * @return $this
      * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
      * @throws \ILIAS\Filesystem\Exception\IOException
      */
    private function parseXmlFileProperties(): self
    {
        $xmlRoot = null;
        $xml = $this->filesystemTemp->readStream($this->_relImportDir . '/properties.xml');
        if ($xml != false) {
            $use_internal_errors = libxml_use_internal_errors(true);
            $xmlRoot = simplexml_load_string((string) $xml);
            libxml_use_internal_errors($use_internal_errors);
        }
        foreach ($this->_dataset->_cmixSettingsProperties as $key => $property) {
            $this->_moduleProperties[$key] = trim($xmlRoot->$key->__toString());
        }
        return $this;
    }

    /**
     * Finalize the new CmiXapi Object
     * @return void
     */
    private function updateNewObj(): void
    {
        $this->_cmixObj->setTitle($this->_moduleProperties['Title'] . " " . $this->dic->language()->txt("copy_of_suffix"));
        $this->_cmixObj->setDescription($this->_moduleProperties['Description']);
        $this->_cmixObj->update();

        if ($this->_moduleProperties['LrsTypeId']) {
            $this->_cmixObj->setLrsTypeId((int) $this->_moduleProperties['LrsTypeId']);
            $this->_cmixObj->setLrsType(new ilCmiXapiLrsType((int) $this->_moduleProperties['LrsTypeId']));
        }
        $this->_cmixObj->setContentType((string) $this->_moduleProperties['ContentType']);
        $this->_cmixObj->setSourceType((string) $this->_moduleProperties['SourceType']);
        $this->_cmixObj->setActivityId((string) $this->_moduleProperties['ActivityId']);
        $this->_cmixObj->setInstructions((string) $this->_moduleProperties['Instructions']);
        // $this->_cmixObj->setOfflineStatus($this->_moduleProperties['OfflineStatus']);
        $this->_cmixObj->setLaunchUrl((string) $this->_moduleProperties['LaunchUrl']);
        $this->_cmixObj->setAuthFetchUrlEnabled((bool) $this->_moduleProperties['AuthFetchUrl']);
        $this->_cmixObj->setLaunchMethod((string) $this->_moduleProperties['LaunchMethod']);
        $this->_cmixObj->setLaunchMode((string) $this->_moduleProperties['LaunchMode']);
        $this->_cmixObj->setMasteryScore((float) $this->_moduleProperties['MasteryScore']);
        $this->_cmixObj->setKeepLpStatusEnabled((bool) $this->_moduleProperties['KeepLp']);
        $this->_cmixObj->setPrivacyIdent((int) $this->_moduleProperties['PrivacyIdent']);
        $this->_cmixObj->setPrivacyName((int) $this->_moduleProperties['PrivacyName']);
        $this->_cmixObj->setUserPrivacyComment((string) $this->_moduleProperties['UsrPrivacyComment']);
        $this->_cmixObj->setStatementsReportEnabled((bool) $this->_moduleProperties['ShowStatements']);
        $this->_cmixObj->setXmlManifest((string) $this->_moduleProperties['XmlManifest']);
        $this->_cmixObj->setVersion((int) $this->_moduleProperties['Version']);
        $this->_cmixObj->setHighscoreEnabled((bool) $this->_moduleProperties['HighscoreEnabled']);
        $this->_cmixObj->setHighscoreAchievedTS((bool) $this->_moduleProperties['HighscoreAchievedTs']);
        $this->_cmixObj->setHighscorePercentage((bool) $this->_moduleProperties['HighscorePercentage']);
        $this->_cmixObj->setHighscoreWtime((bool) $this->_moduleProperties['HighscoreWtime']);
        $this->_cmixObj->setHighscoreOwnTable((bool) $this->_moduleProperties['HighscoreOwnTable']);
        $this->_cmixObj->setHighscoreTopTable((bool) $this->_moduleProperties['HighscoreTopTable']);
        $this->_cmixObj->setHighscoreTopNum((int) $this->_moduleProperties['HighscoreTopNum']);
        $this->_cmixObj->setBypassProxyEnabled((bool) $this->_moduleProperties['BypassProxy']);
        $this->_cmixObj->setOnlyMoveon((bool) $this->_moduleProperties['OnlyMoveon']);
        $this->_cmixObj->setAchieved((bool) $this->_moduleProperties['Achieved']);
        $this->_cmixObj->setAnswered((bool) $this->_moduleProperties['Answered']);
        $this->_cmixObj->setCompleted((bool) $this->_moduleProperties['Completed']);
        $this->_cmixObj->setFailed((bool) $this->_moduleProperties['Failed']);
        $this->_cmixObj->setInitialized((bool) $this->_moduleProperties['Initialized']);
        $this->_cmixObj->setPassed((bool) $this->_moduleProperties['Passed']);
        $this->_cmixObj->setProgressed((bool) $this->_moduleProperties['Progressed']);
        $this->_cmixObj->setSatisfied((bool) $this->_moduleProperties['Satisfied']);
        $this->_cmixObj->setTerminated((bool) $this->_moduleProperties['Terminated']);
        $this->_cmixObj->setHideData((bool) $this->_moduleProperties['HideData']);
        $this->_cmixObj->setTimestamp((bool) $this->_moduleProperties['Timestamp']);
        $this->_cmixObj->setDuration((bool) $this->_moduleProperties['Duration']);
        $this->_cmixObj->setNoSubstatements((bool) $this->_moduleProperties['NoSubstatements']);
        $this->_cmixObj->setPublisherId((string) $this->_moduleProperties['PublisherId']);
        //        $this->_cmixObj->setAnonymousHomepage($this->_moduleProperties['AnonymousHomepage']);
        $this->_cmixObj->setMoveOn((string) $this->_moduleProperties['MoveOn']);
        $this->_cmixObj->setLaunchParameters((string) $this->_moduleProperties['LaunchParameters']);
        $this->_cmixObj->setEntitlementKey((string) $this->_moduleProperties['EntitlementKey']);
        $this->_cmixObj->setSwitchToReviewEnabled((bool) $this->_moduleProperties['SwitchToReview']);
        $this->_cmixObj->save();
        $this->_cmixObj->updateMetaData();

    }

    /**
     * Delete the import directory
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    private function deleteImportDirectiry(): self
    {
        $this->filesystemTemp->delete($this->_relImportDir);
        return $this;
    }

    /**
     * Gets the relative path to the Filesystem::temp Folder
     * @return $this
     */
    private function getImportDirectorySingle(): self
    {
        $importTempDir = $this->getImportDirectory();
        $dirArr = array_reverse(explode('/', $importTempDir));
        $this->_relImportDir = $dirArr[1] . '/' . $dirArr[0];
        return $this;
    }

    /**
     * Gets the relative path to the Filesystem::temp Folder
     * @return $this
     */
    private function getImportDirectoryContainer(): self
    {
        $importTempDir = $this->getImportDirectory();
        $dirArr = array_reverse(explode('/', $importTempDir));
        $this->_relImportDir = $dirArr[3] . '/' . $dirArr[2] . '/' . $dirArr[1] . '/' . $dirArr[0];

        return $this;
    }

    /**  */
    public function init(): void
    {
    }

    /**
     * if single import then deleteImportDirectiry
     */
    public function __destruct()
    {
        if ($this->_isSingleImport) {
            $this->deleteImportDirectiry();
        }
    }
}
