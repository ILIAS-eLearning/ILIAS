<?php

/*

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

class HFile
{

	function HFile()
	{
		# Language name.
		$this->lang_name 			= "";
		# Language configuration hashtable. 
		$this->config 				= array();
		$color 					= 0;
		$parsing				= "";
		$this->validkeys			= array();
		
		$this->indent				= array();
		$this->unindent				= array();
		$this->blockcommenton 			= array();
		$this->blockcommentoff			= array();
		$this->linecommenton			= array();
		$this->preprocessors			= array();
		
		# Zoning
		$this->zones				= array();
		
		# Hashtable of keywords - maps from keyword to colour.
		$this->keywords				= array();
		
		# Used for conversion from keyword -> regexp.
		$this->keypats				= array();
		
		# Which colours to use for each 'level'.
		$this->colours				= array("blue", "purple", "gray", "brown", "blue", "purple", "gray", "brown");
		$this->quotecolour			= "blue";
		$this->blockcommentcolour		= "green";
		$this->linecommentcolour		= "green";
		$this->stringchars			= array();
		$this->delimiters			= array();
		$this->escchar				= "\\";
		
		# Caches for the harvesters.
		$this->comcache				= "";
		$this->stringcache			= "";
		
		# Toggles
		$this->perl				= 0;
		$this->notrim				= 0;
		$this->nocase				= 0;
	}
	
	function parse_file($file)
	{
		print "This method should not be called from here, but from an extending configuration class.<br>";
	}

	function to_perl($stub, $tofile = 1)
	{
		$this->used_categories = $this->_get_categories();
		print "
package HFile::$stub;

###############################
#
# Beautifier Perl HFile
# Language: $stub
#
###############################

use Beautifier::HFile;
@ISA = qw(HFile);
sub new
{
	my( \$class ) = @_;
	my \$self = {};
	bless \$self, \$class;

	# Flags:
";
		$this->_dump_var($this->nocase,				"	\$self->{nocase}         ");
		$this->_dump_var($this->notrim,				"	\$self->{notrim}         ");
		$this->_dump_var($this->perl,				"	\$self->{perl}           ");
		$this->_dump_perl_array($this->indent,			"	\$self->{indent}         ");
		$this->_dump_perl_array($this->unindent,		"	\$self->{unindent}       ");
		$this->_dump_perl_array($this->stringchars,		"	\$self->{stringchars}    ");
		$this->_dump_perl_array($this->delimiters,		"	\$self->{delimiters}     ");
		$this->_dump_var($this->escchar,			"	\$self->{escchar}        ");
		$this->_dump_perl_array($this->linecommenton,		"	\$self->{linecommenton}  ");
		$this->_dump_perl_array($this->blockcommenton,		"	\$self->{blockcommenton} ");
		$this->_dump_perl_array($this->blockcommentoff,		"	\$self->{blockcommentoff}");
		$this->_dump_perl_hash($this->keywords,			"	\$self->{keywords}       ");			
		$this->_dump_perl_linkscripts();	
print "
	return \$self;
}

";

		$this->_dump_perl_defaultscripts();
print "1;\n";
	}
	
	function to_php($stub, $tofile = 1)
	{
	
		
		$this->used_categories = $this->_get_categories();
		
		if ($tofile) print "<?php\n"; else print "&lt;?\n";
		print "require_once('psh.php');\n";
	  	print "class psh_$stub extends psh\n{\n";
	  	print "function psh_$stub(){\n";
	     	print "\$this->psh();\n";
		print "######################################\n";
		print "# Beautifier Highlighting Configuration File \n";
		print "# $this->lang_name\n";
		print "######################################\n";
		print "# Flags\n\n";
		$this->_dump_var($this->nocase,				"\$this->nocase            ");
		$this->_dump_var($this->notrim,				"\$this->notrim            ");
		$this->_dump_var($this->perl,				"\$this->perl              ");
		print "\n# Colours\n\n";
		$this->_dump_colours();	
		$this->_dump_var("blue",				"\$this->quotecolour       ");
		$this->_dump_var("green",				"\$this->blockcommentcolour");
		$this->_dump_var("green",				"\$this->linecommentcolour ");
		print "\n# Indent Strings\n\n"; 
		$this->_dump_array($this->indent,   			"\$this->indent            ");
		$this->_dump_array($this->unindent, 			"\$this->unindent          ");
		print "\n# String characters and delimiters\n\n";
		$this->_dump_array($this->stringchars,			"\$this->stringchars       ");
		$this->_dump_array($this->delimiters,			"\$this->delimiters        ");
		$this->_dump_var  ($this->escchar,			"\$this->escchar           ");
		print "\n# Comment settings\n\n";
		$this->_dump_array($this->linecommenton, 		"\$this->linecommenton     ");
		$this->_dump_var  ($this->blockcommenton,		"\$this->blockcommenton    ");
		$this->_dump_var  ($this->blockcommentoff,		"\$this->blockcommentoff   ");
		print "\n# Keywords (keyword mapping to colour number)\n\n";
		$this->_dump_hash ($this->keywords,			"\$this->keywords          ");
		print "\n# Special extensions\n";
		$this->_dump_linkscripts();
		print "}\n";
		if ($tofile) print "\n?>"; else print "\n?&gt;";
	}
	
	function _get_categories()
	{
		$usedcats = array();
		
		foreach(array_keys($this->keywords) as $k)
		{
			$cat = $this->keywords[$k];
			if (!in_array($cat, $usedcats)) array_push($usedcats, $cat);
		}
		return $usedcats;
	}
	
	function _dump_linkscripts()
	{
		print "
# Each category can specify a PHP function that takes in the function name, and returns a string
# to put in its place. This can be used to generate links, images, etc.\n\n";
		
		$linkhash = array();
		
		foreach($this->used_categories as $c)
		{
			$linkhash{$c} = "donothing";
		}
		
		$this->_dump_hash($linkhash, "\$this->linkscripts    ");
		print "}\n";
		print "\n# DoNothing link function\n\n";
		print "function donothing(\$keywordin)\n{\n	return \$keywordin;\n}\n";
	}

	function _dump_perl_linkscripts()
	{
		print "
# Each category can specify a Perl function that takes in the function name, and returns a string
# to put in its place. This can be used to generate links, images, etc.\n\n";

		$linkhash = array();
		foreach($this->used_categories as $c)
		{
			$linkhash{$c} = "donothing";
		}

		$this->_dump_perl_hash($linkhash, "\$self->{linkscripts}	");
	}

	function _dump_perl_defaultscripts()
	{
		print "\n# DoNothing link function\n\n";
		print "sub donothing\n{\nmy ( \$self ) = @_;\nreturn;\n}\n";
	}
	
	function _dump_colours()
	{
		$usedcols = array();
		foreach($this->used_categories as $c)
		{
			array_push($usedcols, $this->colours[$c-1]);
		}
		$this->_dump_array($usedcols, "\$this->colours        ");
	}
	
	function _dump_var($variable, $name)
	{
		print $name."	= \"".addslashes($variable)."\";\n";
	}
	
	function _dump_array($array, $name)
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

	function _dump_perl_array($array, $name)
	{
		$first = 1;
		print $name."	= [";
		foreach($array as $a)
		{
			if (!$first) print ", "; else $first = 0;
			print "\"".addslashes($a)."\"";
		}
		print "];\n";
	}
	
	function _dump_hash($hash, $name)
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

	function _dump_perl_hash($hash, $name)
	{
		$first = 1;
		print $name."	= {";
		foreach(array_keys($hash) as $k)
		{
			if (!$first) print ", "; else $first = 0;
			print "\n			\"".addslashes($k)."\"";
			print "	=> \"".addslashes($hash[$k])."\"";
		}
		print "};\n";
	}
}

?>
