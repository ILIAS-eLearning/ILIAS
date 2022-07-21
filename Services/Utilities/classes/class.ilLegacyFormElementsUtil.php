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
 * Util class
 * various functions, usage as namespace
 *
 * @author     Sascha Hofmann <saschahofmann@gmx.de>
 * @author     Alex Killing <alex.killing@gmx.de>
 *
 * @deprecated The 2021 Technical Board has decided to mark the ilUtil class as deprecated. The ilUtil is a historically
 * grown helper class with many different UseCases and functions. The class is not under direct maintainership and the
 * responsibilities are unclear. In this context, the class should no longer be used in the code and existing uses
 * should be converted to their own service in the medium term. If you need ilUtil for the implementation of a new
 * function in ILIAS > 7, please contact the Technical Board.
 */
class ilLegacyFormElementsUtil
{

    /**
     * @param string|int $a_str
     * @deprecated
     */
    public static function prepareFormOutput($a_str, bool $a_strip = false) : string
    {
        if ($a_strip) {
            $a_str = ilUtil::stripSlashes($a_str);
        }
        $a_str = htmlspecialchars($a_str);
        // Added replacement of curly brackets to prevent
        // problems with PEAR templates, because {xyz} will
        // be removed as unused template variable
        $a_str = str_replace("{", "&#123;", $a_str);
        $a_str = str_replace("}", "&#125;", $a_str);
        // needed for LaTeX conversion \\ in LaTeX is a line break
        // but without this replacement, php changes \\ to \
        $a_str = str_replace("\\", "&#92;", $a_str);
        return $a_str;
    }

    /**
     * Return a string of time period
     *
     * @deprecated
     */
    public static function period2String(ilDateTime $a_from, $a_to = null) : string
    {
        global $DIC;

        $lng = $DIC->language();

        if (!$a_to) {
            $a_to = new ilDateTime(time(), IL_CAL_UNIX);
        }

        $from = new DateTime($a_from->get(IL_CAL_DATETIME));
        $to = new DateTime($a_to->get(IL_CAL_DATETIME));
        $diff = $to->diff($from);

        $periods = [];
        $periods["years"] = $diff->format("%y");
        $periods["months"] = $diff->format("%m");
        $periods["days"] = $diff->format("%d");
        $periods["hours"] = $diff->format("%h");
        $periods["minutes"] = $diff->format("%i");
        $periods["seconds"] = $diff->format("%s");

        if (!array_sum($periods)) {
            return '';
        }
        $array = [];
        foreach ($periods as $key => $value) {
            if ($value) {
                $segment_name = ($value > 1)
                    ? $key
                    : substr($key, 0, -1);
                $array[] = $value . ' ' . $lng->txt($segment_name);
            }
        }

        if ($len = count($array) > 3) {
            $array = array_slice($array, 0, (3 - $len));
        }

        return implode(', ', $array);
    }

    /**
     * Prepares a string for a text area output where latex code may be in it
     * If the text is HTML-free, CHR(13) will be converted to a line break
     *
     * @param string $txt_output String which should be prepared for output
     * @access public
     * @return array|string|string[]|null
     */
    public static function prepareTextareaOutput(
        string $txt_output,
        bool $prepare_for_latex_output = false,
        bool $omitNl2BrWhenTextArea = false
    ) {
        $result = $txt_output;
        $is_html = ilUtil::isHTML($result);

        // removed: did not work with magic_quotes_gpc = On
        if (!$is_html) {
            if (!$omitNl2BrWhenTextArea) {
                // if the string does not contain HTML code, replace the newlines with HTML line breaks
                $result = preg_replace("/[\n]/", "<br />", $result);
            }
        } else {
            // patch for problems with the <pre> tags in tinyMCE
            if (preg_match_all("/(\<pre>.*?\<\/pre>)/ims", $result, $matches)) {
                foreach ($matches[0] as $found) {
                    $replacement = "";
                    if (strpos("\n", $found) === false) {
                        $replacement = "\n";
                    }
                    $removed = preg_replace("/\<br\s*?\/>/ims", $replacement, $found);
                    $result = str_replace($found, $removed, $result);
                }
            }
        }

        // since server side mathjax rendering does include svg-xml structures that indeed have linebreaks,
        // do latex conversion AFTER replacing linebreaks with <br>. <svg> tag MUST NOT contain any <br> tags.
        if ($prepare_for_latex_output) {
            $result = ilMathJax::getInstance()->insertLatexImages($result, "\<span class\=\"latex\">", "\<\/span>");
            $result = ilMathJax::getInstance()->insertLatexImages($result, "\[tex\]", "\[\/tex\]");
        }

        if ($prepare_for_latex_output) {
            // replace special characters to prevent problems with the ILIAS template system
            // eg. if someone uses {1} as an answer, nothing will be shown without the replacement
            $result = str_replace("{", "&#123;", $result);
            $result = str_replace("}", "&#125;", $result);
            $result = str_replace("\\", "&#92;", $result);
        }

        return $result;
    }

    /**
     * Creates a combination of HTML selects for time inputs
     *
     * Creates a combination of HTML selects for time inputs.
     * The select names are $prefix[h] for hours, $prefix[m]
     * for minutes and $prefix[s] for seconds.
     *
     * @access    public
     * @param string  $prefix Prefix of the select name
     * @param boolean $short  Set TRUE for a short time input (only hours and minutes). Default is TRUE
     * @param integer $hour   Default hour value
     * @param integer $minute Default minute value
     * @param integer $second Default second value
     * @deprecated
     */
    public static function makeTimeSelect(
        string $prefix,
        bool $short = true,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        bool $a_use_default = true,
        array $a_further_options = []
    ) : string {
        global $DIC;

        $lng = $DIC->language();
        $ilUser = $DIC->user();

        $minute_steps = 1;
        $disabled = '';
        if (count($a_further_options)) {
            if (isset($a_further_options['minute_steps'])) {
                $minute_steps = $a_further_options['minute_steps'];
            }
            if (isset($a_further_options['disabled']) and $a_further_options['disabled']) {
                $disabled = 'disabled="disabled" ';
            }
        }

        if ($a_use_default and !strlen("$hour$minute$second")) {
            $now = localtime();
            $hour = $now[2];
            $minute = $now[1];
            $second = $now[0];
        }

        // build hour select
        $sel_hour = '<select ';
        if (isset($a_further_options['select_attributes'])) {
            foreach ($a_further_options['select_attributes'] as $name => $value) {
                $sel_hour .= $name . '=' . $value . ' ';
            }
        }
        $sel_hour .= " " . $disabled . "name=\"" . $prefix . "[h]\" id=\"" . $prefix . "_h\" class=\"form-control\">\n";

        $format = $ilUser->getTimeFormat();
        for ($i = 0; $i <= 23; $i++) {
            if ($format == ilCalendarSettings::TIME_FORMAT_24) {
                $sel_hour .= "<option value=\"$i\">" . sprintf("%02d", $i) . "</option>\n";
            } else {
                $sel_hour .= "<option value=\"$i\">" . date("ga", mktime($i, 0, 0)) . "</option>\n";
            }
        }
        $sel_hour .= "</select>\n";
        $sel_hour = preg_replace("/(value\=\"$hour\")/", "$1 selected=\"selected\"", $sel_hour);

        // build minutes select
        $sel_minute = "<select " . $disabled . "name=\"" . $prefix . "[m]\" id=\"" . $prefix . "_m\" class=\"form-control\">\n";

        for ($i = 0; $i <= 59; $i = $i + $minute_steps) {
            $sel_minute .= "<option value=\"$i\">" . sprintf("%02d", $i) . "</option>\n";
        }
        $sel_minute .= "</select>\n";
        $sel_minute = preg_replace("/(value\=\"$minute\")/", "$1 selected=\"selected\"", $sel_minute);

        if (!$short) {
            // build seconds select
            $sel_second = "<select " . $disabled . "name=\"" . $prefix . "[s]\" id=\"" . $prefix . "_s\" class=\"form-control\">\n";

            for ($i = 0; $i <= 59; $i++) {
                $sel_second .= "<option value=\"$i\">" . sprintf("%02d", $i) . "</option>\n";
            }
            $sel_second .= "</select>\n";
            $sel_second = preg_replace("/(value\=\"$second\")/", "$1 selected=\"selected\"", $sel_second);
        }
        $timeformat = ($lng->text["lang_timeformat"] ?? '');
        if (strlen($timeformat) == 0) {
            $timeformat = "H:i:s";
        }
        $timeformat = strtolower(preg_replace("/\W/", "", $timeformat));
        $timeformat = preg_replace("/(\w)/", "%%$1", $timeformat);
        $timeformat = preg_replace("/%%h/", $sel_hour, $timeformat);
        $timeformat = preg_replace("/%%i/", $sel_minute, $timeformat);
        if ($short) {
            $timeformat = preg_replace("/%%s/", "", $timeformat);
        } else {
            $timeformat = preg_replace("/%%s/", $sel_second, $timeformat);
        }
        return $timeformat;
    }

    /**
     * @deprecated
     */
    public static function formCheckbox(
        bool $checked,
        string $varname,
        string $value,
        bool $disabled = false
    ) : string {
        $str = "<input type=\"checkbox\" name=\"" . $varname . "\"";

        if ($checked === true) {
            $str .= " checked=\"checked\"";
        }

        if ($disabled === true) {
            $str .= " disabled=\"disabled\"";
        }

        $array_var = false;

        if (substr($varname, -2) == "[]") {
            $array_var = true;
        }

        // if varname ends with [], use varname[-2] + _ + value as id tag (e.g. "user_id[]" => "user_id_15")
        if ($array_var) {
            $varname_id = substr($varname, 0, -2) . "_" . $value;
        } else {
            $varname_id = $varname;
        }

        // dirty removal of other "[]" in string
        $varname_id = str_replace("[", "_", $varname_id);
        $varname_id = str_replace("]", "", $varname_id);

        $str .= " value=\"" . $value . "\" id=\"" . $varname_id . "\" />\n";

        return $str;
    }

    /**
     * Builds a select form field with options and shows the selected option first
     *
     * @access    public
     * @param string/array    value to be selected
     * @param string            variable name in formular
     * @param array            array with $options (key = lang_key, value = long name)
     * @param boolean            multiple selection list true/false
     * @param boolean            if true, the option values are displayed directly, otherwise
     *                            they are handled as language variable keys and the corresponding
     *                            language variable is displayed
     * @param int                size
     * @param string            style class
     * @param array            additional attributes (key = attribute name, value = attribute value)
     * @param boolean            disabled
     * @deprecated
     */
    public static function formSelect(
        $selected,
        string $varname,
        array $options,
        bool $multiple = false,
        bool $direct_text = false,
        int $size = 0,
        string $style_class = "",
        array $attribs = [],
        bool $disabled = false
    ) : string {
        global $DIC;

        $lng = $DIC->language();

        if ($multiple == true) {
            $multiple = " multiple=\"multiple\"";
        } else {
            $multiple = "";
            $size = 0;
        }

        $class = " class=\" form-control " . $style_class . "\"";

        // use form-inline!
        // this is workaround the whole function should be set deprecated
        // $attributes = " style='display:inline-block;' ";

        $attributes = "";
        if (is_array($attribs)) {
            foreach ($attribs as $key => $val) {
                $attributes .= " " . $key . "=\"" . $val . "\"";
            }
        }
        if ($disabled) {
            $disabled = ' disabled=\"disabled\"';
        }

        $size_str = "";
        if ($size > 0) {
            $size_str = ' size="' . $size . '" ';
        }
        $str = "<select name=\"" . $varname . "\"" . $multiple . " $class " . $size_str . " $attributes $disabled>\n";

        foreach ($options as $key => $val) {
            $style = "";
            if (is_array($val)) {
                $style = $val["style"];
                $val = $val["text"];        // mus be last line, since we overwrite
            }

            $sty = ($style != "")
                ? ' style="' . $style . '" '
                : "";

            if ($direct_text) {
                $str .= " <option $sty value=\"" . $key . "\"";
            } else {
                $str .= " <option $sty value=\"" . $val . "\"";
            }
            if (is_array($selected)) {
                if (in_array($key, $selected)) {
                    $str .= " selected=\"selected\"";
                }
            } elseif ($selected == $key) {
                $str .= " selected=\"selected\"";
            }

            if ($direct_text) {
                $str .= ">" . $val . "</option>\n";
            } else {
                $str .= ">" . $lng->txt($val) . "</option>\n";
            }
        }

        $str .= "</select>\n";

        return $str;
    }

    /**
     * @deprecated
     */
    public static function formRadioButton(
        bool $checked,
        string $varname,
        string $value,
        string $onclick = null,
        bool $disabled = false
    ) : string {
        $str = '<input ';

        if ($onclick !== null) {
            $str .= ('onclick="' . $onclick . '"');
        }

        $str .= (" type=\"radio\" name=\"" . $varname . "\"");
        if ($checked === true) {
            $str .= " checked=\"checked\"";
        }

        if ($disabled === true) {
            $str .= " disabled=\"disabled\"";
        }

        $str .= " value=\"" . $value . "\"";

        $str .= " id=\"" . $value . "\" />\n";

        return $str;
    }
}
