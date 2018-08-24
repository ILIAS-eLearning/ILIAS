//!/usr/local/bin/php -q
<?php

ini_set('max_execution_time', 300000);

$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require "$BEAUT_PATH/Beautifier/HFile.php";

//######## Configuration Conversion functions

// Function to go through an Ultraedit-style configuration file and convert to a PHP include

function HFile_print_php_file($tofile = 1)
{

global $LANGNAME;
        global $indent, $unindent, $stringchars, $config, $keywords, $delimiters, $lang_name;
        global $linecommenton, $blockcommenton, $blockcommentoff;
        global $perl, $nocase, $notrim;
        if ($tofile) print "<?php\n"; else print "&lt;?\n";
        
        print 'require_once(\'HFile.php\');'."\n";
        print '  class HFile_'.$LANGNAME.' extends HFile{'."\n";
        print '   function HFile_'.$LANGNAME.'(){'."\n";
        print '     $this->HFile();	'."\n";

        
        
        print "######################################\n";
        print "# Beautifier Highlighting Configuration File \n";
        print "# $lang_name\n";
        print "######################################\n";
        print "# Flags\n\n";
        dump_var($nocase,			"\$this->nocase            ");
        dump_var($notrim,			"\$this->notrim            ");
        dump_var($perl,				"\$this->perl              ");
        print "\n# Colours\n\n";
        $used_categories = get_categories();
        dump_colours($used_categories);
        dump_var("blue",			"\$this->quotecolour       ");
        dump_var("green",			"\$this->blockcommentcolour");
        dump_var("green",			"\$this->linecommentcolour ");
        print "\n# Indent Strings\n\n";
        dump_array($indent,   			"\$this->indent            ");
        dump_array($unindent, 			"\$this->unindent          ");
        print "\n# String characters and delimiters\n\n";
        dump_array($stringchars,		"\$this->stringchars       ");
        dump_array($delimiters,			"\$this->delimiters        ");
        dump_var  ($escchar,			"\$this->escchar           ");
        print "\n# Comment settings\n\n";
        dump_var  ($linecommenton, 		"\$this->linecommenton     ");
        dump_var  ($blockcommenton,		"\$this->blockcommenton    ");
        dump_var  ($blockcommentoff,		"\$this->blockcommentoff   ");
        print "\n# Keywords (keyword mapping to colour number)\n\n";
        dump_hash ($keywords,			"\$this->keywords          ");
        print "\n# Special extensions\n";
        dump_linkscripts($used_categories);
        if ($tofile) print "\n}?>"; else print "}\n?&gt;";
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
        
        dump_hash($linkhash, "\$this->linkscripts    ");
        print "}\n# DoNothing link function\n\n";
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
        dump_array($usedcols, "\$this->colours        ");
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

function convert($file){
  $file = str_replace("./", "", $file);
  global $LANGNAME;
  $LANGNAME = $file;
  $LANGNAME = str_replace(".txt", "", $LANGNAME);
  
  $fileout = '../object/'.$LANGNAME . '.php';

  print "Writing $file to $fileout\n";
  
        HFile_parse_file($file);

        ob_start();
        ob_implicit_flush(0);

        HFile_print_php_file(isset($fileout));
        $out = ob_get_contents();               
        ob_end_clean(); 
  
        if (isset($fileout))
        {
                $fd = fopen($fileout, "w");
                fputs($fd, $out);
                fclose($fd);
        }
  



  


}


convert($argv[1]);




?>
