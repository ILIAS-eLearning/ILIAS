<?
	function iso_to_utf8($str) {
		if (extension_loaded("mbstring"))
			return mb_convert_encoding($str, "UTF-8", "auto");;
		
		for($x=0;$x<strlen($str);$x++) {
			$num=ord(substr($str,$x,1));
		  if($num<128)
		  	$xstr.=chr($num); 
	  	else if($num<1024)
  			$xstr.=chr(($num>>6)+192).chr(($num&63)+128);
	  	else if($num<32768)
	  		$xstr.=chr(($num>>12)+240).chr((($num>>6)&63)+128).chr(($num&63)+128);
	  	else if($num<2097152)
  			$xstr.=chr($num>>18+240).chr((($num>>12)&63)+128).chr(($num>>6)&63+128).chr($num&63+128);
		}
		return $xstr;
	}
  
?>