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

class Context
{
	function Context()
	{
	}
	
	function from_language($lang, $output)
	{
		// Current indent level.
		$this->ind 		= 0;
		$this->inquote 		= 0;
		$this->incomment	= 0;
		$this->inbcomment 	= 0;
		$this->inwhitespace 	= 1;
		// Used to ensure quote matching works :-)
		$this->currquotechar	= "";
		$this->begseen		= 0;	
		$this->newline		= 0;
		$this->escaping		= 0;
		// In a line select?
		$this->lineselect	= 0;
		$this->closingstrings	= array();
	
		// Used by $this->munge for keyword checking - we only want to make this once.
		$this->validkeys = array();
	
		foreach(array_keys($lang->keywords) as $key)
		{
			if ($lang->nocase) 
				$this->validkeys[strtolower($key)] = $key;
			else 
				$this->validkeys[$key] = $key;
		}
		$this->alldelims = array_merge($lang->delimiters, $lang->stringchars);
	
		// Additional caching: First we want to store the strlens of the comments.
		$this->lcolengths = array();
		foreach($lang->linecommenton as $lco)
		{
			$this->lcolengths[$lco] = strlen($lco);
		}
		foreach($lang->blockcommenton as $bco)
		{
			$this->bcolengths[$bco] = strlen($bco);
		}
		foreach($lang->blockcommentoff as $bcf)
		{
			$this->bcflengths[$bcf] = strlen($bcf);
		}
	
		// Build up match arrays for bcos.
		$this->bcomatches = array();
		$this->startingbkonchars = array();
		for($i=0; $i<sizeof($lang->blockcommenton); $i++)
		{
			$bco = $lang->blockcommenton[$i];
			if (!isset($this->bcomatches[$bco])) $this->bcomatches[$bco] = array();
			array_push($this->bcomatches[$bco], $lang->blockcommentoff[$i]);
			array_push($this->startingbkonchars, $bco[0]);
		
		}
		
		
		$preprolength = 0;
		$this->prepro = 0;
		if (isset($lang->prepro)) $this->preprolength = strlen($lang->prepro);
		
		// Output module handling
			
		$this->code_parts = explode("_WORD_", $output->code);
		$this->linecomment_parts = explode("_WORD_", $output->linecomment);
		$this->blockcomment_parts = explode("_WORD_", $output->blockcomment);
		$this->prepro_parts = explode("_WORD_", $output->prepro);
		$this->select_parts = explode("_WORD_", $output->select);
		$this->quote_parts = explode("_WORD_", $output->quote);
		$currcat = 1;
		do
		{
			$varname = "category_".$currcat;
			if (isset($output->{$varname}))
			{
				$this->category_parts[$currcat] = explode("_WORD_", $output->{$varname});
			} 
			$currcat++;
		} while (isset($output->{$varname}));
	}
}
?>
