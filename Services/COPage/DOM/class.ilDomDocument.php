<?php

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

/**
 * Dom document wrapper.
 * @author Alexander Killing <killing@leifos.de>
 * @deprecated
 */
class ilDomDocument
{
    private DOMDocument $doc;
    private array $errors = array();

    /**
     * Constructor
     * @param DOMDocument        PHP dom document
     */
    public function __construct()
    {
        $this->doc = new DOMDocument();
    }

    public function __call(string $a_method, array $a_args)
    {
        if (in_array($a_method, array("validate", "loadXML"))) {
            set_error_handler(array($this, "handleError"));
            $rv = call_user_func_array(array($this->doc, $a_method), $a_args);
            restore_error_handler();
            return $rv;
        } else {
            return call_user_func_array(array($this->doc, $a_method), $a_args);
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function handleError(
        int $a_no,
        string $a_string,
        string $a_file = null,
        int $a_line = null,
        array $a_context = null
    ): void {
        $pos = strpos($a_string, "]:");
        $err = trim(substr($a_string, $pos + 2));
        $this->errors[] = $err;
    }
}
