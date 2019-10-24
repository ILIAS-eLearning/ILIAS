<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

// Do the require-dance for ilTemplate.
require_once("./Services/UICore/lib/html-it/IT.php");
require_once("./Services/UICore/lib/html-it/ITX.php");
require_once("./Services/UICore/classes/class.ilTemplate.php");

class ilIndependentTemplate extends ilTemplate implements \ILIAS\UI\Implementation\Render\Template
{
    // This makes PHP happy, baseclass needs that
    protected $blockparents = null;

    public function __construct(
        $file,
        $flag1,
        $flag2,
        $in_module = false,
        $vars = "DEFAULT",
        $plugin = false,
        $a_use_cache = true
    ) {
        parent::__construct($file, $flag1, $flag2, $in_module, $vars, $plugin, false);
    }

    /**
     * Reads a file from disk and returns its content.
     *
     * Copy from Service/PEAR/lib/HTML/Template/IT.php with GlobalCache-stuff
     * removed.
     *
     * @param	string	Filename
     * @return   string	Filecontent
    */
    public function getFile($filename)
    {
        if ($filename{0} == '/' && substr($this->fileRoot, -1) == '/') {
            $filename = substr($filename, 1);
        }

        $filename = $this->fileRoot . $filename;

        require_once('./Services/GlobalCache/classes/class.ilGlobalCache.php');
        $this->real_filename = $filename;

        if (!($fh = @fopen($filename, 'r'))) {
            $this->err[] = PEAR::raiseError(
                $this->errorMessage(IT_TPL_NOT_FOUND) .
                ': "' . $filename . '"',
                IT_TPL_NOT_FOUND
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
    } // end func getFile

    /**
     * Reads a template file from the disk.
     *
     * unoverwrites IT:loadTemplateFile to deinclude the template input hook
     *
     * @param	string	  name of the template file
     * @param	bool		how to handle unknown variables.
     * @param	bool		how to handle empty blocks.
     * @access   public
     * @return   boolean	false on failure, otherwise true
     * @see	  $template, setTemplate(), $removeUnknownVariables,
     *		   $removeEmptyBlocks
     */
    public function loadTemplatefile(
        $filename,
        $removeUnknownVariables = true,
        $removeEmptyBlocks = true
    ) {
        return HTML_Template_IT::loadTemplatefile($filename, $removeUnknownVariables, $removeEmptyBlocks);
    }

    // Small adjustment to fit \ILIAS\UI\Implementation\Template and call to
    public function get(
        $part = null,
        $add_error_mess = false,
        $handle_referer = false,
        $add_ilias_footer = false,
        $add_standard_elements = false,
        $a_main_menu = true,
        $a_tabs = true
    ) {
        if ($part === null) {
            $part = "__global__";
        }
        if ($part == '__global__'  && !$this->flagGlobalParsed) {
            $this->parse('__global__');
        }

        if (!isset($this->blocklist[$part])) {
            throw (new ilTemplateException($this->errorMessage(IT_BLOCK_NOT_FOUND) .
                '"' . $block . "'"));
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
