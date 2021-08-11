<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlStructureReader
 * Reads call structure of classes into db
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilCtrlStructureReader2
{

    protected const IL_CTRL_DECLARATION_REGEXP = '~^.*@{WHICH}\s+([\w\\\\]+)\s*:\s*([\w\\\\]+(\s*,\s*[\w\\\\]+)*)\s*$~mi';
    protected const INTERESTING_FILES_REGEXP = "~^(class\..*\.php)|(ilSCORM13Player\.php)$~i";
    protected const GUI_CLASS_FILE_REGEXP = "~^.*[/\\\\]class\.(.*GUI)\.php$~i";
    const CLASS_NAME = 'class_name';
    const CALLED = 'called';
    const CID = 'cid';

    protected array $temp_data = [];

    public function readStructureOnly() : array
    {
        include_once "./libs/composer/vendor/autoload.php";
        $classmap = include "./libs/composer/vendor/composer/autoload_classmap.php";

        $classes = [];
        $x = 1;
        error_reporting(E_ALL);
        foreach ($classmap as $class => $class_path) {
            if (
                $this->isInterestingFile(basename($class_path))
                && $this->getGUIClassNameFromClassPath($class_path) !== null
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
                            if (count($calls) > 0 || count($called) > 0) {
                                foreach ($calls as $call) {
                                    $this->temp_data[$call][] = $class;
                                }
                                $classes[strtolower($class)] = [
                                    self::CID => $this->generateCid($x),
                                    'calls' => $calls,
                                    self::CALLED => $called,
                                    self::CLASS_NAME => $class
                                ];
                            }
                            $x++;
                        }
                    } catch (Throwable $t) {

                    }
                }
                unset($content);
            }
        }

        foreach ($this->temp_data as $class => $called_bys) {
            foreach ($called_bys as $called_by) {
                if (!isset($classes[$class])) {
                    $classes[strtolower($class)] = [
                        self::CID => $this->generateCid($x),
                        self::CALLED => [],
                        self::CLASS_NAME => $class
                    ];
                }

                $classes[strtolower($class)][self::CALLED][] = strtolower($called_by);
            }
        }

        return $classes;
    }

    protected function isInterestingFile(string $file) : bool
    {
        try {
            return (bool) preg_match(self::INTERESTING_FILES_REGEXP, $file);
        } catch (Throwable $t) {
            return false;
        }
    }

    protected function generateCid(int $cnt) : string
    {
        return base_convert((string) $cnt, 10, 36);
    }

    protected function getGUIClassNameFromClassPath(string $path) : ?string
    {
        $res = [];
        if (preg_match(self::GUI_CLASS_FILE_REGEXP, $path, $res)) {
            return strtolower($res[1]);
        }
        return null;
    }

    protected function containsClassDefinitionFor(string $class, string $content) : bool
    {
        $regexp = "~.*class\s+$class~mi";
        return preg_match($regexp, $content) != 0;
    }


    // ----------------------
    // ILCTRL DECLARATION FINDING
    // ----------------------

    /**
     * @return null|(string,string[])
     */
    protected function getCalls(string $content) : ?array
    {
        return $this->getIlCtrlDeclarations($content, "ilctrl_calls");
    }

    /**
     * @return null|(string,string[])
     */
    protected function getCalledBys(string $content) : ?array
    {
        return $this->getIlCtrlDeclarations($content, "ilctrl_iscalledby");
    }

    /**
     * @return null|(string,string[])
     */
    protected function getIlCtrlDeclarations(string $content, string $which) : ?array
    {
        $regexp = str_replace("{WHICH}", $which, self::IL_CTRL_DECLARATION_REGEXP);
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
                $declaration[] = strtolower(trim($l));
            }
        }
        return $declaration;

        return [strtolower(trim($class_names[0])), $declaration];
    }

    protected function getILIASAbsolutePath() : string
    {
        if (defined("ILIAS_ABSOLUTE_PATH")) {
            return $this->normalizePath(ILIAS_ABSOLUTE_PATH);
        }

        return dirname(__FILE__, 5);
    }

    protected function normalizePath(string $path) : string
    {
        return realpath(str_replace(['//'], ['/'], $path));
    }
}
