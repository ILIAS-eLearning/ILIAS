<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/classes/states/class.ilAbstractBuddySystemRelationState.php';

/**
 * Class ilBuddySystemRelationStateFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateFactory
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var array|null
     */
    protected static $valid_states;

    /**
     * @var array|null
     */
    protected static $state_option_array;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     *
     */
    protected function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get all valid states
     * @return ilBuddySystemRelationState[]
     */
    public function getValidStates()
    {
        if (null !== self::$valid_states) {
            return self::$valid_states;
        }

        $states = array();
        $iter = new DirectoryIterator(dirname(__FILE__));
        foreach ($iter as $file) {
            /**
             * @var $file SplFileInfo
             */
            if ($file->isDir()) {
                continue;
            }

            require_once $file->getFilename();
            $class = str_replace(array('class.', '.php'), '', $file->getBasename());
            $reflection = new ReflectionClass($class);
            if (
                !$reflection->isAbstract() &&
                $reflection->isSubclassOf('ilBuddySystemRelationState')
            ) {
                $states[] = new $class();
            }
        }

        return (self::$valid_states = $states);
    }

    /**
     * @return ilBuddySystemRelationState
     * @throws ilBuddySystemException
     */
    public function getInitialState()
    {
        foreach ($this->getValidStates() as $state) {
            if ($state->isInitial()) {
                return $state;
            }
        }

        throw new ilBuddySystemException("Could not find an initial state class");
    }

    /**
     * @param bool $with_initial_state
     * @return array
     */
    public function getStatesAsOptionArray($with_initial_state = false)
    {
        if (null !== self::$state_option_array[$with_initial_state]) {
            return self::$state_option_array[$with_initial_state];
        }

        $options = array();

        foreach ($this->getValidStates() as $state) {
            if ($with_initial_state || !$state->isInitial()) {
                $options[get_class($state)] = $this->lng->txt('buddy_bs_state_' . strtolower($state->getName()));
            }
        }

        return (self::$state_option_array[$with_initial_state] = $options);
    }

    /**
     * @param int                   $owner_id
     * @param ilBuddySystemRelation $relation
     * @return ilBuddySystemRelationStateButtonRenderer
     * @throws ilBuddySystemException
     */
    public function getRendererByOwnerAndRelation($owner_id, ilBuddySystemRelation $relation)
    {
        $state_class = get_class($relation->getState());
        $renderer_class = $state_class . 'ButtonRenderer';
        $renderer_path = "Services/Contact/BuddySystem/classes/states/renderer/class.{$renderer_class}.php";

        if (!file_exists($renderer_path)) {
            throw new ilBuddySystemException(sprintf("Could not find a renderer file for state: %s", $state_class));
        }

        require_once $renderer_path;
        if (!class_exists($renderer_class)) {
            throw new ilBuddySystemException(sprintf("Could not find a renderer class for state: %s in file: %s", $state_class, $renderer_path));
        }

        return new $renderer_class($owner_id, $relation);
    }
}
