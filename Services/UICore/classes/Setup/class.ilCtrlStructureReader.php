<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./libs/composer/vendor/autoload.php";

/**
 * Class ilCtrlStructureReader is responsible for the ilCtrl structure.
 * This class reads the call structure of all classes into an
 * array and stores it as an artifact.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlStructureReader
{
    /**
     * regex patterns used to read the call structure.
     * they're also known as horcruxes or dark magic, don't touch them!
     */
    private const REGEX_ILCTRL_DECLARATION  = '~^.*@{WHICH}\s+([\w\\\\]+)\s*:\s*([\w\\\\]+(\s*,\s*[\w\\\\]+)*)\s*$~mi';
    private const REGEX_INTERESTING_FILES   = "~^(class\..*\.php)|(ilSCORM13Player\.php)$~i";
    private const REGEX_GUI_CLASSES         = "~^.*[/\\\\]class\.(.*GUI)\.php$~i";

    /**
     * Holds whether the reader has been executed or not.
     * @var bool
     */
    private static bool $executed = false;

    /**
     * Holds all classes mapped with
     *
     * @var array
     */
    private array $raw_structure = [];

    /**
     * @var int
     */
    private int $cid_counter = 0;

    /**
     * Returns the read call structure for ilCtrl.
     * @return array
     */
    public function readStructure() : array
    {
        error_reporting(E_ALL);

        $classmap = include "./libs/composer/vendor/composer/autoload_classmap.php";
        foreach ($classmap as $class => $class_path) {
            if (null !== $this->getGUIClassNameFromClassPath($class_path) &&
                $this->isInterestingFile(basename($class_path))
            ) {
                $this->raw_structure[$class] = [];
                $this->raw_structure[$class][ilCtrlStructureInterface::KEY_CLASS_PATH] = $this->getRelativeClassPath($class_path);

                $content = @file_get_contents($class_path);
                if ($this->containsClassDefinitionFor($class, $content)) {
                    try {
                        $reflection  = new ReflectionClass($class);
                        $doc_comment = $reflection->getDocComment();

                        if (false !== $doc_comment) {
                            $this_calling_others = $this->CallsThisCallingOthers($content) ?? [];
                            $others_calling_this = $this->getOthersCallingThis($content) ?? [];

                            // only store call information if it exists, else
                            // just initialize the array.
                            if (0 < (count($this_calling_others) + count($others_calling_this))) {
                                $this->raw_structure[$class] = [
                                    ilCtrlStructureInterface::KEY_THIS_CALLING_OTHERS => array_map('strtolower', $this_calling_others),
                                    ilCtrlStructureInterface::KEY_OTHERS_CALLING_THIS => array_map('strtolower', $others_calling_this),
                                ];
                            }
                        }
                    } catch (Throwable $t) {}
                }

                unset($content);
            }
        }

        // vise-versa mapping of others calling this
        // and this calling others.
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
        foreach ($this->raw_structure as $class_name => $class_info) {
            $final_structure[strtolower($class_name)] = [
                ilCtrlStructureInterface::KEY_CLASS_CID           => $this->generateCid(),
                ilCtrlStructureInterface::KEY_CLASS_NAME          => $class_name,
                ilCtrlStructureInterface::KEY_CLASS_PATH          => $class_info[ilCtrlStructureInterface::KEY_CLASS_PATH] ?? '',
                ilCtrlStructureInterface::KEY_THIS_CALLING_OTHERS => $class_info[ilCtrlStructureInterface::KEY_OTHERS_CALLING_THIS] ?? [],
                ilCtrlStructureInterface::KEY_OTHERS_CALLING_THIS => $class_info[ilCtrlStructureInterface::KEY_THIS_CALLING_OTHERS] ?? [],
            ];
        }

        self::$executed = true;

        return $final_structure;
    }

    /**
     * @return bool
     */
    public function isExecuted() : bool
    {
        return self::$executed;
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
    private function CallsThisCallingOthers(string $content) : ?array
    {
        return $this->getIlCtrlDeclarations($content, "ilctrl_calls");
    }

    /**
     * @return null|<string,string[]>
     */
    private function getOthersCallingThis(string $content) : ?array
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
        if (1 !== count($class_names)) {
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
