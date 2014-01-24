<?

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

require "MagicConfig.php";

class Magic
{
	function Magic()
	{
		$this->config = new MagicConfig();
	}
	
	function load_file($filename)
	{
		if (!($filehandle = @fopen($filename, "rb"))) return undef;
		set_magic_quotes_runtime(0); 
		$text = fread($filehandle, filesize($filename));
		set_magic_quotes_runtime(get_magic_quotes_gpc());
		fclose($filehandle);
		return $text;
	}   

	function _is_ascii($file)
	{
		$arr = unpack("C*", $file);
		for($i=1; $i<sizeof($arr); $i++)
		{	
			if ($arr[$i]<32 && $arr[$i]!=13 && $arr[$i]!=10) return false;
		}
		return true;
	}
	
	function get_language($filename)
	{
		// First attempt a match with the filename extension.
		$extensionspl = explode($this->config->separator, $filename);
		$extension = $extensionspl[sizeof($extensionspl)-1];
		$langstr = $this->config->extensions[$extension];
		// We may have a choice of languages, so check.
		$langarr = explode("|", $langstr);
		
		// Got one language? Good!
		if (sizeof($langarr)==1 && $langarr[0]!="") return $langstr;
		
		// Now the real magic starts :o) We may have a narrowed-down set of options from 
		// the tests above, which will speed things up a little.
		
		// Get one /big/ string.
		$this->config->file = $this->load_file($filename);
		if ($this->config->file == undef) return "illegal";
		$this->is_ascii = $this->_is_ascii($this->config->file);

		if (isset($langstr)) 
		{
			// We don't know if all of the types are in our magic file, so filter out those that
			// aren't.
			
			$outarray = array();
			foreach($langarr as $lang)
			{
				if ($this->is_ascii && in_array($lang, $this->config->langfunctions))
					array_push($outarray, $lang);
				else if(in_array($lang, $this->config->binfunctions))
					array_push($outarray, $lang);
			}
			$testarray = $outarray;
		}
		else 
		{
			if ($this->is_ascii) $testarray = $this->config->langfunctions;
				else $testarray = $this->config->binfunctions;
		}
		foreach($testarray as $test)
		{
			$func = "detect_".$test;
			if ($this->config->$func()) return $test;
		}
	}
}

?>
