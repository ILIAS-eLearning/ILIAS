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

class Output_xml
{
	function Output_xml()
	{
		$this->code		= '_WORD_';
		$this->linecomment 	= '<Category Type="Linecomment">_WORD_</Category>';
		$this->blockcomment 	= '<Category Type="Blockcomment">_WORD_</Category>';
		$this->prepro 		= '<Category Type="Prepro">_WORD_</Category>';
		$this->select 		= '<Category Type="Select">_WORD_</Category>';
		$this->quote 		= '<Category Type="Quote">_WORD_</Category>';
		$this->category_1 	= '<Category Type="Category_1">_WORD_</Category>';
		$this->category_2 	= '<Category Type="Category_2">_WORD_</Category>';
		$this->category_3 	= '<Category Type="Category_3">_WORD_</Category>';
	}
}
?>
