<?php
require_once(__DIR__ . '/ilSoapMethod.php');
require_once(__DIR__ . '/class.ilSoapPluginException.php');
require_once('./webservice/soap/classes/class.ilSoapAdministration.php');

/**
 * Class ilAbstractSoapMethod
 *
 * Base class for soap methods of SoapHook plugins.
 * Throw a ilSoapPluginException in your business logic in case of errors. The plugin hook catches these exceptions
 * and returns the exception messages to the SOAP caller.
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class ilAbstractSoapMethod extends ilSoapAdministration implements ilSoapMethod
{
    public function __construct()
    {
        parent::__construct(true);
    }

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
     * Overwrites the __raiseError method and transforms any raised errors into ilPluginExceptions.
     * Note: These exceptions will be caught by the plugin slot and and the exception message
     * is returned to the SOAP caller.
     *
     * @param string $a_message
     * @param int $a_code
     * @throws ilSoapPluginException
     * @return void
     */
    public function __raiseError($a_message, $a_code)
    {
        throw new ilSoapPluginException($a_message, $a_code);
    }
}
