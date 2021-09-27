<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./libs/composer/vendor/autoload.php";

/**
 * Class ilCtrlStructureReader is responsible for the ilCtrl structure.
 * This class reads the call structure of all classes into an
 * array and stores it as an artifact.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlStructureReader
{
    /**
     * regex patterns used to read the call structure.
     * they're also known as horcruxes or dark magic, don't touch them!
     */
    private const REGEX_ILCTRL_DECLARATION = '~^.*@{WHICH}\s+([\w\\\\]+)\s*:\s*([\w\\\\]+(\s*,\s*[\w\\\\]+)*)\s*$~mi';
    private const REGEX_INTERESTING_FILES = "~^(class\..*\.php)|(ilSCORM13Player\.php)$~i";
    private const REGEX_GUI_CLASSES = "~^.*[/\\\\]class\.(.*GUI)\.php$~i";

    /**
     * Holds whether the reader has been executed or not.
     * @var bool
     */
    private static bool $executed = false;

    /**
     * Holds the temporarily read data.
     * @var array
     */
    private array $raw_structure = [];

    private int $cid_counter = 0;

    /**
     * @return bool
     */
    public function isExecuted() : bool
    {
        return self::$executed;
    }

    /**
     * Returns the read call structure for ilCtrl.
     * @return array
     */
    public function readStructure() : array
    {
        error_reporting(E_ALL);

        $classmap = include "./libs/composer/vendor/composer/autoload_classmap.php";
        $classes = [];
        $x = 1;

        foreach ($classmap as $class => $class_path) {
            if (null !== $this->getGUIClassNameFromClassPath($class_path) &&
                $this->isInterestingFile(basename($class_path))
            ) {
                $content = @file_get_contents($class_path);
                if ($this->containsClassDefinitionFor($class, $content)) {
                    try {
                        // we parse the Infos from PHPDoc
                        $r = new ReflectionClass($class);
                        $php_doc = $r->getDocComment();
                        if ($php_doc !== false) {
                            $calls = $this->getCalls($content) ?? [];
                            $called = $this->getCalledBys($content) ?? [];
                            if (count($calls) === 0 && count($called) === 0) {
                                continue;
                            }
                            $this->raw_structure[$class] = [
                                ilCtrlStructureInterface::KEY_THIS_CALLING_OTHERS => $calls,
                                ilCtrlStructureInterface::KEY_OTHERS_CALLING_THIS => $called,
                            ];

                            /*

                                                        if (count($calls) > 0 || count($called) > 0) {
                                                            foreach ($calls as $call) {
                                                                $this->temp_data[$call][] = $class;
                                                            }
                                                        }
                                                        $classes[strtolower($class)] = [
                                                            ilCtrlStructureInterface::KEY_CLASS_CID       => $this->generateCid($x),
                                                            ilCtrlStructureInterface::KEY_CALLED_CLASSES  => $calls,
                                                            ilCtrlStructureInterface::KEY_CALLING_CLASSES => $called,
                                                            ilCtrlStructureInterface::KEY_CLASS_NAME      => $class,
                                                            ilCtrlStructureInterface::KEY_CLASS_PATH      => $this->getRelativeClassPath($class_path),
                                                        ];

                                                        $x++;*/
                        }
                    } catch (Throwable $t) {

                    }
                }

                unset($content);
            }
        }

        foreach ($this->raw_structure as $classname => $calling_infos) {
            if (isset($calling_infos[ilCtrlStructureInterface::KEY_THIS_CALLING_OTHERS])) {
                foreach ($calling_infos[ilCtrlStructureInterface::KEY_THIS_CALLING_OTHERS] as $called_class) {
                    $this->raw_structure[$called_class][ilCtrlStructureInterface::KEY_OTHERS_CALLING_THIS][] = $classname;
                }
            }
            if (isset($calling_infos[ilCtrlStructureInterface::KEY_OTHERS_CALLING_THIS])) {
                foreach ($calling_infos[ilCtrlStructureInterface::KEY_OTHERS_CALLING_THIS] as $calling_class) {
                    $this->raw_structure[$calling_class][ilCtrlStructureInterface::KEY_THIS_CALLING_OTHERS][] = $classname;
                }
            }
        }
        $final_structure = [];

        foreach ($this->raw_structure as $class_name => $class_entry) {
            $others_calling_this = $class_entry[ilCtrlStructureInterface::KEY_OTHERS_CALLING_THIS] ?? [];
            $this_calling_others = $class_entry[ilCtrlStructureInterface::KEY_THIS_CALLING_OTHERS] ?? [];
            $final_structure[strtolower($class_name)] = [
                ilCtrlStructureInterface::KEY_CLASS_CID => $this->generateCid(),
                ilCtrlStructureInterface::KEY_OTHERS_CALLING_THIS => array_map('strtolower', $others_calling_this),
                ilCtrlStructureInterface::KEY_THIS_CALLING_OTHERS => array_map('strtolower', $this_calling_others),
                ilCtrlStructureInterface::KEY_CLASS_NAME => $class_name,
                //                ilCtrlStructureInterface::KEY_CLASS_PATH      => $this->getRelativeClassPath($class_path),
            ];
        }

        /*
                foreach ($this->temp_data as $class => $called_bys) {
                    foreach ($called_bys as $called_by) {
                        if (!isset($classes[$class])) {
                            $classes[strtolower($class)] = [
                                ilCtrlStructureInterface::KEY_CLASS_CID       => $this->generateCid($x),
                                ilCtrlStructureInterface::KEY_CALLING_CLASSES => [],
                                ilCtrlStructureInterface::KEY_CALLED_CLASSES  => $class
                            ];
                        }

                        $classes[strtolower($class)][ilCtrlStructureInterface::KEY_CALLING_CLASSES][] = strtolower($called_by);
                    }
                }*/

        self::$executed = true;

        return $final_structure;
    }

    /**
     * @param string $absolute_path
     * @return string
     */
    private function getRelativeClassPath(string $absolute_path) : string
    {
        return '.' . str_replace($this->getILIASAbsolutePath(), '', $absolute_path);
    }

    /**
     * @param string $file
     * @return bool
     */
    private function isInterestingFile(string $file) : bool
    {
        try {
            return (bool) preg_match(self::REGEX_INTERESTING_FILES, $file);
        } catch (Throwable $t) {
            return false;
        }
    }

    /**
     * @return string
     */
    private function generateCid() : string
    {
        $this->cid_counter++;
        return base_convert((string) $this->cid_counter, 10, 36);
    }

    /**
     * @param string $path
     * @return string|null
     */
    private function getGUIClassNameFromClassPath(string $path) : ?string
    {
        $res = [];
        if (preg_match(self::REGEX_GUI_CLASSES, $path, $res)) {
            return ($res[1]);
        }
        return null;
    }

    /**
     * @param string $class
     * @param string $content
     * @return bool
     */
    private function containsClassDefinitionFor(string $class, string $content) : bool
    {
        $regexp = "~.*class\s+$class~mi";
        return preg_match($regexp, $content) !== 0;
    }

    /**
     * @return null|<string,string[]>
     */
    private function getCalls(string $content) : ?array
    {
        return $this->getIlCtrlDeclarations($content, "ilctrl_calls");
    }

    /**
     * @return null|<string,string[]>
     */
    private function getCalledBys(string $content) : ?array
    {
        return $this->getIlCtrlDeclarations($content, "ilctrl_iscalledby");
    }

    /**
     * @return null|<string,string[]>
     */
    private function getIlCtrlDeclarations(string $content, string $which) : ?array
    {
        $regexp = str_replace("{WHICH}", $which, self::REGEX_ILCTRL_DECLARATION);
        $res = [];
        if (!preg_match_all($regexp, $content, $res)) {
            return null;
        }

        $class_names = array_unique($res[1]);
        if (count($class_names) != 1) {
            throw new \LogicException(
                "Found different class names in ilctrl_calls: " . join(",", $class_names)
            );
        }

        $declaration = [];
        foreach ($res[2] as $ls) {
            foreach (explode(",", $ls) as $l) {
                $declaration[] = (trim($l));
            }
        }
        return $declaration;

        return [(trim($class_names[0])), $declaration];
    }

    /**
     * @return string
     */
    private function getILIASAbsolutePath() : string
    {

        $ilias_path = (defined("ILIAS_ABSOLUTE_PATH")) ?
            $this->normalizePath(ILIAS_ABSOLUTE_PATH) :
            dirname(__FILE__, 5);

        return rtrim($ilias_path, '/');
    }

    /**
     * @param string $path
     * @return string
     */
    private function normalizePath(string $path) : string
    {
        return realpath(str_replace(['//'], ['/'], $path));
    }
}
