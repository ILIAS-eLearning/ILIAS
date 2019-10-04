<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilAbstractSoapMethod;
use ilOrgUnitSOAPServicesPlugin;

/**
 * Class Base
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class Base extends ilAbstractSoapMethod
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
     * @throws \ilSoapPluginException
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
     * @throws \ilSoapPluginException
     */
    protected function error($message)
    {
        throw new \ilSoapPluginException($message);
    }


    /**
     * @param $session_id
     *
     * @throws \ilSoapPluginException
     */
    private function init($session_id)
    {
        $this->initIliasAndCheckSession($session_id); // Throws exception if session is not valid
    }
}
