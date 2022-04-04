<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

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
    protected ilDBInterface $db;
    protected array $listener;
    protected ilLogger $logger;
    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->component_repository = $DIC["component.repository"];
        $this->component_factory = $DIC["component.factory"];
        $this->initListeners();

        $this->logger = \ilLoggerFactory::getLogger('evnt');
    }

    protected function initListeners() : void
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
    public function raise(
        string $a_component,
        string $a_event,
        array $a_parameter = []
    ) : void {
        $this->logger->debug(sprintf(
            "Received event '%s' from component '%s'.",
            $a_event,
            $a_component
        ));

        $parameter_formatter = static function ($value) use (&$parameter_formatter) {
            if (is_object($value)) {
                return get_class($value);
            }

            if (is_array($value)) {
                return array_map(
                    $parameter_formatter,
                    $value
                );
            }

            return $value;
        };

        $this->logger->debug('Event data: ' . var_export(array_map(
            $parameter_formatter,
            $a_parameter
        ), true));

        $this->logger->debug("Started event propagation for event listeners ...");

        if (is_array($this->listener[$a_component] ?? null)) {
            foreach ($this->listener[$a_component] as $listener) {
                // Allow listeners like Services/WebServices/ECS
                $last_slash = strripos($listener, '/');
                $comp = substr($listener, 0, $last_slash);

                // any kind of plugins with events in their plugin.xml
                if ($comp == 'Plugins') {
                    $name = substr($listener, $last_slash + 1);


                    foreach ($this->component_repository->getPlugins() as $pl) {
                        if ($pl->getName() !== $name || !$pl->isActive()) {
                            continue;
                        }
                        $plugin = $this->component_factory->getPlugin($pl->getId());
                        $plugin->handleEvent($a_component, $a_event, $a_parameter);
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
        foreach ($this->component_factory->getActivePluginsInSlot("evhk") as $plugin) {
            $plugin->handleEvent($a_component, $a_event, $a_parameter);
        }

        $this->logger->debug("Finished event hook plugin handling, started event propagation for workflow engine ...");

        $workflow_engine = new ilWorkflowEngine();
        $workflow_engine->handleEvent($a_component, $a_event, $a_parameter);

        $this->logger->debug("Finished workflow engine handling.");
    }
}
