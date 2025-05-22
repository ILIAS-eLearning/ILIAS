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
 *********************************************************************/

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
