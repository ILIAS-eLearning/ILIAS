<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

// Do the require-dance for ilTemplate.
$cur_cwd = getcwd();
chdir(__DIR__."/../..");
require_once("include/inc.get_pear.php");
require_once("include/inc.check_pear.php");
require_once("PEAR.php");
require_once("HTML/Template/ITX.php");
require_once("./Services/UICore/classes/class.ilTemplateHTMLITX.php");
require_once("./Services/UICore/classes/class.ilTemplate.php");
chdir($cur_cwd);

class ilIndependentTemplate extends ilTemplate implements \ILIAS\UI\Implementation\Template {
    /**
     * Reads a file from disk and returns its content.
	 *
	 * Copy from Service/PEAR/lib/HTML/Template/IT.php with GlobalCache-stuff
	 * removed.
	 *
     * @param    string    Filename
     * @return   string    Filecontent
     */
    function getFile($filename)
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
                ': "' .$filename .'"',
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
     * @param    string      name of the template file
     * @param    bool        how to handle unknown variables.
     * @param    bool        how to handle empty blocks.
     * @access   public
     * @return   boolean    false on failure, otherwise true
     * @see      $template, setTemplate(), $removeUnknownVariables,
     *           $removeEmptyBlocks
     */
    function loadTemplatefile( $filename,
                               $removeUnknownVariables = true,
                               $removeEmptyBlocks = true )
    {
		return HTML_Template_IT::loadTemplatefile($filename, $removeUnknownVariables, $removeEmptyBlocks);
	}

	// Small adjustment to fit \ILIAS\UI\Implementation\Template and call to
	public function get($name = null) {
		if ($name === null) {
			$name = "__global__";
		}
		return ilTemplateX::get($name);
	}
}
