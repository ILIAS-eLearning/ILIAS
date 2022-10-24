<?php

/**
 * Interface ilSoapMethod
 *
 * Describes a soap method which can be added to the ILIAS SOAP webservice
 * by a plugin of the SoapHook plugin slot
 */
interface ilSoapMethod
{
    /**
     * Get the name of the method. Used as endpoint for SOAP requests.
     * Note that this name must be unique in combination with the service namespace.
     * @return string
     */
    public function getName(): string;

    /**
     * Get the input parameters. Array keys must correspond to parameter names and values must correspond
     * to a valid SOAP data-type
     * @return array
     * @see ilNusoapUserAdministrationAdapter::registerMethods() for examples
     */
    public function getInputParams(): array;

    /**
     * Get the output parameters in the same format as the input parameters
     * @return array
     */
    public function getOutputParams(): array;

    /**
     * Get the namespace of the service where this method belongs to
     * @return string
     */
    public function getServiceNamespace(): string;

    /**
     * Get the service style, e.g. 'rpc'
     * @return string
     */
    public function getServiceStyle(): string;

    /**
     * Get the service use, e.g. 'encoded'
     * @return string
     */
    public function getServiceUse(): string;

    /**
     * Get the documentation of this method
     * @return string
     */
    public function getDocumentation(): string;

    /**
     * Execute the business logic for this SOAP method (when a SOAP request hits the endpoint defined by the name).
     * Note: This Method must return the data in the format specified by getOutputParams().
     * @param array $params Key/Value pair of parameters defined by getInputParams()
     * @return mixed
     */
    public function execute(array $params);
}
