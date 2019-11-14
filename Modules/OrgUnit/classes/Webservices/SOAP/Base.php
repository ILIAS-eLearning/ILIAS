<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

require_once('./webservice/soap/classes/class.ilSoapAdministration.php');
require_once('./Services/WebServices/SOAP/classes/class.ilSoapPluginException.php');

use ilOrgUnitSOAPServicesPlugin;
use ilSoapAdministration;
use ilSoapMethod;
use ilSoapPluginException;

/**
 * Class Base
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class Base extends ilSoapAdministration implements ilSoapMethod
{

    /**
     * @inheritdoc
     */
    const TYPE_INT_ARRAY = 'tns:intArray';
    const TYPE_STRING = 'xsd:string';
    const TYPE_INT = 'xsd:int';
    const TYPE_DOUBLE_ARRAY = 'tns:doubleArray';
    const SID = 'sid';
    const ORGU_REF_ID = 'orgu_ref_id';
    const POSITION_ID = 'position_id';
    const USR_IDS = 'usr_ids';
    const USR_ID = 'usr_id';


    /**
     * @inheritdoc
     */
    public function getServiceStyle()
    {
        return 'rpc';
    }


    /**
     * @inheritdoc
     */
    public function getServiceUse()
    {
        return 'encoded';
    }


    /**
     * Use this method at the beginning of your execute() method to check if the provided session ID is valid.
     * This method wraps around ilSoapAdministration::initAuth() and ilSoapAdministration::initILIAS()
     * which are both required in order to handle the request.
     *
     * @param string $session_id
     *
     * @throws ilSoapPluginException
     */
    protected function initIliasAndCheckSession($session_id)
    {
        $this->initAuth($session_id);
        $this->initIlias();
        if (!$this->__checkSession($session_id)) {
            throw new ilSoapPluginException($this->__getMessage());
        }
    }


    /**
     * Check that all input parameters are present when executing the soap method
     *
     * @param array $params
     *
     * @throws ilSoapPluginException
     */
    protected function checkParameters(array $params)
    {
        for ($i = 0; $i < count($this->getInputParams()); $i++) {
            if (!isset($params[$i])) {
                $names = implode(', ', array_keys($this->getInputParams()));
                throw new ilSoapPluginException("Request is missing at least one of the following parameters: $names");
            }
        }
    }


    /**
     * @inheritdoc
     */
    public function getServiceNamespace()
    {
        return 'urn:' . ilOrgUnitSOAPServicesPlugin::PLUGIN_NAME;
    }


    /**
     * @return array
     */
    protected abstract function getAdditionalInputParams();


    /**
     * @inheritdoc
     */
    final public function getInputParams()
    {
        return array_merge(
            array(
                self::SID => self::TYPE_STRING,
            ), $this->getAdditionalInputParams()
        );
    }


    /**
     * @param array $params
     *
     * @return mixed
     */
    abstract protected function run(array $params);


    /**
     * @param array $params
     *
     * @return mixed
     * @throws ilSoapPluginException
     */
    public function execute(array $params)
    {
        $this->checkParameters($params);
        $session_id = (isset($params[0])) ? $params[0] : '';
        $this->init($session_id);

        $clean_params = array();
        $i = 1;
        foreach ($this->getAdditionalInputParams() as $key => $type) {
            $clean_params[$key] = $params[$i];
            $i++;
        }

        return $this->run($clean_params);
    }


    /**
     * @param $message
     *
     * @return \soap_fault|\SoapFault
     */
    protected function error($message)
    {
        return $this->__raiseError($message, 1);
    }


    /**
     * @param $session_id
     *
     * @throws ilSoapPluginException
     */
    private function init($session_id)
    {
        $this->initIliasAndCheckSession($session_id); // Throws exception if session is not valid
    }
}
