<?php

require_once "./libs/composer/vendor/autoload.php";

/**
 * Class ilCtrlStructureReader is responsible for reading
 * ilCtrl's control structure.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlStructureReader
{
    /**
     * @var string regex pattern for ILIAS GUI classes. Filename
     *             must be 'class.<classname>GUI.php'.
     */
    private const REGEX_GUI_CLASS_NAME = '/^class\.([A-z0-9]*(GUI))\.php$/';

    /**
     * @var string regex pattern that matches classes listed behind
     *             an ilCtrl_Calls statement. '{CLASS_NAME}' has to
     *             be replaced with an actual classname before used.
     */
    private const REGEX_PHPDOC_CALLS = '/((@ilCtrl_Calls|@ilctrl_calls)\s*({CLASS_NAME}(:\s*|\s*:\s*))\K.*)/';

    /**
     * @var string regex pattern similar to the one above, except it's
     *             used for ilCtrl_isCalledBy statements.
     */
    private const REGEX_PHPDOC_CALLED_BYS = '/((@ilCtrl_isCalledBy|@ilctrl_iscalledby)\s*({CLASS_NAME}(:\s*|\s*:\s*))\K.*)/';

    /**
     * Holds whether the structure reader was already executed or not.
     *
     * @var bool
     */
    private static bool $is_executed = false;

    /**
     * Holds the ILIAS absolute path (without ending '/').
     *
     * @var string
     */
    private string $ilias_path;

    /**
     * Holds the composer generated class map.
     *
     * @var array
     */
    private array $class_map;

    /**
     * Holds the currently read references mapped by classname.
     *
     * @var array
     */
    private array $references = [];

    /**
     * Holds the currently read control structure.
     *
     * @var array
     */
    private array $structure = [];

    /**
     * Holds the current cid count.
     *
     * @var int
     */
    private int $cid_count = 0;

    /**
     * ilCtrlStructureReader Constructor
     */
    public function __construct()
    {
        $this->class_map  = include "./libs/composer/vendor/composer/autoload_classmap.php";
        $this->ilias_path = rtrim(dirname(__FILE__, 5), '/');
    }

    /**
     * Returns whether this instance was already executed or not.
     *
     * @return bool
     */
    public function isExecuted() : bool
    {
        return self::$is_executed;
    }

    /**
     * Processes all classes within the ILIAS installation.
     *
     * @return array
     */
    public function readStructure() : array
    {
        foreach ($this->class_map as $class_name => $path) {
            // skip iteration if class doesn't meet ILIAS
            // GUI class criteria.
            if (!$this->isGuiClass($path)) {
                continue;
            }

            $array_key = strtolower($class_name);

            try {
                $reflection = new ReflectionClass($path);
                $this->references[$array_key][ilCtrlStructureInterface::KEY_CLASS_CHILDREN] = $this->getChildren($reflection);
                $this->references[$array_key][ilCtrlStructureInterface::KEY_CLASS_PARENTS]  = $this->getParents($reflection);
            } catch (ReflectionException $e) {
                continue;
            }

            $this->structure[$array_key][ilCtrlStructureInterface::KEY_CLASS_NAME] = $class_name;
            $this->structure[$array_key][ilCtrlStructureInterface::KEY_CLASS_CID]  = $this->generateCid();
            $this->structure[$array_key][ilCtrlStructureInterface::KEY_CLASS_PATH] = $this->getRelativePath($path);
        }


        foreach ($this->references as $class_name => $data) {
            $this->addViseVersaMapping($class_name, ilCtrlStructureInterface::KEY_CLASS_CHILDREN);
            $this->addViseVersaMapping($class_name, ilCtrlStructureInterface::KEY_CLASS_PARENTS);
        }

        foreach ($this->structure as $class_name => $data) {
            $parents = $this->getParentsRecursively($class_name);
            $this->structure[$class_name][ilCtrlStructureInterface::KEY_CLASS_CID_PATHS] = (!empty($parents)) ? array_keys($parents) : [];
        }

        self::$is_executed = true;

        return $this->structure;
    }

    /**
     * If a class has referenced another one as child or parent,
     * this method adds a vise-versa mapping if it doesn't already
     * exist.
     *
     * @param string $class_name
     * @param string $array_key
     */
    private function addViseVersaMapping(string $class_name, string $array_key) : void
    {
        if (!empty($this->references[$class_name][$array_key])) {
            foreach ($this->references[$class_name][$array_key] as $reference) {
                if (!isset($this->references[$reference])) {
                    throw new LogicException("Class '$class_name' referenced '$reference' but it doesn't exist.");
                }

                // only add vise-versa mapping if it doesn't already exist.
                if (!in_array($class_name, $this->references[$reference][$array_key], true)) {
                    $this->references[$reference][$array_key][] = $class_name;
                }
            }
        }
    }

    /**
     * Returns all classes referenced by an ilCtrl_Calls or
     * ilCtrl_isCalledBy statement.
     *
     * @param ReflectionClass $reflection
     * @param string          $regex
     * @return array
     */
    private function getReferencedClassesByReflection(ReflectionClass $reflection, string $regex) : array
    {
        // abort if the class has no PHPDoc comment.
        if (!$reflection->getDocComment()) {
            return [];
        }

        // replace the classname placeholder with the
        // actual one and execute the regex search.
        $regex = str_replace('{CLASS_NAME}', $reflection->getName(), $regex);
        preg_match_all($regex, $reflection->getDocComment(), $matches);

        // the first array entry of $matches contains
        // the list's of statements found.
        if (empty($matches[0])) {
            return [];
        }

        $referenced_classes = [];
        foreach ($matches[0] as $class_list) {
            // explode lists and strip all whitespaces.
            foreach (explode(',', $class_list) as $class) {
                $class_name = $this->stripWhitespaces($class);
                if (!empty($class_name)) {
                    // NOTE that all references are lowercase.
                    $referenced_classes[] = strtolower($class_name);
                }
            }
        }

        return $referenced_classes;
    }

    /**
     * Returns all direct and derived parent classes for a given
     * class. The parents are mapped to their cid path in order
     * to know how it can be reached.
     *
     * Return value might look like this for example:
     *
     *      array(
     *          'cid1'           => 'foo',
     *          'cid1:cid2'      => 'fooBar',
     *          'cid1:cid2:cid3' => null,
     *          ...
     *      );
     *
     * If no parents are found null will be mapped to the path.
     *
     * @param string      $target_class
     * @param string|null $current_path
     * @return array<string, string[]>
     */
    private function getParentsRecursively(string $target_class, string $current_path = null) : array
    {
        $target_class_parents = $this->references[$target_class][ilCtrlStructureInterface::KEY_CLASS_PARENTS] ?? [];
        $target_class_cid     = $this->structure[$target_class][ilCtrlStructureInterface::KEY_CLASS_CID];

        // abort if the target is an orphan.
        if (empty($target_class_parents)) {
            return [];
        }

        // initialize the current path if not provided,
        // else append the target class cid.
        $current_path = (null !== $current_path) ?
            $current_path . ilCtrlPathInterface::CID_PATH_SEPARATOR . $target_class_cid :
            $target_class_cid
        ;

        // map the target classes parents to the current path
        $parents[$current_path] = $target_class_parents;

        // fetch derived parents for all parent objects of the
        // current target class.
        foreach ($parents[$current_path] as $parent_class) {
            // only process parent class if it exists (in the
            // control structure).
            $parent_class_cid = $this->structure[$parent_class][ilCtrlStructureInterface::KEY_CLASS_CID];
            $parent_class_parents = $this->getParentsRecursively($parent_class, $current_path);
            if (!empty($parent_class_parents)) {
                // if the parent has further parents, map them to their path.
                foreach ($parent_class_parents as $parent_path => $parent) {
                    $parents[$parent_path] = $parent;
                }
            } else {
                // if the parent class is an orphan, set null mapped
                // to the parent's path.
                $parents[$current_path . ilCtrlPathInterface::CID_PATH_SEPARATOR . $parent_class_cid] = null;
            }
        }

        return $parents;
    }

    /**
     * Helper function that returns all children references.
     *
     * @param ReflectionClass $reflection
     * @return array
     */
    private function getChildren(ReflectionClass $reflection) : array
    {
        return $this->getReferencedClassesByReflection($reflection, self::REGEX_PHPDOC_CALLS);
    }

    /**
     * Helper function that returns all parent references.
     *
     * @param ReflectionClass $reflection
     * @return array
     */
    private function getParents(ReflectionClass $reflection) : array
    {
        return $this->getReferencedClassesByReflection($reflection, self::REGEX_PHPDOC_CALLED_BYS);
    }

    /**
     * Helper function that replaces all whitespace characters
     * from the given string.
     *
     * @param string $string
     * @return string
     */
    private function stripWhitespaces(string $string) : string
    {
        return preg_replace('/\s+/', '', $string);
    }

    /**
     * Returns a given path relative to the ILIAS absolute path.
     *
     * @param string $absolute_path
     * @return string
     */
    private function getRelativePath(string $absolute_path) : string
    {
        return str_replace($this->ilias_path, '', $absolute_path);
    }

    /**
     * Returns whether the given file/path matches ILIAS conventions.
     *
     * @param string $path
     * @return bool
     */
    private function isGuiClass(string $path) : bool
    {
        return preg_match(self::REGEX_GUI_CLASS_NAME, basename($path));
    }

    /**
     * Returns an incremented base 36 class id.
     *
     * @return string
     */
    private function generateCid() : string
    {
        $this->cid_count++;

        return base_convert((string) $this->cid_count, 10, 36);
    }
}
