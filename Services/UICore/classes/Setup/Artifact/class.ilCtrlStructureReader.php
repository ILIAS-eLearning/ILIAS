<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../../../../../libs/composer/vendor/autoload.php';

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
    public const REGEX_GUI_CLASS_NAME = '/^class\.([A-z0-9]*(GUI))\.php$/';

    /**
     * @var string regex pattern that matches classes listed behind
     *             an ilCtrl_Calls statement. '{CLASS_NAME}' has to
     *             be replaced with an actual classname before used.
     */
    private const REGEX_PHPDOC_CALLS = '/(((?i)@ilctrl_calls)\s*({CLASS_NAME}(:\s*|\s*:\s*))\K)([A-z0-9,\s])*/';

    /**
     * @var string regex pattern similar to the one above, except it's
     *             used for ilCtrl_isCalledBy statements.
     */
    private const REGEX_PHPDOC_CALLED_BYS = '/(((?i)@ilctrl_iscalledby)\s*({CLASS_NAME}(:\s*|\s*:\s*))\K)([A-z0-9,\s])*/';

    /**
     * Holds whether the structure reader was already executed or not.
     *
     * @var bool
     */
    private static bool $is_executed = false;

    /**
     * Holds an instance of the cid generator.
     * @var ilCtrlStructureCidGenerator
     */
    private ilCtrlStructureCidGenerator $cid_generator;

    /**
     * Holds the structure-reader's iterator or datasource.
     *
     * @var ilCtrlIteratorInterface
     */
    private ilCtrlIteratorInterface $iterator;

    /**
     * Holds the ILIAS absolute path (without ending '/').
     *
     * @var string
     */
    private string $ilias_path;

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
     * ilCtrlStructureReader Constructor
     */
    public function __construct(ilCtrlIteratorInterface $iterator, ilCtrlStructureCidGenerator $cid_generator)
    {
        $this->ilias_path = rtrim(
            (defined('ILIAS_ABSOLUTE_PATH')) ?
                ILIAS_ABSOLUTE_PATH : dirname(__FILE__, 6),
            '/'
        );

        $this->cid_generator = $cid_generator;
        $this->iterator = $iterator;
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
        foreach ($this->iterator as $class_name => $path) {
            // skip iteration if class doesn't meet ILIAS GUI class criteria.
            if (!$this->isGuiClass($path)) {
                continue;
            }

            $array_key = strtolower($class_name);
            try {
                // the classes need to be required manually, because
                // auto-loading breaks when testing.
                require_once $path;

                $reflection = new ReflectionClass($class_name);
                $this->references[$array_key][ilCtrlStructureInterface::KEY_CLASS_CHILDREN] = $this->getChildren($reflection);
                $this->references[$array_key][ilCtrlStructureInterface::KEY_CLASS_PARENTS]  = $this->getParents($reflection);
            } catch (ReflectionException $e) {
                continue;
            }

            $this->structure[$array_key][ilCtrlStructureInterface::KEY_CLASS_CID]  = $this->cid_generator->getCid();
            $this->structure[$array_key][ilCtrlStructureInterface::KEY_CLASS_NAME] = $class_name;
            $this->structure[$array_key][ilCtrlStructureInterface::KEY_CLASS_PATH] = $this->getRelativePath($path);
        }

        // loops through all references and creates vise-versa
        // entries for them, e.g. if a class has children, the
        // class is added to all children as their parent.
        foreach ($this->references as $class_name => $data) {
            $this->addViseVersaMapping(
                $class_name,
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN,
                ilCtrlStructureInterface::KEY_CLASS_PARENTS
            );

            $this->addViseVersaMapping(
                $class_name,
                ilCtrlStructureInterface::KEY_CLASS_PARENTS,
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN
            );
        }

        // loops again through all references in order to
        // add this data to the actual output. This needs
        // to happen in a separate loop, as the vise-versa
        // mappings are not yet finished in the previous loop.
        foreach ($this->references as $class_name => $data) {
            $this->structure[$class_name][ilCtrlStructureInterface::KEY_CLASS_PARENTS] = $data[ilCtrlStructureInterface::KEY_CLASS_PARENTS];
            $this->structure[$class_name][ilCtrlStructureInterface::KEY_CLASS_CHILDREN] = $data[ilCtrlStructureInterface::KEY_CLASS_CHILDREN];
        }

        self::$is_executed = true;

        return $this->structure;
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
                    $referenced_classes[] = strtolower($class_name);
                }
            }
        }

        return $referenced_classes;
    }

    /**
     * If a class has referenced another one as child or parent,
     * this method adds a vise-versa mapping if it doesn't already
     * exist.
     *
     * @param string $class_name
     * @param string $key_ref_from
     * @param string $key_ref_to
     */
    private function addViseVersaMapping(string $class_name, string $key_ref_from, string $key_ref_to) : void
    {
        if (!empty($this->references[$class_name][$key_ref_from])) {
            foreach ($this->references[$class_name][$key_ref_from] as $reference) {
                // only add vise-versa mapping if it doesn't already exist.
                if (isset($this->references[$reference]) && !in_array($class_name, $this->references[$reference][$key_ref_to], true)) {
                    $this->references[$reference][$key_ref_to][] = $class_name;
                }
            }
        }
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
        return (string) preg_replace('/\s+/', '', $string);
    }

    /**
     * Returns a given path relative to the ILIAS absolute path.
     *
     * @param string $absolute_path
     * @return string
     */
    private function getRelativePath(string $absolute_path) : string
    {
        // some paths might contain syntax like '../../../' etc.
        // and realpath() resolves that in order to cut off the
        // ilias installation path properly.
        $absolute_path = realpath($absolute_path);

        return '.' . str_replace($this->ilias_path, '', $absolute_path);
    }

    /**
     * Returns whether the given file/path matches ILIAS conventions.
     *
     * @param string $path
     * @return bool
     */
    private function isGuiClass(string $path) : bool
    {
        return (bool) preg_match(self::REGEX_GUI_CLASS_NAME, basename($path));
    }
}
