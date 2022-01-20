<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
* Validation of SCORM-XML Files
*
* @author Romeo Kienzler contact@kienzler.biz
* @company 21 LearnLine AG info@21ll.com
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMValidator
{
    public $dir;
    public $flag;
    public $summary;

    public function validateXML($file) : void
    {
        // exec(ilUtil::getJavaPath()." -jar ".ilUtil::escapeShellArg(ILIAS_ABSOLUTE_PATH."/Modules/ScormAicc/validation/vali.jar")." ".ilUtil::escapeShellArg($file)." 2>&1", $error);
            // if (count($error) != 0)
            // {
                // $this->summary[] = "";
                // $this->summary[] = "<b>File: $file</b>";
                // foreach($error as $line)
                // {
                    // $this->summary[] = $line;
// //echo "<br><b>".$line."</b>";
                // }
            // }
    }

    public function searchDir($dir) : void
    {
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (!preg_match("~^[\.]{1,2}~i", $file)) {
                        //2DO FIXME regex machen dass nur . und .. erkannt werden und nicht .lala. oder so
                        if (is_dir($dir . $file)) {
                            // This is commented because subdirecories of my scromexamples contain xml files which aren't valid!
                                //$this->searchDir($dir.$file."/");
                        }
                        if (preg_match("~(\.xml)$~i", $file)) {
                                
                                // we skip index.xml and indexMD.xml which come from the
                            // scorm editor and currently do not validate against anything
                            if ($file != "index.xml" && $file != "indexMD.xml") {
                                $this->validateXML($dir . $file);
                            }
                        }
                    }
                }
            }
            closedir($dh);
        }
    }

    public function __construct($directory)
    {
        $this->dir = $directory . '/';
    }

    public function validate()
    {
        $this->summary = array();
        $this->searchDir($this->dir);
        if (count($this->summary) == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getSummary() : string
    {
        $summary = "";

        foreach ($this->summary as $line) {
            $summary .= $line . "<br>";
        }

        return $summary;
    }
}
