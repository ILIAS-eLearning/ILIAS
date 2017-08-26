<?php
$manifest_string = "CACHE MANIFEST\n\nCACHE:\n";
$appcache = fopen('./sop/sop.appcache','w');
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('./sop/'));
foreach($objects as $name => $object) {
	
	if (preg_match('/\/\.+/',$name)) {
		continue;
	}
	
	if (preg_match('/sop\.appcache/',$name)) {
		continue;
	}
	
	if (preg_match('/sop_index\.html/',$name)) {
		continue;
	}
	
	$manifest_string .= preg_replace('/^\./','./Modules/ScormAicc',$name) . "\n";
	//echo "$name\n";
}
$manifest_string .= "\nNETWORK:\n*\n";
$manifest_string .= "\n#".date("Y-m-d H:i:s");
fwrite($appcache, $manifest_string);
fclose($appcache);
?>
