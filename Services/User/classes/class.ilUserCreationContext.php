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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilUserCreationContext
{
    public const CONTEXT_REGISTRATION = 1;
    public const CONTEXT_ADMINISTRATION = 2;
    public const CONTEXT_SOAP = 3;
    public const CONTEXT_LDAP = 4;
    public const CONTEXT_SHIB = 6;

    private static ?ilUserCreationContext $instance = null;
    private array $contexts = array(); // Missing array type.
    
    protected function __construct()
    {
    }
    
    public static function getInstance() : self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getValidContexts() : array // Missing array type.
    {
        return array(
            self::CONTEXT_REGISTRATION,
            self::CONTEXT_ADMINISTRATION,
            self::CONTEXT_SOAP,
            self::CONTEXT_LDAP,
            self::CONTEXT_SHIB
        );
    }
    
    public function getCurrentContexts() : array // Missing array type.
    {
        return $this->contexts;
    }
    
    public function addContext(int $a_context) : void
    {
        if (in_array($a_context, $this->getValidContexts())) {
            if (!in_array($a_context, $this->getCurrentContexts())) {
                $this->contexts[] = $a_context;
            }
        }
    }
}
