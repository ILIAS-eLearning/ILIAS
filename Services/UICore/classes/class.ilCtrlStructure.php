<?php

/**
 * Class ilCtrlStructure
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlStructure implements ilCtrlStructureInterface
{
    /**
     * @var string regex for the validation of $_GET parameter names.
     *             (allows A-Z, a-z, 0-9, '_' and '-'.)
     */
    private const PARAM_NAME_REGEX = '/^[A-Za-z0-9_-]*$/';

    /**
     * Holds an instance of the database access layer.
     *
     * @var ilDBInterface
     */
    private ilDBInterface $database;

    /**
     * Holds parameter => value pairs mapped to the corresponding
     * or owning class.
     *
     * @var array<string, array>
     */
    private array $temporary_parameters = [];

    /**
     * Holds parameter names mapped to the corresponding or owning
     * class.
     *
     * @var array<string, string[]>
     */
    private array $permanent_parameters = [];

    /**
     * Holds the currently read control structure as array data.
     *
     * @var array<string, string|string[]>
     */
    private array $structure;

    /**
     * Holds a list of all baseclasses from services.
     *
     * @var string[]
     */
    private array $services;

    /**
     * Holds a list of all baseclasses from modules.
     *
     * @var string[]
     */
    private array $modules;

    /**
     * Holds the control structure mapped by other identifiers than
     * the classname (primarily CID).
     *
     * @var array<string, string|string[]>
     */
    private static array $mapped_structure = [];

    /**
     * Constructor
     *
     * @throws ilCtrlException if the artifact cannot be included.
     */
    public function __construct(ilDBInterface $database)
    {
        try {
            $this->structure = require ilCtrlStructureArtifactObjective::ARTIFACT_PATH;
        } catch (Throwable $exception) {
            throw new ilCtrlException("Could not include structure from artifact: " . $exception->getMessage());
        }

        $this->database = $database;
        $this->services = $this->fetchServices();
        $this->modules  = $this->fetchModules();
    }

    /**
     * @TODO: move implementation to artifact as well.
     *
     * @inheritDoc
     */
    public function isBaseClass(string $class_name) : bool
    {
        $class_name = $this->lowercase($class_name);
        return
            in_array($class_name, $this->modules, true) ||
            in_array($class_name, $this->services, true)
        ;
    }

    /**
     * @inheritDoc
     */
    public function getObjectNameByClass(string $class_name) : string
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_NAME, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getObjectNameByCid(string $cid) : string
    {
        return $this->getValueForKeyByCid(self::KEY_CLASS_NAME, $cid);
    }

    /**
     * @inheritDoc
     */
    public function getClassNameByCid(string $cid) : ?string
    {
        $class_name = $this->getValueForKeyByCid(
            self::KEY_CLASS_NAME,
            $cid
        );

        if (null === $class_name) {
            $x = 1;
        }

        return (null !== $class_name) ? $this->lowercase($class_name) : null;
    }

    /**
     * @inheritDoc
     */
    public function getClassCidByName(string $class_name) : ?string
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_CID, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getClassPathByName(string $class_name) : ?string
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_PATH, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getClassPathByCid(string $cid) : ?string
    {
        return $this->getValueForKeyByCid(self::KEY_CLASS_PATH, $cid);
    }

    /**
     * @inheritDoc
     */
    public function getCalledClassesByCid(string $cid) : array
    {
        return $this->getValueForKeyByCid(self::KEY_CALLED_CLASSES, $cid) ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getCalledClassesByName(string $class_name) : array
    {
        return $this->getValueForKeyByName(self::KEY_CALLED_CLASSES, $class_name) ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getCallingClassesByCid(string $cid) : array
    {
        return $this->getValueForKeyByCid(self::KEY_CALLING_CLASSES, $cid) ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getCallingClassesByName(string $class_name) : array
    {
        return $this->getValueForKeyByName(self::KEY_CALLING_CLASSES, $class_name) ?? [];
    }

    /**
     * @inheritDoc
     */
    public function saveParameterByClass(string $class_name, string $parameter_name) : void
    {
        if (!preg_match(self::PARAM_NAME_REGEX, $parameter_name)) {
            throw new ilCtrlException("Cannot save parameter '$parameter_name', as it contains invalid characters.");
        }

        $this->permanent_parameters[$this->lowercase($class_name)][] = $parameter_name;
    }

    /**
     * @inheritDoc
     */
    public function removeSavedParametersByClass(string $class_name) : void
    {
        $class_name = $this->lowercase($class_name);
        if (isset($this->permanent_parameters[$class_name])) {
            unset($this->permanent_parameters[$class_name]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getSavedParametersByClass(string $class_name) : ?array
    {
        return $this->permanent_parameters[$this->lowercase($class_name)] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setParameterByClass(string $class_name, string $parameter_name, mixed $value) : void
    {
        if (!preg_match(self::PARAM_NAME_REGEX, $parameter_name)) {
            throw new ilCtrlException("Cannot set parameter '$parameter_name', as it contains invalid characters.");
        }

        $this->temporary_parameters[$this->lowercase($class_name)][$parameter_name] = $value;
    }

    /**
     * @inheritDoc
     */
    public function removeParametersByClass(string $class_name) : void
    {
        $class_name = $this->lowercase($class_name);
        if (isset($this->temporary_parameters[$class_name])) {
            unset($this->temporary_parameters[$class_name]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getParametersByClass(string $class_name) : ?array
    {
        return $this->temporary_parameters[$this->lowercase($class_name)] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function removeSingleParameterByClass(string $class_name, string $parameter_name) : void
    {
        $class_name = $this->lowercase($class_name);
        if (isset($this->permanent_parameters[$class_name][$parameter_name])) {
            unset($this->permanent_parameters[$class_name][$parameter_name]);
        }

        if (isset($this->temporary_parameters[$class_name][$parameter_name])) {
            unset($this->temporary_parameters[$class_name][$parameter_name]);
        }
    }

    /**
     * Returns a stored structure value of the given key from the
     * corresponding class mapped by CID.
     *
     * @param string $identifier_key
     * @param string $cid
     * @return array|string|null
     */
    private function getValueForKeyByCid(string $identifier_key, string $cid) : array|string|null
    {
        if (isset(self::$mapped_structure[$cid][$identifier_key])) {
            return self::$mapped_structure[$cid][$identifier_key];
        }

        foreach ($this->structure as $class_info) {
            if (isset($class_info[$identifier_key]) && $class_info[self::KEY_CLASS_CID] === $cid) {
                self::$mapped_structure[$cid] = $class_info;
                return $class_info[$identifier_key];
            }
        }

        return null;
    }

    /**
     * Returns a stored structure value of the given key from the
     * corresponding class mapped by name.
     *
     * @param string $identifier_key
     * @param string $class_name
     * @return array|string|null
     */
    private function getValueForKeyByName(string $identifier_key, string $class_name) : array|string|null
    {
        $class_name = $this->lowercase($class_name);
        if (isset($this->structure[$class_name])) {
            return $this->structure[$class_name][$identifier_key];
        }

        return null;
    }

    /**
     * Returns all (lowercase) classes of services.
     *
     * @return string[]
     */
    private function fetchServices() : array
    {
        $services    = [];
        $service_set = $this->database->query(
            "SELECT LOWER(class) AS class_name FROM service_class;"
        );

        while ($record = $service_set->fetchAssoc()) {
            $services[] = $record['class_name'];
        }

        return (!empty($services)) ? $services : [];
    }

    /**
     * Returns all (lowercase) classes of services.
     *
     * @return string[]
     */
    private function fetchModules() : array
    {
        $modules    = [];
        $module_set = $this->database->query(
            "SELECT LOWER(class) AS class_name FROM module_class;"
        );

        while ($record = $module_set->fetchAssoc()) {
            $modules[] = $record['class_name'];
        }

        return (!empty($modules)) ? $modules : [];
    }

    /**
     * Helper function to lowercase strings.
     *
     * @param string $string
     * @return string
     */
    private function lowercase(string $string) : string
    {
        return strtolower($string);
    }
}