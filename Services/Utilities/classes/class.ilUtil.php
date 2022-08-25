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

use ILIAS\FileDelivery\Delivery;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Cookies\CookieFactoryImpl;

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
class ilUtil
{
    /**
     * Builds an html image tag
     *
     * @deprecated Use UI-Service!
     */
    public static function getImageTagByType(string $a_type, string $a_path, bool $a_big = false): string
    {
        global $DIC;

        $lng = $DIC->language();

        $size = ($a_big)
            ? "big"
            : "small";

        $filename = ilObject::_getIcon(0, $size, $a_type);

        return "<img src=\"" . $filename . "\" alt=\"" . $lng->txt("obj_" . $a_type) . "\" title=\"" . $lng->txt(
            "obj_" . $a_type
        ) . "\" border=\"0\" vspace=\"0\"/>";
    }

    /**
     * get image path (for images located in a template directory)
     *
     * @deprecated use UI Service!
     *
     */
    public static function getImagePath(
        string $img,
        string $module_path = "",
        string $mode = "output",
        bool $offline = false
    ): string {
        global $DIC;

        $styleDefinition = null;
        if (isset($DIC["styleDefinition"])) {
            $styleDefinition = $DIC["styleDefinition"];
        }

        if (is_int(strpos($_SERVER["PHP_SELF"], "setup.php"))) {
            $module_path = "..";
        }
        if ($module_path != "") {
            $module_path = "/" . $module_path;
        }

        // default image
        $default_img = "." . $module_path . "/templates/default/images/" . $img;

        // use ilStyleDefinition instead of account to get the current skin and style
        $current_skin = ilStyleDefinition::getCurrentSkin();
        $current_style = ilStyleDefinition::getCurrentStyle();

        if (is_object($styleDefinition)) {
            $image_dir = $styleDefinition->getImageDirectory($current_style);
        }
        $skin_img = "";
        if ($current_skin == "default") {
            $user_img = "." . $module_path . "/templates/default/" . $image_dir . "/" . $img;
            $skin_img = "." . $module_path . "/templates/default/images/" . $img;
        } elseif (is_object($styleDefinition) && $current_skin != "default") {
            $user_img = "./Customizing/global/skin/" .
                $current_skin . $module_path . "/" . $image_dir . "/" . $img;
            $skin_img = "./Customizing/global/skin/" .
                $current_skin . $module_path . "/images/" . $img;
        }


        if ($offline) {
            return "./images/" . $img;
        } elseif (@file_exists($user_img) && $image_dir != "") {
            return $user_img;        // found image for skin and style
        } elseif (file_exists($skin_img)) {
            return $skin_img;        // found image in skin/images
        }

        return $default_img;            // take image in default
    }

    /**
     * get url of path
     *
     * @param $relative_path string: complete path to file, relative to web root (e.g.  /data/pfplms103/mobs/mm_732/athena_standing.jpg)
     * @deprecated
     */
    public static function getHtmlPath(string $relative_path): string
    {
        if (substr($relative_path, 0, 2) == './') {
            $relative_path = (substr($relative_path, 1));
        }
        if (substr($relative_path, 0, 1) != '/') {
            $relative_path = '/' . $relative_path;
        }
        $htmlpath = ILIAS_HTTP_PATH . $relative_path;
        return $htmlpath;
    }

    /**
     * get full style sheet file name (path inclusive) of current user
     *
     * @param $mode           string Output mode of the style sheet ("output" or "filesystem"). !"filesystem" generates the ILIAS
     *                        version number as attribute to force the reload of the style sheet in a different ILIAS version
     * @param $a_css_name     string The name of the style sheet. If empty, the default style name will be chosen
     * @param $a_css_location string The location of the style sheet e.g. a module path. This parameter only makes sense
     *                        when $a_css_name is used
     * @deprecated
     */
    public static function getStyleSheetLocation(
        string $mode = "output",
        string $a_css_name = "",
        string $a_css_location = ""
    ): string {
        global $DIC;

        $ilSetting = $DIC->settings();

        // add version as parameter to force reload for new releases
        // use ilStyleDefinition instead of account to get the current style
        $stylesheet_name = (strlen($a_css_name))
            ? $a_css_name
            : ilStyleDefinition::getCurrentStyle() . ".css";
        if (strlen($a_css_location) && (strcmp(substr($a_css_location, -1), "/") != 0)) {
            $a_css_location = $a_css_location . "/";
        }

        $filename = "";
        // use ilStyleDefinition instead of account to get the current skin
        if (ilStyleDefinition::getCurrentSkin() != "default") {
            $filename = "./Customizing/global/skin/" . ilStyleDefinition::getCurrentSkin(
            ) . "/" . $a_css_location . $stylesheet_name;
        }
        if (strlen($filename) == 0 || !file_exists($filename)) {
            $filename = "./" . $a_css_location . "templates/default/" . $stylesheet_name;
        }
        return $filename;
    }

    /**
     * get full style sheet file name (path inclusive) of current user
     *
     * @deprecated
     */
    public static function getNewContentStyleSheetLocation(string $mode = "output"): string
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        // add version as parameter to force reload for new releases
        if ($mode != "filesystem") {
            $vers = str_replace(" ", "-", ILIAS_VERSION);
            $vers = "?vers=" . str_replace(".", "-", $vers);
        }

        // use ilStyleDefinition instead of account to get the current skin and style
        if (ilStyleDefinition::getCurrentSkin() == "default") {
            $in_style = "./templates/" . ilStyleDefinition::getCurrentSkin() . "/"
                . ilStyleDefinition::getCurrentStyle() . "_cont.css";
        } else {
            $in_style = "./Customizing/global/skin/" . ilStyleDefinition::getCurrentSkin() . "/"
                . ilStyleDefinition::getCurrentStyle() . "_cont.css";
        }

        if (is_file("./" . $in_style)) {
            return $in_style . $vers;
        } else {
            return "templates/default/delos_cont.css" . $vers;
        }
    }

    /**
     * switches style sheets for each even $a_num
     * (used for changing colors of different result rows)
     *
     * @deprecated
     */
    public static function switchColor(int $a_num, string $a_css1, string $a_css2): string
    {
        if (!($a_num % 2)) {
            return $a_css1;
        } else {
            return $a_css2;
        }
    }

    /**
     * @depracated Use the respective `Refinery` transformation `$refinery->string()->makeClickable("foo bar")` to convert URL-like string parts to an HTML anchor (`<a>`) element (the boolean flag is removed)
     */
    public static function makeClickable(string $a_text, bool $detectGotoLinks = false): string
    {
        global $DIC;

        return $DIC->refinery()->string()->makeClickable()->transform($a_text);
    }

    /**
     * This preg-based function checks whether an e-mail address is formally valid.
     * It works with all top level domains including the new ones (.biz, .info, .museum etc.)
     * and the special ones (.arpa, .int etc.)
     * as well as with e-mail addresses based on IPs (e.g. webmaster@123.45.123.45)
     * Valid top level domains: http://data.iana.org/TLD/tlds-alpha-by-domain.txt
     *
     * @deprecated use ilMailRfc822AddressParserFactory directly
     */
    public static function is_email(
        string $a_email,
        ilMailRfc822AddressParserFactory $mailAddressParserFactory = null
    ): bool {
        if ($mailAddressParserFactory === null) {
            $mailAddressParserFactory = new ilMailRfc822AddressParserFactory();
        }

        try {
            $parser = $mailAddressParserFactory->getParser($a_email);
            $addresses = $parser->parse();
            return count($addresses) == 1 && $addresses[0]->getHost() != ilMail::ILIAS_HOST;
        } catch (ilException $e) {
            return false;
        }
    }

    /**
     * @deprecated
     */
    public static function isLogin(string $a_login): bool
    {
        if (empty($a_login)) {
            return false;
        }

        if (strlen($a_login) < 3) {
            return false;
        }

        // FIXME - If ILIAS is configured to use RFC 822
        //         compliant mail addresses we should not
        //         allow the @ character.
        if (!preg_match("/^[A-Za-z0-9_\.\+\*\@!\$\%\~\-]+$/", $a_login)) {
            return false;
        }

        return true;
    }

    /**
     * Build img tag
     *
     * @static
     * @deprecated
     */
    public static function img(
        string $a_src,
        ?string $a_alt = null,
        $a_width = "",
        $a_height = "",
        $a_border = 0,
        $a_id = "",
        $a_class = ""
    ) {
        $img = '<img src="' . $a_src . '"';
        if (!is_null($a_alt)) {
            $img .= ' alt="' . htmlspecialchars($a_alt) . '"';
        }
        if ($a_width != "") {
            $img .= ' width="' . htmlspecialchars($a_width) . '"';
        }
        if ($a_height != "") {
            $img .= ' height="' . htmlspecialchars($a_height) . '"';
        }
        if ($a_class != "") {
            $img .= ' class="' . $a_class . '"';
        }
        if ($a_id != "") {
            $img .= ' id="' . $a_id . '"';
        }
        $img .= ' />';

        return $img;
    }

    /**
     * @deprecated use ilFileDelivery
     */
    public static function deliverData(
        string $a_data,
        string $a_filename,
        string $mime = "application/octet-stream"
    ): void {
        global $DIC;
        $delivery = new Delivery(
            Delivery::DIRECT_PHP_OUTPUT,
            $DIC->http()
        );
        $delivery->setMimeType($mime);
        $delivery->setSendMimeType(true);
        $delivery->setDisposition(Delivery::DISP_ATTACHMENT);
        $delivery->setDownloadFileName($a_filename);
        $delivery->setConvertFileNameToAsci(true);
        $repsonse = $DIC->http()->response()->withBody(Streams::ofString($a_data));
        $DIC->http()->saveResponse($repsonse);
        $delivery->deliver();
    }

    /**
     * @deprecated
     */
    public static function appendUrlParameterString(string $a_url, string $a_par, bool $xml_style = false): string
    {
        $amp = $xml_style
            ? "&amp;"
            : "&";

        $url = (is_int(strpos($a_url, "?")))
            ? $a_url . $amp . $a_par
            : $a_url . "?" . $a_par;

        return $url;
    }

    /**
     * @deprecated
     */
    public static function stripSlashes(string $a_str, bool $a_strip_html = true, string $a_allow = ""): string
    {
        if (ini_get("magic_quotes_gpc")) {
            $a_str = stripslashes($a_str);
        }

        return ilUtil::secureString($a_str, $a_strip_html, $a_allow);
    }

    /**
     * @deprecated
     */
    public static function stripOnlySlashes(string $a_str): string
    {
        if (ini_get("magic_quotes_gpc")) {
            $a_str = stripslashes($a_str);
        }

        return $a_str;
    }

    /**
     * @deprecated
     */
    public static function secureString(string $a_str, bool $a_strip_html = true, string $a_allow = ""): string
    {
        // check whether all allowed tags can be made secure
        $only_secure = true;
        $allow_tags = explode(">", $a_allow);
        $sec_tags = ilUtil::getSecureTags();
        $allow_array = [];
        foreach ($allow_tags as $allow) {
            if ($allow != "") {
                $allow = str_replace("<", "", $allow);

                if (!in_array($allow, $sec_tags)) {
                    $only_secure = false;
                }
                $allow_array[] = $allow;
            }
        }

        // default behaviour: allow only secure tags 1:1
        if (($only_secure || $a_allow == "") && $a_strip_html) {
            if ($a_allow === "") {
                $allow_array = ["b",
                                "i",
                                "strong",
                                "em",
                                "code",
                                "cite",
                                "gap",
                                "sub",
                                "sup",
                                "pre",
                                "strike",
                                "bdo"
                ];
            }

            // this currently removes parts of strings like "a <= b"
            // because "a <= b" is treated like "<spam onclick='hurt()'>ss</spam>"
            $a_str = ilUtil::maskSecureTags($a_str, $allow_array);
            $a_str = strip_tags($a_str);        // strip all other tags
            $a_str = ilUtil::unmaskSecureTags($a_str, $allow_array);

        // a possible solution could be something like:
        // $a_str = str_replace("<", "&lt;", $a_str);
        // $a_str = str_replace(">", "&gt;", $a_str);
        // $a_str = ilUtil::unmaskSecureTags($a_str, $allow_array);
            //
        // output would be ok then, but input fields would show
        // "a &lt;= b" for input "a <= b" if data is brought back to a form
        } else {
            // only for scripts, that need to allow more/other tags and parameters
            if ($a_strip_html) {
                $a_str = ilUtil::stripScriptHTML($a_str, $a_allow);
            }
        }

        return $a_str;
    }

    public static function getSecureTags(): array
    {
        return ["strong",
                "em",
                "u",
                "strike",
                "ol",
                "li",
                "ul",
                "p",
                "div",
                "i",
                "b",
                "code",
                "sup",
                "sub",
                "pre",
                "gap",
                "a",
                "img",
                "bdo"
        ];
    }

    private static function maskSecureTags(string $a_str, array $allow_array): string
    {
        foreach ($allow_array as $t) {
            switch ($t) {
                case "a":
                    $a_str = ilUtil::maskAttributeTag($a_str, "a", "href");
                    break;

                case "img":
                    $a_str = ilUtil::maskAttributeTag($a_str, "img", "src");
                    break;

                case "p":
                case "div":
                    $a_str = ilUtil::maskTag($a_str, $t, [
                        ["param" => "align", "value" => "left"],
                        ["param" => "align", "value" => "center"],
                        ["param" => "align", "value" => "justify"],
                        ["param" => "align", "value" => "right"]
                    ]);
                    break;

                default:
                    $a_str = ilUtil::maskTag($a_str, $t);
                    break;
            }
        }

        return $a_str;
    }

    private static function unmaskSecureTags(string $a_str, array $allow_array): string
    {
        foreach ($allow_array as $t) {
            switch ($t) {
                case "a":
                    $a_str = ilUtil::unmaskAttributeTag($a_str, "a", "href");
                    break;

                case "img":
                    $a_str = ilUtil::unmaskAttributeTag($a_str, "img", "src");
                    break;

                case "p":
                case "div":
                    $a_str = ilUtil::unmaskTag($a_str, $t, [
                        ["param" => "align", "value" => "left"],
                        ["param" => "align", "value" => "center"],
                        ["param" => "align", "value" => "justify"],
                        ["param" => "align", "value" => "right"]
                    ]);
                    break;

                default:
                    $a_str = ilUtil::unmaskTag($a_str, $t);
                    break;
            }
        }

        return $a_str;
    }

    /**
     * @deprecated
     */
    public static function securePlainString(string $a_str): string
    {
        if (ini_get("magic_quotes_gpc")) {
            return stripslashes($a_str);
        } else {
            return $a_str;
        }
    }

    /**
     * Encodes a plain text string into HTML for display in a browser.
     * This function encodes HTML special characters: < > & with &lt; &gt; &amp;
     * and converts newlines into <br>
     *
     * If $a_make_links_clickable is set to true, URLs in the plain string which
     * are considered to be safe, are made clickable.
     *
     *
     * @param string the plain text string
     * @param boolean set this to true, to make links in the plain string
     * clickable.
     * @param boolean set this to true, to detect goto links
     * @static
     *
     */
    public static function htmlencodePlainString(
        string $a_str,
        bool $a_make_links_clickable,
        bool $a_detect_goto_links = false
    ): string {
        $encoded = "";

        if ($a_make_links_clickable) {
            // Find text sequences in the plain text string which match
            // the URI syntax rules, and pass them to ilUtil::makeClickable.
            // Encode all other text sequences in the plain text string using
            // htmlspecialchars and nl2br.
            // The following expressions matches URI's as specified in RFC 2396.
            //
            // The expression matches URI's, which start with some well known
            // schemes, like "http:", or with "www.". This must be followed
            // by at least one of the following RFC 2396 expressions:
            // - alphanum:           [a-zA-Z0-9]
            // - reserved:           [;\/?:|&=+$,]
            // - mark:               [\\-_.!~*\'()]
            // - escaped:            %[0-9a-fA-F]{2}
            // - fragment delimiter: #
            // - uric_no_slash:      [;?:@&=+$,]
            $matches = [];
            $numberOfMatches = preg_match_all(
                '/(?:(?:http|https|ftp|ftps|mailto):|www\.)(?:[a-zA-Z0-9]|[;\/?:|&=+$,]|[\\-_.!~*\'()]|%[0-9a-fA-F]{2}|#|[;?:@&=+$,])+/',
                $a_str,
                $matches,
                PREG_OFFSET_CAPTURE
            );
            $pos1 = 0;
            $encoded = "";

            foreach ($matches[0] as $match) {
                $matched_text = $match[0];
                $pos2 = $match[1];

                // encode plain text
                $encoded .= nl2br(htmlspecialchars(substr($a_str, $pos1, $pos2 - $pos1)));

                // encode URI
                $encoded .= ilUtil::makeClickable($matched_text, $a_detect_goto_links);


                $pos1 = $pos2 + strlen($matched_text);
            }
            if ($pos1 < strlen($a_str)) {
                $encoded .= nl2br(htmlspecialchars(substr($a_str, $pos1)));
            }
        } else {
            $encoded = nl2br(htmlspecialchars($a_str));
        }
        return $encoded;
    }

    private static function maskAttributeTag(string $a_str, string $tag, string $tag_att): string
    {
        global $DIC;

        $ilLog = $DIC["ilLog"];

        $ws = "[\s]*";
        $att = $ws . "[^>]*" . $ws;

        while (preg_match(
            '/<(' . $tag . $att . '(' . $tag_att . $ws . '="' . $ws . '(([$@!*()~;,_0-9A-z\/:=%.&#?+\-])*)")' . $att . ')>/i',
            $a_str,
            $found
        )) {
            $old_str = $a_str;
            $a_str = preg_replace(
                "/<" . preg_quote($found[1], "/") . ">/i",
                '&lt;' . $tag . ' ' . $tag_att . $tag_att . '="' . $found[3] . '"&gt;',
                $a_str
            );
            if ($old_str == $a_str) {
                $ilLog->write(
                    "ilUtil::maskA-" . htmlentities($old_str) . " == " .
                    htmlentities($a_str)
                );
                return $a_str;
            }
        }
        $a_str = str_ireplace(
            "</$tag>",
            "&lt;/$tag&gt;",
            $a_str
        );
        return $a_str;
    }

    private static function unmaskAttributeTag(string $a_str, string $tag, string $tag_att): string
    {
        global $DIC;

        $ilLog = $DIC["ilLog"];

        while (preg_match(
            '/&lt;(' . $tag . ' ' . $tag_att . $tag_att . '="(([$@!*()~;,_0-9A-z\/:=%.&#?+\-])*)")&gt;/i',
            $a_str,
            $found
        )) {
            $old_str = $a_str;
            $a_str = preg_replace(
                "/&lt;" . preg_quote($found[1], "/") . "&gt;/i",
                '<' . $tag . ' ' . $tag_att . '="' . ilUtil::secureLink($found[2]) . '">',
                $a_str
            );
            if ($old_str == $a_str) {
                $ilLog->write(
                    "ilUtil::unmaskA-" . htmlentities($old_str) . " == " .
                    htmlentities($a_str)
                );
                return $a_str;
            }
        }
        $a_str = str_replace('&lt;/' . $tag . '&gt;', '</' . $tag . '>', $a_str);
        return $a_str;
    }

    public static function maskTag(string $a_str, string $tag, array $fix_param = []): string
    {
        $a_str = str_replace(
            ["<$tag>", "<" . strtoupper($tag) . ">"],
            "&lt;" . $tag . "&gt;",
            $a_str
        );
        $a_str = str_replace(
            ["</$tag>", "</" . strtoupper($tag) . ">"],
            "&lt;/" . $tag . "&gt;",
            $a_str
        );

        foreach ($fix_param as $p) {
            $k = $p["param"];
            $v = $p["value"];
            $a_str = str_replace(
                "<$tag $k=\"$v\">",
                "&lt;" . "$tag $k=\"$v\"" . "&gt;",
                $a_str
            );
        }

        return $a_str;
    }

    private static function unmaskTag(string $a_str, string $tag, array $fix_param = []): string
    {
        $a_str = str_replace("&lt;" . $tag . "&gt;", "<" . $tag . ">", $a_str);
        $a_str = str_replace("&lt;/" . $tag . "&gt;", "</" . $tag . ">", $a_str);

        foreach ($fix_param as $p) {
            $k = $p["param"];
            $v = $p["value"];
            $a_str = str_replace(
                "&lt;$tag $k=\"$v\"&gt;",
                "<" . "$tag $k=\"$v\"" . ">",
                $a_str
            );
        }
        return $a_str;
    }

    /**
     * @deprecated
     */
    public static function secureLink(string $a_str): string
    {
        $a_str = str_ireplace("javascript", "jvscrpt", $a_str);
        $a_str = str_ireplace(["%00",
                               "%0a",
                               "%0d",
                               "%1a",
                               "&#00;",
                               "&#x00;",
                               "&#0;",
                               "&#x0;",
                               "&#x0a;",
                               "&#x0d;",
                               "&#10;",
                               "&#13;"
        ], "-", $a_str);
        return $a_str;
    }

    /**
     * @deprecated
     */
    public static function stripScriptHTML(string $a_str, string $a_allow = "", bool $a_rm_js = true): string
    {
        $negativestr = "a,abbr,acronym,address,applet,area,base,basefont," .
            "big,blockquote,body,br,button,caption,center,cite,code,col," .
            "colgroup,dd,del,dfn,dir,div,dl,dt,em,fieldset,font,form,frame," .
            "frameset,h1,h2,h3,h4,h5,h6,head,hr,html,i,iframe,img,input,ins,isindex,kbd," .
            "label,legend,li,link,map,menu,meta,noframes,noscript,object,ol," .
            "optgroup,option,p,param,q,s,samp,script,select,small,span," .
            "strike,strong,style,sub,sup,table,tbody,td,textarea,tfoot,th,thead," .
            "title,tr,tt,u,ul,var";
        $a_allow = strtolower($a_allow);
        $negatives = explode(",", $negativestr);
        $outer_old_str = "";
        while ($outer_old_str != $a_str) {
            $outer_old_str = $a_str;
            foreach ($negatives as $item) {
                $pos = strpos($a_allow, "<$item>");

                // remove complete tag, if not allowed
                if ($pos === false) {
                    $old_str = "";
                    while ($old_str != $a_str) {
                        $old_str = $a_str;
                        $a_str = preg_replace("/<\/?\s*$item(\/?)\s*>/i", "", $a_str);
                        $a_str = preg_replace("/<\/?\s*$item(\/?)\s+([^>]*)>/i", "", $a_str);
                    }
                }
            }
        }

        if ($a_rm_js) {
            // remove all attributes if an "on..." attribute is given
            $a_str = preg_replace("/<\s*\w*(\/?)(\s+[^>]*)?(\s+on[^>]*)>/i", "", $a_str);

            // remove all attributes if a "javascript" is within tag
            $a_str = preg_replace("/<\s*\w*(\/?)\s+[^>]*javascript[^>]*>/i", "", $a_str);

            // remove all attributes if an "expression" is within tag
            // (IE allows something like <b style='width:expression(alert(1))'>test</b>)
            $a_str = preg_replace("/<\s*\w*(\/?)\s+[^>]*expression[^>]*>/i", "", $a_str);
        }

        return $a_str;
    }

    /**
     * @deprecated
     */
    public static function secureUrl(string $url): string
    {
        // check if url is valid (absolute or relative)
        if (filter_var($url, FILTER_VALIDATE_URL) === false &&
            filter_var("http://" . $url, FILTER_VALIDATE_URL) === false &&
            filter_var("http:" . $url, FILTER_VALIDATE_URL) === false &&
            filter_var("http://de.de" . $url, FILTER_VALIDATE_URL) === false &&
            filter_var("http://de.de/" . $url, FILTER_VALIDATE_URL) === false) {
            return "";
        }
        if (trim(strtolower(parse_url($url, PHP_URL_SCHEME))) == "javascript") {
            return "";
        }
        $url = htmlspecialchars($url, ENT_QUOTES);
        return $url;
    }

    /**
     * @deprecated
     */
    public static function extractParameterString(string $a_parstr): array
    {
        // parse parameters in array
        $par = [];
        $ok = true;
        while (($spos = strpos($a_parstr, "=")) && $ok) {
            // extract parameter
            $cpar = substr($a_parstr, 0, $spos);
            $a_parstr = substr($a_parstr, $spos, strlen($a_parstr) - $spos);
            while (substr($cpar, 0, 1) == "," || substr($cpar, 0, 1) == " " || substr($cpar, 0, 1) == chr(13) || substr(
                $cpar,
                0,
                1
            ) == chr(10)) {
                $cpar = substr($cpar, 1, strlen($cpar) - 1);
            }
            while (substr($cpar, strlen($cpar) - 1, 1) == " " || substr($cpar, strlen($cpar) - 1, 1) == chr(
                13
            ) || substr($cpar, strlen($cpar) - 1, 1) == chr(10)) {
                $cpar = substr($cpar, 0, strlen($cpar) - 1);
            }

            // parameter name should only
            $cpar_old = "";
            while ($cpar != $cpar_old) {
                $cpar_old = $cpar;
                $cpar = preg_replace("/[^a-zA-Z0-9_]/i", "", $cpar);
            }

            // extract value
            if ($cpar != "") {
                if ($spos = strpos($a_parstr, "\"")) {
                    $a_parstr = substr($a_parstr, $spos + 1, strlen($a_parstr) - $spos);
                    $spos = strpos($a_parstr, "\"");
                    if (is_int($spos)) {
                        $cval = substr($a_parstr, 0, $spos);
                        $par[$cpar] = $cval;
                        $a_parstr = substr($a_parstr, $spos + 1, strlen($a_parstr) - $spos - 1);
                    } else {
                        $ok = false;
                    }
                } else {
                    $ok = false;
                }
            }
        }

        return $ok ? $par : [];
    }

    /**
     * @deprecated use Refinery instead
     */
    public static function yn2tf(string $a_yn): bool
    {
        if (strtolower($a_yn) === "y") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @deprecated
     */
    public static function tf2yn(bool $a_tf): string
    {
        if ($a_tf) {
            return "y";
        } else {
            return "n";
        }
    }

    /**
     * checks if mime type is provided by getimagesize()
     *
     * @deprecated
     */
    public static function deducibleSize(string $a_mime): bool
    {
        if (($a_mime == "image/gif") || ($a_mime == "image/jpeg") ||
            ($a_mime == "image/png") || ($a_mime == "application/x-shockwave-flash") ||
            ($a_mime == "image/tiff") || ($a_mime == "image/x-ms-bmp") ||
            ($a_mime == "image/psd") || ($a_mime == "image/iff")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @deprecated Use $DIC->ctrl()->redirectToURL() instead
     */
    public static function redirect(string $a_script): void
    {
        global $DIC;

        if (!isset($DIC['ilCtrl']) || !$DIC['ilCtrl'] instanceof ilCtrl) {
            (new InitCtrlService())->init($DIC);
        }
        $DIC->ctrl()->redirectToURL($a_script);
    }

    /**
     * inserts installation id into ILIAS id
     *
     * e.g. "il__pg_3" -> "il_43_pg_3"
     *
     * @deprecated
     */
    public static function insertInstIntoID(string $a_value): string
    {
        if (substr($a_value, 0, 4) == "il__") {
            $a_value = "il_" . IL_INST_ID . "_" . substr($a_value, 4, strlen($a_value) - 4);
        }

        return $a_value;
    }

    /**
     * checks if group name already exists. Groupnames must be unique for mailing purposes
     * static function
     *
     * @access    public
     * @param string    groupname
     * @param integer    obj_id of group to exclude from the check.
     * @return    boolean    true if exists
     * @static
     *
     */
    public static function groupNameExists(string $a_group_name, ?int $a_id = null): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilErr = null;
        if (isset($DIC["ilErr"])) {
            $ilErr = $DIC["ilErr"];
        }

        if (empty($a_group_name)) {
            $message = __METHOD__ . ": No groupname given!";
            $ilErr->raiseError($message, $ilErr->WARNING);
        }

        $clause = ($a_id !== null) ? " AND obj_id != " . $ilDB->quote($a_id) . " " : "";

        $q = "SELECT obj_id FROM object_data " .
            "WHERE title = " . $ilDB->quote($a_group_name, "text") . " " .
            "AND type = " . $ilDB->quote("grp", "text") .
            $clause;

        $r = $ilDB->query($q);

        return $r->numRows() > 0;
    }

    /**
     * @deprecated
     */
    public static function isWindows(): bool
    {
        return (strtolower(substr(php_uname(), 0, 3)) === "win");
    }

    /**
     * Return current timestamp in Y-m-d H:i:s format
     *
     * @deprecated
     */
    public static function now(): string
    {
        return date("Y-m-d H:i:s");
    }

    /**
     * Get all objects of a specific type and check access
     * This function is not recursive, instead it parses the serialized rbac_pa entries
     *
     * Get all objects of a specific type where access is granted for the given
     * operation. This function does a checkAccess call for all objects
     * in the object hierarchy and return only the objects of the given type.
     * Please note if access is not granted to any object in the hierarchy
     * the function skips all objects under it.
     * Example:
     * You want a list of all Courses that are visible and readable for the user.
     * The function call would be:
     * $your_list = IlUtil::getObjectsByOperation ("crs", "visible");
     * Lets say there is a course A where the user would have access to according to
     * his role assignments. Course A lies within a group object which is not readable
     * for the user. Therefore course A won't appear in the result list although
     * the queried operations 'read' would actually permit the user
     * to access course A.
     *
     * @access    public
     * @param string/array    object type 'lm' or array('lm','sahs')
     * @param string    permission to check e.g. 'visible' or 'read'
     * @param int id of user in question
     * @param int limit of results. if not given it defaults to search max hits.If limit is -1 limit is unlimited
     * @return    array of ref_ids
     * @static
     *
     */
    public static function _getObjectsByOperations(
        $a_obj_type,
        string $a_operation,
        int $a_usr_id = 0,
        int $limit = 0
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $rbacreview = $DIC->rbac()->review();
        $ilAccess = $DIC->access();
        $ilUser = $DIC->user();
        $ilSetting = $DIC->settings();
        $tree = $DIC->repositoryTree();

        if (!is_array($a_obj_type)) {
            $where = "WHERE type = " . $ilDB->quote($a_obj_type, "text") . " ";
        } else {
            $where = "WHERE " . $ilDB->in("type", $a_obj_type, false, "text") . " ";
        }

        // limit number of results default is search result limit
        if (!$limit) {
            $limit = (int) $ilSetting->get('search_max_hits', "100");
        }
        if ($limit == -1) {
            $limit = 10000;
        }

        // default to logged in usr
        $a_usr_id = $a_usr_id ?: $ilUser->getId();
        $a_roles = $rbacreview->assignedRoles($a_usr_id);

        // Since no rbac_pa entries are available for the system role. This function returns !all! ref_ids in the case the user
        // is assigned to the system role
        if ($rbacreview->isAssigned($a_usr_id, SYSTEM_ROLE_ID)) {
            $query = "SELECT ref_id FROM object_reference obr LEFT JOIN object_data obd ON obr.obj_id = obd.obj_id " .
                "LEFT JOIN tree ON obr.ref_id = tree.child " .
                $where .
                "AND tree = 1";

            $res = $ilDB->query($query);
            $counter = 0;
            $ref_ids = [];
            while ($row = $ilDB->fetchObject($res)) {
                // Filter recovery folder
                if ($tree->isGrandChild(RECOVERY_FOLDER_ID, $row->ref_id)) {
                    continue;
                }

                if ($counter++ >= $limit) {
                    break;
                }

                $ref_ids[] = $row->ref_id;
            }
            return $ref_ids;
        } // End Administrators

        // Check ownership if it is not asked for edit_permission or a create permission
        if ($a_operation == 'edit_permissions' or strpos($a_operation, 'create') !== false) {
            $check_owner = ") ";
        } else {
            $check_owner = "OR owner = " . $ilDB->quote($a_usr_id, "integer") . ") ";
        }

        $ops_ids = ilRbacReview::_getOperationIdsByName([$a_operation]);
        $ops_id = $ops_ids[0];

        $and = "AND ((" . $ilDB->in("rol_id", $a_roles, false, "integer") . " ";

        $query = "SELECT DISTINCT(obr.ref_id),obr.obj_id,type FROM object_reference obr " .
            "JOIN object_data obd ON obd.obj_id = obr.obj_id " .
            "LEFT JOIN rbac_pa  ON obr.ref_id = rbac_pa.ref_id " .
            $where .
            $and .
            "AND (" . $ilDB->like("ops_id", "text", "%i:" . $ops_id . "%") . " " .
            "OR " . $ilDB->like("ops_id", "text", "%:\"" . $ops_id . "\";%") . ")) " .
            $check_owner;

        $res = $ilDB->query($query);
        $counter = 0;
        $ref_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($counter >= $limit) {
                break;
            }

            // Filter objects in recovery folder
            if ($tree->isGrandChild(RECOVERY_FOLDER_ID, $row->ref_id)) {
                continue;
            }

            // Check deleted, hierarchical access ...
            if ($ilAccess->checkAccessOfUser($a_usr_id, $a_operation, '', $row->ref_id, $row->type, $row->obj_id)) {
                $counter++;
                $ref_ids[] = $row->ref_id;
            }
        }
        return $ref_ids ?: [];
    }

    /**
     * Checks if a given string contains HTML or not
     *
     * @param string $a_text Text which should be checked
     * @return boolean
     * @access public
     * @static
     */
    public static function isHTML(string $a_text): bool
    {
        if (strlen(strip_tags($a_text)) < strlen($a_text)) {
            return true;
        }

        return false;
    }

    /**
     *  extract ref id from role title, e.g. 893 from 'il_crs_member_893'
     *
     * @param string $role_title with format like il_crs_member_893
     * @deprecated
     */
    public static function __extractRefId(string $role_title): ?int
    {
        $test_str = explode('_', $role_title);
        $prefix = $test_str[0] ?? '';

        if ($prefix === 'il') {
            $ref_id = $test_str[3] ?? null;
            return is_numeric($ref_id) ? (int) $ref_id : null;
        }
        return null;
    }

    /**
     * extract ref id from role title, e.g. 893 from 'il_122_role_893'
     *
     * @param string $ilias_id with format like il_<instid>_<objTyp>_ID
     * @param int    $inst_id  Installation ID must match inst id in param ilias_id
     * @deprecated
     */

    public static function __extractId(string $ilias_id, int $inst_id): ?int
    {
        $test_str = explode('_', $ilias_id);

        $parsed_inst_id = (int) $test_str[1] ?? 0;
        $prefix = $test_str[0] ?? '';

        if ($prefix === 'il' && $parsed_inst_id === $inst_id && count($test_str) === 4) {
            return is_numeric($test_str[3]) ? (int) $test_str[3] : null;
        }
        return null;
    }

    /**
     * Function that sorts ids by a given table field using WHERE IN
     * E.g: __sort(array(6,7),'usr_data','lastname','usr_id') => sorts by lastname
     *
     * @deprecated
     */
    public static function _sortIds(array $a_ids, string $a_table, string $a_field, string $a_id_name): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$a_ids) {
            return [];
        }

        // use database to sort user array
        $where = "WHERE " . $a_id_name . " IN (";
        $where .= implode(",", ilArrayUtil::quoteArray($a_ids));
        $where .= ") ";

        $query = "SELECT " . $a_id_name . " FROM " . $a_table . " " .
            $where .
            "ORDER BY " . $a_field;

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->$a_id_name;
        }
        return $ids;
    }

    /**
     * Get HTML for a system message
     *
     * @deprecated replace with UI Compoenten in ilGlobalPageTemplate
     * ATTENTION: This method is deprecated. Use MessageBox from the
     * UI-framework instead.
     */
    public static function getSystemMessageHTML(string $a_txt, string $a_type = "info")
    {
        global $DIC;

        $box_factory = $DIC->ui()->factory()->messageBox();
        switch ($a_type) {
            case 'info':
                $box = $box_factory->info($a_txt);
                break;
            case 'success':
                $box = $box_factory->success($a_txt);
                break;
            case 'question':
                $box = $box_factory->confirmation($a_txt);
                break;
            case 'failure':
                $box = $box_factory->failure($a_txt);
                break;
            default:
                throw new InvalidArgumentException();
        }

        return $DIC->ui()->renderer()->render($box);
    }

    /**
     * @deprecated use HTTP-service instead
     */
    public static function setCookie(
        string $a_cookie_name,
        string $a_cookie_value = '',
        bool $a_also_set_super_global = true,
        bool $a_set_cookie_invalid = false
    ): void {
        global $DIC;

        $cookie_factory = new CookieFactoryImpl();
        $defalut_cookie_time = time() - (365 * 24 * 60 * 60);

        $cookie = $cookie_factory->create($a_cookie_name, $a_cookie_value)
                                 ->withExpires($a_set_cookie_invalid ? 0 : $defalut_cookie_time)
                                 ->withSecure(defined('IL_COOKIE_SECURE') ? IL_COOKIE_SECURE : false)
                                 ->withPath(defined('IL_COOKIE_PATH') ? IL_COOKIE_PATH : '')
                                 ->withDomain(defined('IL_COOKIE_DOMAIN') ? IL_COOKIE_DOMAIN : '')
                                 ->withHttpOnly(defined('IL_COOKIE_HTTPONLY') ? IL_COOKIE_HTTPONLY : false);
        $DIC->http()->cookieJar()->with($cookie);
    }

    /**
     * @deprecated
     */
    public static function _getHttpPath(): string
    {
        global $DIC;

        $ilIliasIniFile = $DIC["ilIliasIniFile"];

        if ((isset($_SERVER['SHELL']) && $_SERVER['SHELL']) || PHP_SAPI === 'cli' ||
            // fallback for windows systems, useful in crons
            (class_exists("ilContext") && !ilContext::usesHTTP())) {
            return $ilIliasIniFile->readVariable('server', 'http_path');
        } else {
            return ILIAS_HTTP_PATH;
        }
    }

    /**
     * Parse an ilias import id
     * Typically of type il_[IL_INST_ID]_[OBJ_TYPE]_[OBJ_ID]
     * returns array(
     * 'orig' => 'il_4800_rolt_123'
     * 'prefix' => 'il'
     * 'inst_id => '4800'
     * 'type' => 'rolt'
     * 'id' => '123'
     *
     *
     * @deprecated
     *
     */
    public static function parseImportId(string $a_import_id): array
    {
        $exploded = explode('_', $a_import_id);

        $parsed['orig'] = $a_import_id;
        if ($exploded[0] == 'il') {
            $parsed['prefix'] = $exploded[0];
        }
        if (is_numeric($exploded[1])) {
            $parsed['inst_id'] = (int) $exploded[1];
        }
        $parsed['type'] = $exploded[2];

        if (is_numeric($exploded[3])) {
            $parsed['id'] = (int) $exploded[3];
        }
        return $parsed;
    }

    /**
     * format a float
     *
     * this functions takes php's number_format function and
     * formats the given value with appropriate thousand and decimal
     * separator.
     *
     * @deprecated
     */
    protected static function fmtFloat(
        float $a_float,
        int $a_decimals = 0,
        string $a_dec_point = null,
        string $a_thousands_sep = null,
        bool $a_suppress_dot_zero = false
    ): string {
        global $DIC;

        $lng = $DIC->language();

        if ($a_dec_point === null) {
            $a_dec_point = ".";
        }
        if ($a_dec_point === '-lang_sep_decimal-') {
            $a_dec_point = ".";
        }

        if ($a_thousands_sep === null) {
            $a_thousands_sep = $lng->txt('lang_sep_thousand');
        }
        if ($a_thousands_sep === '-lang_sep_thousand-') {
            $a_thousands_sep = ",";
        }

        $txt = number_format($a_float, $a_decimals, $a_dec_point, $a_thousands_sep);

        // remove trailing ".0"
        if (($a_suppress_dot_zero == 0 || $a_decimals == 0)
            && substr($txt, -2) == $a_dec_point . '0'
        ) {
            $txt = substr($txt, 0, strlen($txt) - 2);
        }
        if ($a_float == 0 and $txt == "") {
            $txt = "0";
        }

        return $txt;
    }

    /**
     * Returns the specified file size value in a human friendly form.
     * <p>
     * By default, the oder of magnitude 1024 is used. Thus the value returned
     * by this function is the same value that Windows and Mac OS X return for a
     * file. The value is a GibiBig, MebiBit, KibiBit or byte unit.
     * <p>
     * For more information about these units see:
     * http://en.wikipedia.org/wiki/Megabyte
     *
     * @param integer    size in bytes
     * @param string    mode:
     *                  "short" is useful for display in the repository
     *                  "long" is useful for display on the info page of an object
     * @param ilLanguage  The language object, or null if you want to use the system language.
     */
    public static function formatSize(int $size, string $a_mode = 'short', ?ilLanguage $a_lng = null): string
    {
        global $DIC;

        $lng = $DIC->language();
        if ($a_lng == null) {
            $a_lng = $lng;
        }

        $mag = 1024;

        if ($size >= $mag * $mag * $mag) {
            $scaled_size = $size / $mag / $mag / $mag;
            $scaled_unit = 'lang_size_gb';
        } else {
            if ($size >= $mag * $mag) {
                $scaled_size = $size / $mag / $mag;
                $scaled_unit = 'lang_size_mb';
            } else {
                if ($size >= $mag) {
                    $scaled_size = $size / $mag;
                    $scaled_unit = 'lang_size_kb';
                } else {
                    $scaled_size = $size;
                    $scaled_unit = 'lang_size_bytes';
                }
            }
        }

        $result = self::fmtFloat(
            $scaled_size,
            ($scaled_unit
                    == 'lang_size_bytes') ? 0 : 1,
            $a_lng->txt('lang_sep_decimal'),
            $a_lng->txt('lang_sep_thousand'),
            true
        )
            . ' ' . $a_lng->txt($scaled_unit);
        if ($a_mode == 'long' && $size > $mag) {
            $result .= ' (' . self::fmtFloat(
                $size,
                0,
                $a_lng->txt('lang_sep_decimal'),
                $a_lng->txt('lang_sep_thousand')
            ) . ' '
                . $a_lng->txt('lang_size_bytes') . ')';
        }

        return $result;
    }
}
