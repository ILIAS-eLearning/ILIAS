<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * Search for deprecated lang vars
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ServicesLanguage
 */
class ilLangDeprecated
{
    private const ILIAS_CLASS_FILE_RE = "class\..*\.php$";

    protected array $candidates;
    private \ilDBInterface $db;

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
     */
    public function getDeprecatedLangVars() : array
    {
        $this->getCandidates();
        $this->parseCodeFiles();
        return $this->candidates;
    }

    /**
     * Get candidates from the db. Base are all lang vars of the
     * english lang file reduced by the ones being accessed (having entries in lng_log)
     */
    protected function getCandidates() : void
    {
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
    protected function parseCodeFiles() : void
    {
        foreach ($this->getCodeFiles(ILIAS_ABSOLUTE_PATH) as $file) {
            $this->parseCodeFile($file->getPathname());
        }
    }

    /**
     * Get code files
     */
    protected function getCodeFiles(string $path) : \Generator
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
     */
    protected function parseCodeFile(string $file_path) : void
    {
        $tokens = token_get_all(file_get_contents($file_path));
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
                    if ($lv !== "") {
                        unset($this->candidates[$lv]);
                    }
                    break;
            }
        }
    }
}
