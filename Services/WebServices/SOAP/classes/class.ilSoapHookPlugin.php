<?php

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Class ilSoapHookPlugin
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class ilSoapHookPlugin extends ilPlugin
{
    /**
     * Get all soap methods which will be made available to the SOAP webservice
     *
     * @return ilSoapMethod[]
     */
    abstract public function getSoapMethods(): array;

    /**
     * Get any (new) types which the SOAP methods may use.
     * These types are registered in WSDL.
     *
     * @see ilNusoapUserAdministrationAdapter::registerMethods()
     *
     * @return ilWsdlType[]
     */
    abstract public function getWsdlTypes(): array;
}
