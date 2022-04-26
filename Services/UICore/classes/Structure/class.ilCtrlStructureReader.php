<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../../../../libs/composer/vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Class ilCtrlStructureReader is responsible for reading
 * ilCtrl's control structure.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureReader
{
    /**
     * @var string regex pattern for ILIAS GUI classes. Filename
     *             must be 'class.<classname>GUI.php'.
     */
    public const REGEX_GUI_CLASS_NAME = '/^class\.([A-z0-9]*(GUI))\.php$/';

    /**
     * Holds an instance of the cid generator.
     *
     * @var ilCtrlStructureCidGenerator
     */
    private ilCtrlStructureCidGenerator $cid_generator;

    /**
     * Holds an instance of Doctrine's annotation reader.
     *
     * @var AnnotationReader
     */
    private AnnotationReader $annotation_reader;

    /**
     * Holds the current composer class-map.
     *
     * @var array<string, string>
     */
    private array $class_map;

    /**
     * Holds whether the structure reader was already executed or not.
     *
     * @var bool
     */
    private bool $is_executed = false;

    /**
     * Holds the ILIAS absolute path (without ending '/').
     *
     * @var string
     */
    private string $ilias_path;

    /**
     * ilCtrlStructureReader Constructor
     *
     * @param ilCtrlStructureCidGenerator $cid_generator
     * @param AnnotationReader            $annotation_reader
     * @param array<string, string>       $class_map
     */
    public function __construct(
        ilCtrlStructureCidGenerator $cid_generator,
        AnnotationReader $annotation_reader,
        array $class_map
    ) {
        $this->ilias_path = rtrim(
            (defined('ILIAS_ABSOLUTE_PATH')) ?
                ILIAS_ABSOLUTE_PATH : dirname(__FILE__, 5),
            '/'
        );

        $this->annotation_reader = $annotation_reader;
        $this->cid_generator = $cid_generator;
        $this->class_map = $class_map;
    }

    /**
     * Returns whether this instance was already executed or not.
     *
     * @return bool
     */
    public function isExecuted() : bool
    {
        return $this->is_executed;
    }

    /**
     * Processes all classes within the ILIAS installation.
     *
     * @return array
     */
    public function readStructure() : array
    {
        $base_classes = $structure = [];
        foreach ($this->class_map as $class_name => $path) {
            // skip iteration if class doesn't meet ILIAS GUI class criteria.
            if (!$this->isGuiClass($path)) {
                continue;
            }

            $lower_class_name = strtolower($class_name);
            try {
                // the classes need to be required manually, because
                // the autoload classmap might not include the plugin
                // classes when an update is triggered (small structure
                // reload).
                require_once $path;

                $reflection = ($this->isNamespaced($class_name)) ?
                    new ReflectionClass("\\$class_name") :
                    new ReflectionClass($class_name)
                ;

                $structure[$lower_class_name][ilCtrlStructureInterface::KEY_CLASS_CID] = $this->cid_generator->getCid();
                $structure[$lower_class_name][ilCtrlStructureInterface::KEY_CLASS_NAME] = $class_name;
                $structure[$lower_class_name][ilCtrlStructureInterface::KEY_CLASS_PATH] = $this->getRelativePath($path);
                $structure[$lower_class_name][ilCtrlStructureInterface::KEY_CLASS_CHILDREN] = $this->getChildren($reflection);
                $structure[$lower_class_name][ilCtrlStructureInterface::KEY_CLASS_PARENTS] = $this->getParents($reflection);

                // temporarily store base classes in order to filer the
                // structure afterwards.
                if (in_array(ilCtrlBaseClassInterface::class, $reflection->getInterfaceNames(), true)) {
                    $base_classes[] = $lower_class_name;
                }
            } catch (ReflectionException $e) {
                continue;
            }
        }

        $mapped_structure = (new ilCtrlStructureHelper($base_classes, $structure))
            ->mapStructureReferences()
            ->filterUnnecessaryEntries()
            ->getStructure()
        ;

        $this->is_executed = true;

        return $mapped_structure;
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
     * Helper function that returns all children references.
     *
     * @param ReflectionClass $reflection
     * @return array
     */
    private function getChildren(ReflectionClass $reflection) : array
    {
        $annotation = $this->annotation_reader->getClassAnnotation(
            $reflection,
            ilCtrlStructureCalls::class
        );

        return ($annotation) ? array_map('strtolower', $annotation->getChildren()) : [];
    }

    /**
     * Helper function that returns all parent references.
     *
     * @param ReflectionClass $reflection
     * @return array
     */
    private function getParents(ReflectionClass $reflection) : array
    {
        $annotation = $this->annotation_reader->getClassAnnotation(
            $reflection,
            ilCtrlStructureCalls::class
        );

        return ($annotation) ? array_map('strtolower', $annotation->getParents()) : [];
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

    /**
     * Returns if the given classname is namespaced.
     *
     * @param string $class_name
     * @return bool
     */
    private function isNamespaced(string $class_name) : bool
    {
        return (false !== strpos($class_name, '\\'));
    }
}
