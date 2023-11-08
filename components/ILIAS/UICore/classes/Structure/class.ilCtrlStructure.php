<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlStructure holds the currently read control
 * structure.
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructure implements ilCtrlStructureInterface
{
    /**
     * @var string regex for the validation of $_GET parameter names.
     *             (allows A-Z, a-z, 0-9, '_' and '-'.)
     */
    private const PARAM_NAME_REGEX = '/^[A-Za-z0-9_-]*$/';

    /**
     * Holds parameter => value pairs mapped to the corresponding
     * or owning class.
     * @var array<string, array>
     */
    private array $temporary_parameters = [];

    /**
     * Holds parameter names mapped to the corresponding or owning
     * class.
     * @var array<string, string[]>
     */
    private array $permanent_parameters = [];

    /**
     * Holds target URLs mapped to
     * @var array<string, string>
     */
    private array $return_targets = [];

    /**
     * Holds the currently read control structure as array data.
     * @var array<string, string|string[]>
     */
    private array $structure;

    /**
     * Holds a list of the currently gathered ilCtrl base classes.
     * @var string[]
     */
    private array $base_classes;

    /**
     * Holds the stored ilCtrlSecurityInterface information.
     * @var array<string, mixed>
     */
    private array $security;

    /**
     * Holds the control structure mapped by other identifiers than
     * the classname (primarily CID).
     * @var array<string, string|string[]>
     */
    private array $mapped_structure = [];

    /**
     * ilCtrlStructure Constructor
     * @param array $ctrl_structure
     * @param array $base_classes
     * @param array $security_info
     */
    public function __construct(
        array $ctrl_structure,
        array $base_classes,
        array $security_info
    ) {
        $this->base_classes = $base_classes;
        $this->security = $security_info;
        $this->structure = $ctrl_structure;
    }

    /**
     * @inheritDoc
     */
    public function isBaseClass(string $class_name): bool
    {
        // baseclass must be contained within the current structure
        // and within the current baseclass array.
        return
            null !== $this->getClassCidByName($class_name) &&
            in_array($this->lowercase($class_name), $this->base_classes, true);
    }

    /**
     * @inheritDoc
     */
    public function getObjNameByCid(string $cid): ?string
    {
        return $this->getValueForKeyByCid(self::KEY_CLASS_NAME, $cid);
    }

    /**
     * @inheritDoc
     */
    public function getObjNameByName(string $class_name): ?string
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_NAME, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getClassNameByCid(string $cid): ?string
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
    public function getClassCidByName(string $class_name): ?string
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_CID, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getRelativePathByName(string $class_name): ?string
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_PATH, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getRelativePathByCid(string $cid): ?string
    {
        return $this->getValueForKeyByCid(self::KEY_CLASS_PATH, $cid);
    }

    /**
     * @inheritDoc
     */
    public function getChildrenByCid(string $cid): ?array
    {
        $children = $this->getValueForKeyByCid(self::KEY_CLASS_CHILDREN, $cid);
        if (empty($children)) {
            return null;
        }

        return $children;
    }

    /**
     * @inheritDoc
     */
    public function getChildrenByName(string $class_name): ?array
    {
        $children = $this->getValueForKeyByName(self::KEY_CLASS_CHILDREN, $class_name);
        if (empty($children)) {
            return null;
        }

        return $children;
    }

    /**
     * @inheritDoc
     */
    public function getParentsByCid(string $cid): ?array
    {
        $parents = $this->getValueForKeyByCid(self::KEY_CLASS_PARENTS, $cid);
        if (empty($parents)) {
            return null;
        }

        return $parents;
    }

    /**
     * @inheritDoc
     */
    public function getParentsByName(string $class_name): ?array
    {
        $parents = $this->getValueForKeyByName(self::KEY_CLASS_PARENTS, $class_name);
        if (empty($parents)) {
            return null;
        }

        return $parents;
    }

    /**
     * @inheritDoc
     */
    public function setPermanentParameterByClass(string $class_name, string $parameter_name): void
    {
        if (in_array($parameter_name, ilCtrlInterface::PROTECTED_PARAMETERS, true)) {
            throw new ilCtrlException("Parameter '$parameter_name' must not be saved, it could mess with the control flow.");
        }

        if (!preg_match(self::PARAM_NAME_REGEX, $parameter_name)) {
            throw new ilCtrlException("Cannot save parameter '$parameter_name', as it contains invalid characters.");
        }

        $this->permanent_parameters[$this->lowercase($class_name)][] = $parameter_name;
    }

    /**
     * @inheritDoc
     */
    public function removePermanentParametersByClass(string $class_name): void
    {
        $class_name = $this->lowercase($class_name);
        if (isset($this->permanent_parameters[$class_name])) {
            unset($this->permanent_parameters[$class_name]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getPermanentParametersByClass(string $class_name): ?array
    {
        return $this->permanent_parameters[$this->lowercase($class_name)] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setTemporaryParameterByClass(string $class_name, string $parameter_name, $value): void
    {
        if (!preg_match(self::PARAM_NAME_REGEX, $parameter_name)) {
            throw new ilCtrlException("Cannot save parameter '$parameter_name', as it contains invalid characters.");
        }

        $this->temporary_parameters[$this->lowercase($class_name)][$parameter_name] = $value;
    }

    /**
     * @inheritDoc
     */
    public function removeTemporaryParametersByClass(string $class_name): void
    {
        $class_name = $this->lowercase($class_name);
        if (isset($this->temporary_parameters[$class_name])) {
            unset($this->temporary_parameters[$class_name]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getTemporaryParametersByClass(string $class_name): ?array
    {
        return $this->temporary_parameters[$this->lowercase($class_name)] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function removeSingleParameterByClass(string $class_name, string $parameter_name): void
    {
        $class_name = $this->lowercase($class_name);

        // permanent parameters are lists of parameter names
        // mapped to the classname, therefore the index is
        // unknown and has to be figured out.
        if (!empty($this->permanent_parameters[$class_name])) {
            foreach ($this->permanent_parameters[$class_name] as $index => $permanent_parameter) {
                if ($parameter_name === $permanent_parameter) {
                    unset($this->permanent_parameters[$class_name][$index]);

                    // reindex the array values.
                    $permanent_parameters = &$this->permanent_parameters[$class_name];
                    $permanent_parameters = array_values($permanent_parameters);
                }
            }
        }

        // the temporary parameters are key => value pairs mapped
        // to the classname, whereas key is the parameter name.
        // The index is therefore known and can be unset directly.
        if (isset($this->temporary_parameters[$class_name])) {
            unset($this->temporary_parameters[$class_name][$parameter_name]);
        }
    }

    /**
     * @inheritDoc
     */
    public function setReturnTargetByClass(string $class_name, string $target_url): void
    {
        $this->return_targets[$this->lowercase($class_name)] = $target_url;
    }

    /**
     * @inheritDoc
     */
    public function getReturnTargetByClass(string $class_name): ?string
    {
        return $this->return_targets[$this->lowercase($class_name)] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getUnsafeCommandsByCid(string $cid): array
    {
        $class_name = $this->getClassNameByCid($cid);
        if (null !== $class_name) {
            return $this->getUnsafeCommandsByName($class_name);
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getUnsafeCommandsByName(string $class_name): array
    {
        return $this->security[$this->lowercase($class_name)][self::KEY_UNSAFE_COMMANDS] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getSafeCommandsByCid(string $cid): array
    {
        $class_name = $this->getClassNameByCid($cid);
        if (null !== $class_name) {
            return $this->getSafeCommandsByName($class_name);
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getSafeCommandsByName(string $class_name): array
    {
        return $this->security[$this->lowercase($class_name)][self::KEY_SAFE_COMMANDS] ?? [];
    }

    /**
     * Returns a stored structure value of the given key from the
     * corresponding class mapped by CID.
     * @param string $identifier_key
     * @param string $cid
     * @return array|string|null
     */
    private function getValueForKeyByCid(string $identifier_key, string $cid)
    {
        if (isset($this->mapped_structure[$cid][$identifier_key])) {
            return $this->mapped_structure[$cid][$identifier_key];
        }

        foreach ($this->structure as $class_info) {
            if (isset($class_info[$identifier_key]) && $class_info[self::KEY_CLASS_CID] === $cid) {
                $this->mapped_structure[$cid] = $class_info;
                return $class_info[$identifier_key];
            }
        }

        return null;
    }

    /**
     * Returns a stored structure value of the given key from the
     * corresponding class mapped by name.
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
     * @param string $string
     * @return string
     */
    private function lowercase(string $string): string
    {
        return strtolower($string);
    }
}
