<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Search for deprecated lang vars
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ServicesLanguage
 */
class ilLangDeprecated
{
    const ILIAS_CLASS_FILE_RE = 'class\..*\.php$';

    /**
     * @var array
     */
    protected $candidates = array();

    /**
     * ilLangDeprecated constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
    }

    /**
     * Get deprecated lang vars
     *
     * @return array
     */
    public function getDeprecatedLangVars()
    {
        $this->getCandidates();
        $this->parseCodeFiles();
        return $this->candidates;
    }

    /**
     * Get candidates from the db. Base are all lang vars of the
     * english lang file reduced by the ones being accessed (having entries in lng_log)
     */
    protected function getCandidates()
    {
        $this->candidates = array();

        $log = array();
        $set = $this->db->query("SELECT module, identifier FROM lng_log ");
        while ($rec = $this->db->fetchAssoc($set)) {
            $log[$rec["module"] . ":" . $rec["identifier"]] = 1;
        }
        $set = $this->db->query("SELECT module, identifier FROM lng_data WHERE lang_key = " .
            $this->db->quote("en", "text") . " ORDER BY module, identifier");
        while ($rec = $this->db->fetchAssoc($set)) {
            if (!isset($log[$rec["module"] . ":" . $rec["identifier"]])) {
                $this->candidates[$rec["identifier"]] = $rec["module"];
            }
        }
    }

    /**
     * Parse Code Files
     */
    protected function parseCodeFiles()
    {
        foreach ($this->getCodeFiles(ILIAS_ABSOLUTE_PATH) as $file) {
            $this->parseCodeFile($file->getPathname());
        }
    }

    /**
     * @param $path string
     * @return \Generator
     */
    protected function getCodeFiles($path)
    {
        foreach (
            new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path),
                    \RecursiveIteratorIterator::SELF_FIRST,
                    \RecursiveIteratorIterator::CATCH_GET_CHILD
                ),
                '/' . self::ILIAS_CLASS_FILE_RE . '/i'
            ) as $file
        ) {
            yield $file;
        }
    }

    /**
     * Parse code file and reduce candidates
     *
     * @param $file_path
     */
    protected function parseCodeFile($file_path)
    {
        $tokens = token_get_all(file_get_contents($file_path));

        /*if (is_int(strpos($file_path, "TrackingItemsTableGUI")))
        {
            $transl = array_map(function($e) {
                return array(token_name($e[0]), $e[1], $e[2]);
            }, $tokens);

            var_dump($transl); exit;
        }*/

        $num_tokens = count($tokens);

        for ($i = 0; $i < $num_tokens; $i++) {
            if (is_string($tokens[$i])) {
                continue;
            }

            $token = $tokens[$i][0];
            switch ($token) {

                case T_STRING:
                case T_CONSTANT_ENCAPSED_STRING:
                    $lv = str_replace(array("'", '"'), "", $tokens[$i][1]);
                    if ($lv != "") {
                        unset($this->candidates[$lv]);
                    }
                    break;
            }
        }
    }
}
