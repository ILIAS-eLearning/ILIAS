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

include_once($BEAUT_PATH."/Beautifier/HFile.php");
include_once($BEAUT_PATH."/Beautifier/Context.php");

class Core
{

function Core($file=undef, $outputmodule)
{
	if (!isset($file)) $file = new HFile();
	$this->zbuffer = false;
	$this->buffer = false;
	$this->highlightfile = $file;
	$this->output_module = $outputmodule;
}

//******** Highlighting functions

// Load a file and return it as a (big :-) string.
function load_file($filename)
{
	$filehandle = fopen ($filename, "r") or die("Could not open $filename for reading.");
	$text = fread($filehandle, filesize($filename));
	fclose($filehandle);
	return $text;
}   

function set_stats($statobj)
{
	$this->statobj = $statobj;
}
 
// Do the brunt work of highlighting the text.
function highlight_text($text, $contextstack=undef)
{
	global $BEAUT_PATH;
	if (isset($contextstack) && is_array($contextstack))
	{
		$this->contextstack = $contextstack;
		$this->context = array_pop($contextstack);
		$this->context->inwhitespace = 0;
	}
	else
	{
		$this->context = new Context();
		$this->context->from_language($this->highlightfile, $this->output_module);
		$this->contextstack = array();
	}
	array_push($this->contextstack, $this->context);
	$this->langstack = array();

	if (isset($this->highlightfile->zones))
	{
		// Create the hash mapping from start tag to language, and put together a hash to give
		// the possible endings for a start tag.
		$this->starttags = array();
		$this->endtags = array();
		$this->starttaglengths = array();
		foreach($this->highlightfile->zones as $zone)
		{
			$this->startmap[$zone[0]] = $zone[2];
			array_push($this->starttags, $zone[0]);
			if (!isset($this->endtags[$zone[0]])) $this->endtags[$zone[0]] = array();
			array_push($this->endtags[$zone[0]], $zone[1]);
			$this->starttaglengths[$zone[0]] = strlen($zone[0]);
		}
		$this->endtaglist = array();
		$this->langcache = array();
	}

	
	// Get the lines.
	$arr = preg_split("/\n/", $text);
	$aln = sizeof($arr);
	
	if (isset($this->context->code_parts[0])) $out.= $this->context->code_parts[0];
	for ($i=0; $i<$aln; $i++)
	{

		$this->context->prepro = 0;
		$line = $arr[$i];
		if ($this->context->preprolength>0 && substr($line, 0, $this->context->preprolength)==$this->highlightfile->prepro)
		{
			$out.= $this->context->prepro_parts[0];
			$this->context->prepro = 1;
		}
		
		
		$this->context->inwhitespace = 1;
		
		
		$this->context->incomment = 0;
		// Handle selected lines.
		if (isset($this->highlightfile->lineselect) && !$this->context->inselection && substr($line, 0, strlen($this->highlightfile->lineselect))==$this->highlightfile->lineselect)
		{
			$out.= $this->context->select_parts[0];
			$line = substr($line, strlen($this->highlightfile->lineselect));
			$this->context->lineselect = 1;
		}
		
		// Strip leading and trailing spaces
		if ($this->highlightfile->notrim==0) $line = trim($line);
		
		$lineout = "";
		$lineorig = $line;
		// Print out the current indent.
		$sw = $this->_starts_with($lineorig, $this->highlightfile->unindent);
		if ($lineorig != "")
		{
			if ($this->context->ind>0 && $sw!="")
			{
				$lineout = str_repeat("        ", ($this->context->ind-1));
			}
			else
			{
				$lineout = str_repeat("        ", $this->context->ind);
			}
		}
		$ln = strlen($lineorig);
		for ($j=0; $j<$ln; $j++)
		{
			$currchar = $lineorig[$j];
//print $currchar;
			// Handle opening selection blocks.
			if (isset($this->highlightfile->selecton) && !$this->context->inselection && 
				!$this->context->inquote && !$this->context->inbcomment && 
				substr($line, $j, strlen($this->highlightfile->selecton))==$this->highlightfile->selecton)
			{
//print "01";
				$lineout = $this->_munge($lineout).$this->context->select_parts[0];
				$out.= $lineout;
				$lineout = "";
				$this->context->inselection = 1;
				$j+= strlen($this->highlightfile->selecton)-1;
				continue;
			}
			// Handle closing selection blocks.
			if (isset($this->highlightfile->selectoff) && $this->context->inselection && 
				substr($line, $j, strlen($this->highlightfile->selectoff))==$this->highlightfile->selectoff)
			{
//print "02";
				$lineout.=$this->context->select_parts[1];
				$out.= $lineout;
				$lineout = "";
				$this->context->inselection = 0;
				$j+= strlen($this->highlightfile->selectoff);
				continue;
			}
			// Handle line comments. This is made slightly faster by going straight to
			// the next line - as nothing else can be done.
			if (!$this->context->lineselect && !$this->context->inselection && !$this->context->inquote && !$this->context->incomment && !($this->highlightfile->perl && $j>0 && $line[$j-1]=="$"))
			{
//print "03";				
				$currmax = 0;
				foreach($this->highlightfile->linecommenton as $l)
				{
					if ($l[0] != $currchar) continue;
					$lln = $this->context->lcolengths[$l];
					if (substr($line, $j, $lln)==$l)
					{
						if ($lln > $currmax)
						{
							$lnc = $l;
							$currmax = $lln;
						}
					}
				}

				if ($currmax != 0)
				{
//print "04";
					
					$line = substr($line, $j);
					$lineout = $this->_munge($lineout);
					$line = htmlentities($line);
					$out.= $lineout;
					if ($this->context->prepro) 
					{
						$out.= $this->context->prepro_parts[1];
						$this->context->prepro = 0;
					}
					$out.= $this->context->linecomment_parts[0].$line;
					if (isset($this->statobj) && $this->statobj->harvest_comments) $this->statobj->comment_cache .= " ".substr($line, $lncl);
					$lineout = "";
					$this->context->incomment = 1;
					$j = $ln + 1;
					continue;
				}
			}
			
			// Handle opening block comments. Sadly this can't be done quickly (like with
			// line comments) as we may have 'foo /* bar */ foo'.
			if (!$this->context->lineselect && !$this->context->inselection && !$this->context->inquote && !$this->context->inbcomment && in_array($currchar, $this->context->startingbkonchars))
			{
//print "05";				
				$currmax = 0;
				foreach($this->highlightfile->blockcommenton as $bo)
				{
					if ($bo[0] != $currchar) continue;
					$boln = $this->context->bcolengths[$bo];
					if (substr($line, $j, $boln)==$bo)
					{
						if ($boln > $currmax)
						{
//print "06";
							$bkc = $bo;
							$bkcl = $boln;
							$currmax = $boln;
						}
					}
				}

				if ($currmax != 0)
				{
//print "07";
					if ($this->prepro) 
					{
						$out.= $this->context->prepro_parts[1];
						$this->prepro = 0;
					}
					$this->context->closingstrings = $this->context->bcomatches[$bkc];
					$lineout = $this->_munge($lineout);
					$bkcout = str_replace(">", "&gt;", $bkc);
					$bkcout = str_replace("<", "&lt;", $bkcout);
					$out.= $lineout;
					$out.= $this->context->blockcomment_parts[0].$bkcout;
					$lineout = "";
					$this->context->inbcomment = 1;
					$j += $bkcl-1;
					continue;
				}
			}
			// Handle closing comments.
			if (!$this->context->lineselect && !$this->context->inselection && !$this->context->inquote && $this->context->inbcomment)
			{
//print "08";			
				$currmax = 0;
				foreach($this->context->closingstrings as $bf)
				{
					if ($bf[0] != $currchar) continue;
					$bfln = $this->context->bcflengths[$bf];
					if (substr($line, $j, $bfln)==$bf)
					{
						if ($bfln > $currmax)
						{
							$bku = $bf;
							$bkul = $bfln;
							$currmax = $bfln;
						}
					}
				}
				
				if ($currmax != 0)
				{
//print "09";
					$bkuout = str_replace(">", "&gt;", $bku);
					$bkuout = str_replace("<", "&lt;", $bkuout);
					$lineout .= $bkuout.$this->context->blockcomment_parts[1];
					
					$out.= $lineout;
					$lineout = "";
					$this->context->inbcomment = 0;
					$j += $bkul-1;
					continue;
				}
			}
			if (isset($this->highlightfile->zones) && !$this->context->inbcomment && !$this->context->incomment && !$this->context->inquote)
			{
//print "10";
				$startcurrmax = 0;
				foreach($this->starttags as $starttag)
				{
					if ($starttag[0] != $currchar) continue;	// Avoid doing substr.
					$starttagln = $this->starttaglengths[$starttag];
					
					if (substr($line, $j, $starttagln)==$starttag)
					{
						if ($starttagln > $startcurrmax)
						{
							$startcurrtag = $starttag;
							$startcurrmax = $starttagln;

						}
					}
				}
				if ($startcurrmax != 0)
				{
//print "11";
					$tagout = str_replace(">", "&gt;", $startcurrtag);
					$tagout = str_replace("<", "&lt;", $tagout);
					$out.= ltrim($lineout);	// Sane? --moj
					array_push($this->langstack, $this->highlightfile);
					array_push($this->contextstack, $this->context);
					$out.= "$tagout";
					require_once $BEAUT_PATH."/HFile/".$this->startmap[$startcurrtag].".php";
					$this->endtaglist = $this->endtags[$startcurrtag];
					if (isset($this->langcache[$startcurrtag])) 
						$this->highlightfile = $this->langcache[$startcurrtag];
					else
					{
						$this->highlightfile = new $this->startmap[$startcurrtag]();
						$this->langcache[$startcurrtag] = $this->highlightfile;
					}
					$this->context = new Context();
					$this->context->from_language($this->highlightfile, $this->output_module);
					$lineout = "";
					$j += $startcurrmax-1;
					continue;
				}

				$endcurrmax = 0;
				foreach($this->endtaglist as $endtag)
				{
					
					if ($endtag[0] != $currchar) continue;	// Avoid doing substr.
					$endtagln = strlen($endtag);
					if (substr($line, $j, $endtagln)==$endtag)
					{
						if ($endtagln > $endcurrmax)
						{
							$endcurrtag = $endtag;
							$endcurrmax = $endtagln;
						}
					}
				}
				if ($endcurrmax!=0)
				{
//print "12";
					$tagout = str_replace(">", "&gt;", $endcurrtag);
					$tagout = str_replace("<", "&lt;", $tagout);
					
					$lineout .= "$tagout";
					$out.= $lineout;
					$lineout = "";
					$this->highlightfile = array_pop($this->langstack);
					$this->context = array_pop($this->contextstack);
					$this->endtaglist = array();
					$j += $endcurrmax;
					continue;
				}
				
			}
			// If we're in a comment, skip keyword checking, cache the comments, and go
			// to the next char.
			if ($this->context->incomment || $this->context->inbcomment) 
			{
				if ($this->context->inbcomment)
				{
					if ($currchar == "<") $currchar = "&lt;";
					else if ($currchar == ">") $currchar = "&gt;";
					else if ($currchar == "&") $currchar = "&amp;";
				}
//print "13";
				$lineout .= $currchar; 
				if ($this->context->newline) 
				{
					if (isset($this->statobj) && $this->statobj->harvest_comments) $this->statobj->comment_cache .= " ";
					$this->context->newline = 0;
				}
				if (isset($this->statobj) && $this->statobj->harvest_comments) $this->statobj->comment_cache .= $currchar;
				continue; 
			}
			
			// Indent has to be either preceded by, or be, a delimiter.
			$delim = ($j==0 || in_array($currchar, $this->context->alldelims) || ($j>0 && in_array($lineorig[$j-1], $this->context->alldelims)));
			
			// Handle quotes.	
			if (!$this->context->lineselect && !$this->context->inselection && !$this->context->escaping && 
			((in_array($currchar, (array)$this->highlightfile->stringchars) && $this->context->inquote && $currchar==$this->context->currquotechar) || (in_array($currchar, (array)$this->highlightfile->stringchars) && !$this->context->inquote))) 
			{
				
//print "14:$currchar";
				// First quote, so go blue.
				if (!$this->context->escaping && isset($this->context->inquote) && !$this->context->inquote)
				{
//print "15";
					$lineout = $this->_munge($lineout);
					$out.= $lineout;
					$this->context->inquote = 1;
					if (isset($this->statobj) && $this->statobj->harvest_strings) $this->string_cache.=" ";
					if ($this->context->prepro)
					{
						$lineout = $this->context->prepro_parts[1].$currchar.$this->context->quote_parts[0];
					}
					else
					{
						$out.= $currchar.$this->context->quote_parts[0];
						$lineout = "";
					}
					$this->context->currquotechar = $currchar;
				}
				// Last quote, so turn off font colour.
				else if ($this->context->inquote && !$this->context->escaping && $currchar == $this->context->currquotechar)
				{
//print "16";
					$this->context->inquote = 0;
					if ($this->context->prepro)
					{
						$lineout .= $this->context->quote_parts[1].$this->context->prepro_parts[0].$lineorig[$j];
					}
					else
					{
						$lineout .= $this->context->quote_parts[1].$lineorig[$j];
					}
					$out.= $lineout;
					$lineout = "";
					$this->context->currquotechar = "";
				}
			}
			// If we've got an indent character, increase the level, and add an indent.
			else if (!$this->context->inselection && $delim && !$this->context->inquote && ($stri=$this->_starts_with(substr($line, $j), $this->highlightfile->indent))!="") 
			{
//print "17";
				if (!$this->context->inwhitespace) 
				{
					$lineout .= str_repeat("        ", $this->context->ind);
				}
				$lineout .= $stri;
				$this->context->ind++;
				$j += strlen($stri)-1;
				
			}
			// If we've got an unindent (and we are indented), go back a level.
			else if (!$this->context->inselection && $delim && $this->context->ind>0 && !$this->context->inquote && ($stru=$this->_starts_with(substr($line, $j), $this->highlightfile->unindent))!="") 
			{
//print "18";
				$this->context->ind--;
				
				if (!$this->context->inwhitespace) 
				{
					$lineout .= str_repeat("        ", $this->context->ind);
				}
				$lineout .= $stru;
				
				$j += strlen($stru)-1;
			}
			// Add the characters to the output, and cache strings.
			else if (!$this->context->inwhitespace || $currchar != " " || $currchar != "\t") 
			{
//print "19";
				if ($this->context->inquote && isset($this->statobj) && $this->statobj->harvest_strings)
					$this->statobj->string_cache .=$currchar;
				$lineout .= htmlentities($currchar);
			}
			if ($this->context->inquote && $this->context->escaping) 
			{
//print "20";
				$this->context->escaping = 0; 
			}
			else if ($this->context->inquote && $currchar == $this->highlightfile->escchar && !$this->context->escaping) 
			{
//print "21";
				$this->context->escaping = 1;
			}	
		}	
		if ($currchar != " " && $currchar != "\t") 
		{
			$this->context->inwhitespace = 0;
		}
		if (!$this->context->incomment && !$this->context->inbcomment && !$this->context->inquote) 
		{
			$lineout = $this->_munge($lineout);
		}
		if ($i<($aln-1)) 
		{
			if ($this->context->prepro)
			{
				$lineout .= $this->context->prepro_parts[1];
			}
		}
		// Close any hanging font tags.
		if ($this->context->incomment) 
		{
			$out.= $this->context->linecomment_parts[1];
		}
		if ($i<($aln-1)) $lineout .="\n";
		if ($this->context->lineselect) $lineout.= $this->context->select_parts[1];
		$out.= $lineout;
		$this->context->newline = 1;
		$this->context->lineselect = 0;
		
	}
	// If we've finished, and are still in a comment, close the font tag.
	if ($this->context->incomment)
	{
		$out.= $this->context->linecomment_parts[1];
	} 
	else if ($this->context->inbcomment)
	{
		$out.= $this->context->blockcomment_parts[1];
	}
	else if ($this->context->inselection)
	{
		$out.= $this->context->select_parts[1];
	}
	if (isset($this->context->code_parts[1])) $out.= $this->context->code_parts[1];
	return $out;

}

function get_stack()
{
	return $this->contextstack;
}

// Go through the string, highlighting keywords.
function _munge($munge)
{
	$munge 	= str_replace("&gt;", ">", $munge);
	$munge 	= str_replace("&lt;","<", $munge);

	$inword 	= 0;
	$currword 	= "";
	$currchar 	= "";
	$strout 	= "";
	$lngth 		= strlen($munge);
	if ($this->context->inselection || $this->context->lineselect) return $munge;
	if (!$this->context->prepro)
	{
		for($i=0; $i<=$lngth; $i++)
		{
			$currchar = $munge[$i];
			$delim = in_array($currchar, $this->highlightfile->delimiters);
			if ($delim || $i==($lngth))
			{
				if ($inword)
				{
					$inword = 0;
					$oldword = $currword;
					
					$checkword = $oldword;
					if ($this->highlightfile->nocase) $checkword = strtolower($checkword);
					$currword = str_replace("<", "&lt;", $currword);
					$currword = str_replace(">", "&gt;", $currword);
					if (isset($this->context->validkeys[$checkword])) 
					{
						
						if ($this->highlightfile->nocase) $checkword = $this->context->validkeys[$checkword];
						$category = $this->highlightfile->keywords[$checkword];
						$fontchunk = $this->context->category_parts[$category][0].$currword.$this->context->category_parts[$category][1];
				
						if (
						  isset($this->highlightfile->linkscripts) && 
							(
							  $code = call_user_method($this->highlightfile->linkscripts{$category}, $this->highlightfile, $oldword, $this->output_module)
							) != $oldword
							)
						{
							$fontchunk = $code;
						}
						$strout .= $fontchunk;
					}
					else
						$strout .= $currword;
				}
				$currchar = str_replace("<", "&lt;", $currchar);
				$currchar = str_replace(">", "&gt;", $currchar);
				$strout .= $currchar;
			}
			else
			{
				if ($inword)
				{
					$currword .= $currchar;
				}
				else
				{
					$inword = 1;
					$currword = $currchar;
				}
			}
		}
	}
	else
	{
		$strout = htmlentities($munge);
	}

	return $strout;
}


//******** Helper Functions

// A handy function to find the longest element of the array that is present at the beginning
// of the provided string. For example, given an array of 'foo' and 'foot' and the string 'football',
// this returns 'foot'.
function _starts_with($text, $array)
{
	
	$ml = 0;
	$curr = "";
	
	foreach($array as $i)
	{
		$l = strlen($i);
		if (((!$this->highlightfile->nocase && substr($text, 0, $l)==$i) || ($this->highlightfile->nocase && strtolower(substr($text, 0, $l))==strtolower($i))) && ($text[$l]==" " || $l==1 || $text[$l]=="\n" || $text[$l]=="\t" || $text[$l]=="." || $text[$l]==";" || $l==strlen($text)))
		{
			if ($l>$ml) 
			{
				$curr = substr($text, 0, $l);
				$ml = $l;
			}
		}
	}
	return $curr;
}


}
?>
