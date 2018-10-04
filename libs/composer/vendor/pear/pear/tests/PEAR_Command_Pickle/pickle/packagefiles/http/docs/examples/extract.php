<?php
/**
 * extract examples from tutorial.txt
 */

if (preg_match_all('/\n- ([^\n]+).*?(\<\?.+?\?\>)/s', file_get_contents($_SERVER['argv'][1]), $matches)) {
	for ($i = 0; $i < count($matches[0]); $i++) {
		file_put_contents(preg_replace('/\W/', '_', $matches[1][$i]).".php", $matches[2][$i]."\n");
	}
}

?>
