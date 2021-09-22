<?php

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Wrapper\RequestWrapper;

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
     * Holds the current HTTP request.
     *
     * @var RequestWrapper
     */
    private RequestWrapper $request;

    /**
     * Holds a Refinery Factory instance.
     *
     * @var Refinery
     */
    private Refinery $refinery;

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
     * Holds the control structure mapped by other identifiers than
     * the classname (primarily CID).
     *
     * @var array<string, string|string[]>
     */
    private static array $mapped_structure = [];

    /**
     * Constructor
     *
     * @param Refinery $refinery
     * @throws ilCtrlException if the control structure cannot be included.
     */
    public function __construct(RequestWrapper $request, Refinery $refinery)
    {
        try {
            $this->structure = require ilCtrlStructureArtifactObjective::ARTIFACT_PATH;
        } catch (Throwable $exception) {
            throw new ilCtrlException("Could not include structure from artifact: " . $exception->getMessage());
        }

        $this->request  = $request;
        $this->refinery = $refinery;
    }

    /**
     * @inheritDoc
     */
    public function getQualifiedClassName(string $class_name) : string
    {
        return $this->getValueForKeyByName(self::KEY_CLASS_NAME, $class_name);
    }

    /**
     * @inheritDoc
     */
    public function getClassNameByCid(string $cid) : ?string
    {
        return strtolower(
            $this->getValueForKeyByCid(
                self::KEY_CLASS_NAME,
                $cid
            )
        );
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
    public function getCalledClassesByCid(string $cid) : array
    {
        return $this->getValueForKeyByCid(self::KEY_CALLED_CLASSES, $cid) ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getCalledClassesByName(string $class_name) : array
    {
        $this->getValueForKeyByName(self::KEY_CALLED_CLASSES, $class_name) ?? [];
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
    public function saveParameterForClass(string $class_name, string $parameter_name) : void
    {
        if (!preg_match(self::PARAM_NAME_REGEX, $parameter_name)) {
            throw new ilCtrlException("Cannot save parameter '$parameter_name', as it contains invalid characters.");
        }

        $this->permanent_parameters[strtolower($class_name)][] = $parameter_name;
    }

    /**
     * @inheritDoc
     */
    public function setParameterForClass(string $class_name, string $parameter_name, mixed $value) : void
    {
        if (!preg_match(self::PARAM_NAME_REGEX, $parameter_name)) {
            throw new ilCtrlException("Cannot set parameter '$parameter_name', as it contains invalid characters.");
        }

        $this->temporary_parameters[strtolower($class_name)][$parameter_name] = $value;
    }

    /**
     * @inheritDoc
     */
    public function getParametersByClass(string $class_name) : array
    {
        $class_name = strtolower($class_name);
        $parameters = [];

        if (isset($this->permanent_parameters[$class_name])) {
            foreach ($this->permanent_parameters[$class_name] as $key => $value) {
                if ($this->request->has($key)) {
                    $parameters[$key] = $this->request->retrieve(
                        $key,
                        $this->refinery->to()->string()
                    );
                } else {
                    $parameters[$key] = null;
                }
            }
        }

        if (isset($this->temporary_parameters[$class_name])) {
            foreach ($this->temporary_parameters[$class_name] as $key => $value) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
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
        if (isset(self::$mapped_structure[$cid])) {
            return self::$mapped_structure[$cid][$identifier_key];
        }

        foreach ($this->structure as $class_info) {
            foreach ($class_info as $key => $value) {
                if ($identifier_key === $key && $cid === $value) {
                    self::$mapped_structure[$cid] = $class_info;
                    return $value;
                }
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
        if (isset($this->structure[$class_name])) {
            return $this->structure[$class_name][$identifier_key];
        }

        return null;
    }
}