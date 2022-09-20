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
 ********************************************************************
 */

namespace ILIAS\OrgUnit\Webservices\SOAP;

require_once('./webservice/soap/classes/class.ilSoapAdministration.php');
require_once('./Services/WebServices/SOAP/classes/class.ilSoapPluginException.php');

use ilOrgUnitSOAPServicesPlugin;
use ilSoapAdministration;
use ilSoapMethod;
use ilSoapPluginException;

/**
 * Class Base
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class Base extends ilSoapAdministration implements ilSoapMethod
{
    /**
     * @inheritdoc
     */
    public const TYPE_INT_ARRAY = 'tns:intArray';
    public const TYPE_STRING = 'xsd:string';
    public const TYPE_INT = 'xsd:int';
    public const TYPE_DOUBLE_ARRAY = 'tns:doubleArray';
    public const SID = 'sid';
    public const ORGU_REF_ID = 'orgu_ref_id';
    public const POSITION_ID = 'position_id';
    public const USR_IDS = 'usr_ids';
    public const USR_ID = 'usr_id';

    public function getServiceStyle(): string
    {
        return 'rpc';
    }

    public function getServiceUse(): string
    {
        return 'encoded';
    }

    /**
     * Use this method at the beginning of your execute() method to check if the provided session ID is valid.
     * This method wraps around ilSoapAdministration::initAuth() and ilSoapAdministration::initILIAS()
     * which are both required in order to handle the request.
     * @param string $session_id
     * @throws ilSoapPluginException
     */
    protected function initIliasAndCheckSession(string $session_id): void
    {
        $this->initAuth($session_id);
        $this->initIlias();
        if (!$this->checkSession($session_id)) {
            throw new ilSoapPluginException($this->getMessage());
        }
    }

    /**
     * Check that all input parameters are present when executing the soap method
     * @param array $params
     * @throws ilSoapPluginException
     */
    protected function checkParameters(array $params): void
    {
        for ($i = 0, $iMax = count($this->getInputParams()); $i < $iMax; $i++) {
            if (!isset($params[$i])) {
                $names = implode(', ', array_keys($this->getInputParams()));
                throw new ilSoapPluginException("Request is missing at least one of the following parameters: $names");
            }
        }
    }

    public function getServiceNamespace(): string
    {
        return 'urn:' . ilOrgUnitSOAPServicesPlugin::PLUGIN_NAME;
    }

    abstract protected function getAdditionalInputParams(): array;

    public function getInputParams(): array
    {
        return array_merge(
            array(
                self::SID => self::TYPE_STRING,
            ),
            $this->getAdditionalInputParams()
        );
    }

    abstract protected function run(array $params);

    public function execute(array $params)
    {
        $this->checkParameters($params);
        $session_id = (isset($params[0])) ? $params[0] : '';
        $this->init($session_id);

        // Check Permissions
        global $DIC;
        if (!$DIC->access()->checkAccess('write', '', \ilObjOrgUnit::getRootOrgRefId())) {
            $this->addError('Permission denied');
        }

        $clean_params = array();
        $i = 1;
        foreach ($this->getAdditionalInputParams() as $key => $type) {
            $clean_params[$key] = $params[$i];
            $i++;
        }

        return $this->run($clean_params);
    }

    /**
     * @throws \SoapFault
     */
    public function addError(string $message)
    {
        throw $this->raiseError($message, 'ERROR');
    }

    /**
     * @throws ilSoapPluginException
     */
    private function init(string $session_id): void
    {
        $this->initIliasAndCheckSession($session_id); // Throws exception if session is not valid
    }
}
