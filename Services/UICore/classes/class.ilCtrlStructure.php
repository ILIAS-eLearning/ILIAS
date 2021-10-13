<?php

/**
 * Class ilCtrlStructure holds the currently read control
 * structure.
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
     * Holds target URLs mapped to
     *
     * @var array<string, string>
     */
    private array $return_targets = [];

    /**
     * Holds the currently read control structure as array data.
     *
     * @var array<string, string|string[]>
     */
    private array $structure;

    /**
     * Holds a list of the currently gathered ilCtrl base classes.
     *
     * @var string[]
     */
    private array $base_classes;

    /**
     * Holds the control structure mapped by other identifiers than
     * the classname (primarily CID).
     *
     * @var array<string, string|string[]>
     */
    private static array $mapped_structure = [];

    /**
     * ilCtrlStructure Constructor
     *
     * @throws ilCtrlException if the artifacts cannot be included.
     */
    public function __construct()
    {
        try {
            $this->structure    = require ilCtrlStructureArtifactObjective::ARTIFACT_PATH;
            $this->base_classes = require ilCtrlBaseClassArtifactObjective::ARTIFACT_PATH;
        } catch (Throwable $t) {
            throw new ilCtrlException("Could not include ilCtrl artifacts: " . $t->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function isBaseClass(string $class_name) : bool
    {
        return
            null !== $this->getClassCidByName($class_name) &&
            in_array($this->lowercase($class_name), $this->base_classes, true)
        ;
    }

    /**
     * @inheritDoc
     */
    public function getObjNameByName(string $class_name) : string
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_NAME, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getObjNameByCid(string $cid) : string
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
    public function getRelativePathByName(string $class_name) : ?string
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_PATH, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getRelativePathByCid(string $cid) : ?string
    {
        return $this->getValueForKeyByCid(self::KEY_CLASS_PATH, $cid);
    }

    /**
     * @inheritDoc
     */
    public function getChildrenByCid(string $cid) : ?array
    {
        return $this->getValueForKeyByCid(self::KEY_CLASS_CHILDREN, $cid);
    }

    /**
     * @inheritDoc
     */
    public function getChildrenByName(string $class_name) : ?array
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_CHILDREN, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getParentsByCid(string $cid) : ?array
    {
        return $this->getValueForKeyByCid(self::KEY_CLASS_PARENTS, $cid);
    }

    /**
     * @inheritDoc
     */
    public function getParentsByName(string $class_name) : ?array
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_PARENTS, $class_name);
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
    public function setParameterByClass(string $class_name, string $parameter_name, $value) : void
    {
        if (!preg_match(self::PARAM_NAME_REGEX, $parameter_name)) {
            throw new ilCtrlException("Cannot set parameter '$parameter_name', as it contains invalid characters.");
        }

        $this->temporary_parameters[$this->lowercase($class_name)][$parameter_name] = $value;
    }

    /**ilCtrlTarget
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
     * @inheritDoc
     */
    public function setReturnTargetByClass(string $class_name, string $target_url) : void
    {
        $this->return_targets[$this->lowercase($class_name)] = $target_url;
    }

    /**
     * @inheritDoc
     */
    public function getReturnTargetByClass(string $class_name) : ?string
    {
        return $this->return_targets[$this->lowercase($class_name)] ?? null;
    }

    /**
     * Returns a stored structure value of the given key from the
     * corresponding class mapped by CID.
     *
     * @param string $identifier_key
     * @param string $cid
     * @return array|string|null
     */
    private function getValueForKeyByCid(string $identifier_key, string $cid)
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
    private function getValueForKeyByName(string $identifier_key, string $class_name)
    {
        $class_name = $this->lowercase($class_name);
        if (isset($this->structure[$class_name])) {
            return $this->structure[$class_name][$identifier_key];
        }

        return null;
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
