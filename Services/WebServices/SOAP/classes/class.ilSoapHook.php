<?php

/**
 * Class ilSoapHook
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilSoapHook
{

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    /**
     * @param ilPluginAdmin $plugin_admin
     */
    public function __construct(ilPluginAdmin $plugin_admin)
    {
        $this->plugin_admin = $plugin_admin;
    }

    /**
     * Get all registered soap methods over all SOAP plugins
     *
     * @return ilSoapMethod[]
     */
    public function getSoapMethods()
    {
        static $methods = null;
        if ($methods !== null) {
            return $methods;
        }
        $methods = array();
        $plugin_names = $this->plugin_admin->getActivePluginsForSlot(IL_COMP_SERVICE, 'WebServices', 'soaphk');
        foreach ($plugin_names as $plugin_name) {
            /** @var ilSoapHookPlugin $instance */
            $instance = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, 'WebServices', 'soaphk', $plugin_name);
            foreach ($instance->getSoapMethods() as $method) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * Get all registered WSDL types over all SOAP plugins
     *
     * @return ilWsdlType[]
     */
    public function getWsdlTypes()
    {
        static $types = null;
        if ($types !== null) {
            return $types;
        }
        $types = array();
        $plugin_names = $this->plugin_admin->getActivePluginsForSlot(IL_COMP_SERVICE, 'WebServices', 'soaphk');
        foreach ($plugin_names as $plugin_name) {
            /** @var ilSoapHookPlugin $instance */
            $instance = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, 'WebServices', 'soaphk', $plugin_name);
            foreach ($instance->getWsdlTypes() as $type) {
                $types[] = $type;
            }
        }
        return $types;
    }


    /**
     * Get a registered soap method by name
     *
     * @param string $name
     * @return ilSoapMethod|null
     */
    public function getMethodByName($name)
    {
        return array_pop(array_filter($this->getSoapMethods(), function ($method) use ($name) {
            /** @var ilSoapMethod $method */
            return ($method->getName() == $name);
        }));
    }
}
