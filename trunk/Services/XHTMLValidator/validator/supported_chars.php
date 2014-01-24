<?php

/** callback function for array_map(). matches aliases and returns an array */
function match_aliases($str) {
    if (!preg_match_all('/Alias:\s*(\S+)/S', $str, $matches) ||
        $matches[1][0] == 'None') {
        return array();
    }

    return $matches[1];

}


/** test if the given charset is supported by the local iconv() implementation */
function test_charset($charset) {
    return (bool) @iconv($charset, 'UTF-8', 'a');
}



$file = file_get_contents('http://www.iana.org/assignments/character-sets');

preg_match_all('/Name:\s*(\S+).*[\r\n]+MIBenum:.*[\r\n]+Source:.*[\r\n]+((?:Alias:\s*\S+.*[\r\n]+)*)/', $file, $charsets);

array_shift($charsets);
$charsets[1] = array_map('match_aliases', $charsets[1]);
usort($charsets[0], 'strnatcasecmp');

$total = count($charsets[0]);
$total_alias = count($charsets[1]);
$ok = 0;
$aliases = 0;

for ($i = 0; $i < $total; $i++) {

    echo $charsets[0][$i] . ': ';

    if (test_charset($charsets[0][$i])) {
        ++$ok;
        $valid[] = $charsets[0][$i];
        echo "true";
        $a = array();

        foreach ($charsets[1][$i] as $alias) {
            if (test_charset($alias)) {
                ++$aliases;
                $a[] = $alias;
            }
        }

        if($a = join($a, ', '))
            echo "; Valid aliases: $a";

        echo "\n";

    } else {
        echo "false\n";
    }
}

echo "\n\n";
print_r($valid);
echo "\n\n";

echo number_format($ok * 100 / $total, 2, ',', '.') . "% of good charsets\n";
echo number_format($aliases * 100 / $total_alias, 2, ',', '.') . "% of good aliases\n";
echo "$total charsets\n";

?>
