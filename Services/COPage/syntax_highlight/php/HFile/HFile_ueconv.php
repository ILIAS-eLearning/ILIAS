<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require "$BEAUT_PATH/Beautifier/HFile.php";

//######## Configuration Conversion functions

// Function to go through an Ultraedit-style configuration file and convert to a PHP include

function HFile_print_php_file($tofile = 1)
{
        global $indent, $unindent, $stringchars, $config, $keywords, $delimiters, $lang_name;
        global $linecommenton, $blockcommenton, $blockcommentoff;
        global $perl, $nocase, $notrim;
        if ($tofile) print "<?php\n"; else print "&lt;?\n";
        print "######################################\n";
        print "# Beautifier Highlighting Configuration File \n";
        print "# $lang_name\n";
        print "######################################\n";
        print "# Flags\n\n";
        dump_var($nocase,			"\$nocase            ");
        dump_var($notrim,			"\$notrim            ");
        dump_var($perl,				"\$perl              ");
        print "\n# Colours\n\n";
        $used_categories = get_categories();
        dump_colours($used_categories);
        dump_var("blue",			"\$quotecolour       ");
        dump_var("green",			"\$blockcommentcolour");
        dump_var("green",			"\$linecommentcolour ");
        print "\n# Indent Strings\n\n";
        dump_array($indent,   			"\$indent            ");
        dump_array($unindent, 			"\$unindent          ");
        print "\n# String characters and delimiters\n\n";
        dump_array($stringchars,		"\$stringchars       ");
        dump_array($delimiters,			"\$delimiters        ");
        dump_var  ($escchar,			"\$escchar           ");
        print "\n# Comment settings\n\n";
        dump_var  ($linecommenton, 		"\$linecommenton     ");
        dump_var  ($blockcommenton,		"\$blockcommenton    ");
        dump_var  ($blockcommentoff,		"\$blockcommentoff   ");
        print "\n# Keywords (keyword mapping to colour number)\n\n";
        dump_hash ($keywords,			"\$keywords          ");
        print "\n# Special extensions\n";
        dump_linkscripts($used_categories);
        if ($tofile) print "\n?>"; else print "\n?&gt;";
}

function get_categories()
{
        global $keywords;
        $usedcats = array();
        
        foreach(array_keys($keywords) as $k)
        {
                $cat = $keywords[$k];
                if (!in_array($cat, $usedcats)) array_push($usedcats, $cat);
        }
        return $usedcats;
}

function dump_linkscripts($cats)
{
        print "
// Each category can specify a PHP function that returns an altered
// version of the keyword.
        # This link is then placed in a <a href=\"...\">foo</a>; structure around keyword 'foo' - which is
        \n\n";
        
        $linkhash = array();
        
        foreach($cats as $c)
        {
                $linkhash{$c} = "donothing";
        }
        
        dump_hash($linkhash, "\$linkscripts    ");
        print "\n# DoNothing link function\n\n";
        print "function donothing(\$keywordin)\n{\n	return \$keywordin;\n}\n";
}

function dump_colours($cats)
{
        global $colours;
        
        $usedcols = array();
        foreach($cats as $c)
        {
                array_push($usedcols, $colours[$c-1]);
        }
        dump_array($usedcols, "\$colours        ");
}

function dump_var($variable, $name)
{
        print $name."	= \"".addslashes($variable)."\";\n";
}

function dump_array($array, $name)
{
        $first = 1;
        print $name."	= array(";
        foreach($array as $a)
        {
                if (!$first) print ", "; else $first = 0;
                print "\"".addslashes($a)."\"";
        }
        print ");\n";
}

function dump_hash($hash, $name)
{
        $first = 1;
        print $name."	= array(";
        foreach(array_keys($hash) as $k)
        {
                if (!$first) print ", "; else $first = 0;
                print "\n			\"".addslashes($k)."\"";
                print " => \"".addslashes($hash[$k])."\"";
        }
        print ");\n";
}

?>
<html><title>Configuration Converter</title><body>
<h1>Important Note</h1>
<p>This script should <b>not</b> be run on a public server. It is possible to access files other than syntax highlight files (by entering /etc/passwd, for example). Although this should not display anything useful, it is still possible that important data may be extractable. As such, only run this script <b>locally</b> (it should not be necessary to run it on a public server, anyway).</p>
<form action="HFileconv.php">Please specify a syntax file to convert:<br />
<input type="text" width="64" size="64" name="file" value="<?php print $file; ?>" /><br />
Save to:<br />
<input type="text" width="64" size="64" name="fileout" value="<?php print stripslashes($fileout); ?>" /><br />
<input type="submit" /></form><hr />
<pre>
<?php
if (isset($fileout) && $fileout == "") unset($fileout);
if (isset($file) && file_exists($file))
{
        HFile_parse_file($file);
        ob_start();
        HFile_print_php_file(isset($fileout));
        $out = ob_get_contents();
        if (isset($fileout))
        {
                $fd = fopen($fileout, "w");
                fputs($fd, $out);
                fclose($fd);
        }
        ob_end_flush();
}
?>
</pre>
</body></html>



