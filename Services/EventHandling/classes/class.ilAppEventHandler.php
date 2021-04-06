<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Global event handler
 *
 * The event handler delegates application events (not gui events)
 * between components that trigger events and components that listen to events.
 * A component is a module or a service.
 *
 * The component that triggers an event calls the raise function of the event
 * handler through the global instance ilAppEventHandler:
 *
 * E.g. in ilObjUser->delete():
 * $ilAppEventHandler->raise("Services/User", "deleteUser", array("id" => ..., ...))
 *
 * A listener has to subscribe to the events of another component. This currently
 * is done here in the constructor, e.g. if the News service listens to the User
 * service, add a
 * $this->listener["Services/User"] = array("Services/News");
 * This information will go to xml files in the future.
 *
 * A component has to implement a listener class that implements
 * Services/EventHandling/interfaces/interface.ilAppEventListener.php
 *
 * The location must be <component>/classes/class.il<comp_name>AppEventListener.php,
 * e.g. ./Services/News/classes/class.ilNewsAppEventListener.php
 *
 * The class name must be il<comp_name>AppEventListener.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilAppEventHandler
{
    /**
     * @var ilDB
     */
    protected $db;

    protected $listener; // [array]

    /**
     * @var ilLogger
     */
    protected $logger;
    
    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->initListeners();

        $this->logger = \ilLoggerFactory::getLogger('evnt');
    }

    protected function initListeners()
    {
        $ilGlobalCache = ilGlobalCache::getInstance(ilGlobalCache::COMP_EVENTS);
        $cached_listeners = $ilGlobalCache->get('listeners');
        if (is_array($cached_listeners)) {
            $this->listener = $cached_listeners;

            return;
        }

        $ilDB = $this->db;

        $this->listener = array();

        $sql = "SELECT * FROM il_event_handling" .
            " WHERE type = " . $ilDB->quote("listen", "text");
        $res = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($res)) {
            $this->listener[$row["id"]][] = $row["component"];
        }

        $ilGlobalCache->set('listeners', $this->listener);
    }



    /**
    * Raise an event. The event is passed to all interested listeners.
    *
    * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
    * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public function raise($a_component, $a_event, $a_parameter = "")
    {
        $this->logger->debug(sprintf(
            "Received event '%s' from component '%s'.",
            $a_event,
            $a_component
        ));

        // lazy transforming event data to string
        $this->logger->debug(new class($a_parameter) {
            /**
             * @var mixed
             */
            protected $parameter;

            /**
             * @param mixed $parameter
             */
            public function __construct($parameter)
            {
                $this->parameter = $parameter;
            }

            /**
             * @return string
             */
            public function __toString()
            {
                if (is_object($this->parameter)) {
                    return 'Event data class: ' . get_class($this->parameter);
                }

                return 'Event data size: ' . sizeof($this->parameter);
                //return 'Event data: ' . print_r($this->parameter, 1);
            }
        });

        $this->logger->debug("Started event propagation for event listeners ...");

        if (is_array($this->listener[$a_component])) {
            foreach ($this->listener[$a_component] as $listener) {
                // Allow listeners like Services/WebServices/ECS
                $last_slash = strripos($listener, '/');
                $comp = substr($listener, 0, $last_slash);

                // any kind of plugins with events in their plugin.xml
                if ($comp == 'Plugins') {
                    $name = substr($listener, $last_slash + 1);

                    foreach (ilPluginAdmin::getActivePlugins() as $pdata) {
                        if ($pdata['name'] == $name) {
                            $plugin = ilPluginAdmin::getPluginObject(
                                $pdata['component_type'],
                                $pdata['component_name'],
                                $pdata['slot_id'],
                                $pdata['name']
                            );

                            $plugin->handleEvent($a_component, $a_event, $a_parameter);
                        }
                    }
                } else {
                    $class = 'il' . substr($listener, $last_slash + 1) . 'AppEventListener';
                    $file = "./" . $listener . "/classes/class." . $class . ".php";

                    // if file exists, call listener
                    if (is_file($file)) {
                        include_once($file);
                        call_user_func(array($class, 'handleEvent'), $a_component, $a_event, $a_parameter);
                    }
                }
            }
        }

        $this->logger->debug("Finished event listener handling, started event propagation for event hook plugins ...");

        // get all event hook plugins and forward the event to them
        $plugins = ilPluginAdmin::getActivePluginsForSlot("Services", "EventHandling", "evhk");
        foreach ($plugins as $pl) {
            $plugin = ilPluginAdmin::getPluginObject(
                "Services",
                "EventHandling",
                "evhk",
                $pl
            );
            $plugin->handleEvent($a_component, $a_event, $a_parameter);
        }

        $this->logger->debug("Finished event hook plugin handling, started event propagation for workflow engine ...");

        $workflow_engine = new ilWorkflowEngine(false);
        $workflow_engine->handleEvent($a_component, $a_event, $a_parameter);

        $this->logger->debug("Finished workflow engine handling.");
    }
}
