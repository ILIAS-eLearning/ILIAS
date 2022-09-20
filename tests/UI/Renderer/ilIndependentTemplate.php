<?php

declare(strict_types=1);

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
 *********************************************************************/

require_once("libs/composer/vendor/autoload.php");

// Do the require-dance for ilTemplate.
require_once("./Services/UICore/lib/html-it/IT.php");
require_once("./Services/UICore/lib/html-it/ITX.php");
require_once("./Services/UICore/classes/class.ilTemplate.php");

class ilIndependentGlobalTemplate extends ilGlobalTemplate implements \ILIAS\UI\Implementation\Render\Template
{
    public function __construct(
        $file,
        $flag1,
        $flag2,
        $in_module = '',
        $vars = self::DEFAULT_BLOCK,
        $plugin = false,
        $a_use_cache = true
    ) {
        $this->setBodyClass("std");
        $this->template = new ilIndependantTemplate($file, $flag1, $flag2, $in_module, $vars, $plugin, $a_use_cache);
    }

    // Small adjustment to fit \ILIAS\UI\Implementation\Template and call to
    public function get(
        string $part = null,
        bool $add_error_mess = false,
        bool $handle_referer = false,
        bool $add_ilias_footer = false,
        bool $add_standard_elements = false,
        bool $a_main_menu = true,
        bool $a_tabs = true
    ): string {
        return $this->template->get($part);
    }
}

class ilIndependantTemplate extends ilTemplate
{
    /**
     * Reads a file from disk and returns its content.
     * Copy from Service/PEAR/lib/HTML/Template/IT.php with GlobalCache-stuff
     * removed.
     */
    public function getFile(string $filename): string
    {
        if ($filename[0] === '/' && substr($this->fileRoot, -1) === '/') {
            $filename = substr($filename, 1);
        }

        $filename = $this->fileRoot . $filename;

        require_once('./Services/GlobalCache/classes/class.ilGlobalCache.php');
        $this->real_filename = $filename;

        if (!($fh = @fopen($filename, 'rb'))) {
            $this->err[] = (new PEAR())->raiseError(
                $this->errorMessage(self::IT_TPL_NOT_FOUND) .
                ': "' . $filename . '"',
                self::IT_TPL_NOT_FOUND
            );
            return "";
        }

        $fsize = filesize($filename);
        if ($fsize < 1) {
            fclose($fh);
            return '';
        }

        $content = fread($fh, $fsize);
        fclose($fh);

        return preg_replace_callback(
            "#<!-- INCLUDE (.*) -->#im",
            function ($hit) {
                return $this->getFile($hit[1]);
            },
            $content
        );
    }

    /**
     * Reads a template file from the disk.
     * unoverwrites IT:loadTemplateFile to deinclude the template input hook
     */
    public function loadTemplatefile(
        string $filename,
        bool $removeUnknownVariables = true,
        bool $removeEmptyBlocks = true
    ): bool {
        return HTML_Template_IT::loadTemplatefile($filename, $removeUnknownVariables, $removeEmptyBlocks);
    }

    // Small adjustment to fit \ILIAS\UI\Implementation\Template and call to
    public function get(string $part = null): string
    {
        if ($part === null) {
            $part = self::IT_DEFAULT_BLOCK;
        }
        if ($part === self::IT_DEFAULT_BLOCK && !$this->flagGlobalParsed) {
            $this->parse(self::IT_DEFAULT_BLOCK);
        }

        if (!isset($this->blocklist[$part])) {
            throw (new ilTemplateException($this->errorMessage(self::IT_BLOCK_NOT_FOUND) .
                '"' . $part . "'"));
        }

        if (isset($this->blockdata[$part])) {
            $ret = $this->blockdata[$part];
            if ($this->clearCache) {
                unset($this->blockdata[$part]);
            }
            if ($this->_options['preserve_data']) {
                $ret = str_replace(
                    $this->openingDelimiter .
                    '%preserved%' . $this->closingDelimiter,
                    $this->openingDelimiter,
                    $ret
                );
            }
            return $ret;
        }

        return '';
    }
}
