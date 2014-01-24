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

class Output_css
{
	function Output_css()
	{
		$this->code		= '_WORD_';
		$this->linecomment 	= '<span class="ilc_CodeLinecomment">_WORD_</span>';
		$this->blockcomment 	= '<span class="ilc_CodeBlockcomment">_WORD_</span>';
		$this->prepro 		= '<span class="ilc_CodePrepro">_WORD_</span>';
		$this->select 		= '<span class="ilc_CodeSelect">_WORD_</span>';
		$this->quote 		= '<span class="ilc_CodeQuote">_WORD_</span>';
		$this->category_1 	= '<span class="ilc_CodeCategory_1">_WORD_</span>';
		$this->category_2 	= '<span class="ilc_CodeCategory_2">_WORD_</span>';
		$this->category_3 	= '<span class="ilc_CodeCategory_3">_WORD_</span>';
	}
}
?>
