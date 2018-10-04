<?php

function hl($m)
{
    return sprintf("<blockquote>%s</blockquote>\n", highlight_string($m[1], true));
}
function mf($f, &$m)
{
    return preg_match_all(
        '/\/\* *\{\{\{ *proto (.*?)(\n|$)(.*?)PHP_(?:FUNCTION|METHOD)\((.*?)\)/s', 
        file_get_contents($f), $m);
}
function ff($t)
{
    $t = preg_replace('/^ \* /m', '', trim($t, "*/ \n"));
    $t = preg_replace_callback('/(\<\?php.*?\?\>)/s', 'hl', $t);
    $t = str_replace("<br />\n<br />\n", "</p>\n<p>", nl2br(preg_replace('/\n *\* */', "\n", $t)));
    $t = preg_replace('/(\<br \/\>\n)+\<pre\>(\<br \/\>\n)+/', '</p><pre>', $t);
    $t = preg_replace('/(\<br \/\>\n)+\<\/pre\>(\<br \/\>\n)+/', '</pre><p>', $t);
    $t = str_replace("</span><br />\n</code>", "</span></code>", $t);
    return sprintf('<p>%s</p>', ltrim($t, ' *'));
}
function e($s)
{
    $a = func_get_args();
    array_unshift($a, STDERR);
    call_user_func_array('fprintf', $a);
}

$preface = <<<_PREFACE
<html>
<head>
    <title>Function Summary of ext/%s</title>
    <style>
        body { 
            font-size: 80%%; 
            font-family: sans-serif; 
        } 
        h2, h3 { 
            color: #339; 
            clear: both;
            font-size: 1.2em;
            background: #ffc;
            padding: .2em;
        } 
        h2.o {
            color: #66b; 
            clear: both;
            font-size: 1.3em;
            background: #f0f0f0;
            padding: .2em;
        }
        p { 
            margin-left: 1em;
        } 
        pre { 
            font-size: 1.2em; 
        } 
        br { 
            display: none; 
        } 
        blockquote {
            margin-bottom: 3em;
            border: 1px solid #ccc;
            background: #f0f0f0;
            padding: 0em 1em;
            width: auto;
            float: left;
        }
        p, pre {
            clear: both;
        }
        p br, pre code br { 
            display: block; 
        } 
        .toc {
        	position: absolute;
        	top: 10px;
        	right: 10px;
        	width: 300px;
        	height: 95%%;
        	overflow: scroll;
        	font-size: .9em;
		}
		body>div.toc {
			position: fixed;
		}
		.toc ul {
			padding-left: 15px;
			margin-left: 0;
		}
		.toc li {
			padding: 0;
			margin: 0;
		}
    </style>
</head>
<body>
_PREFACE;

$footer = <<<_FOOTER
    <p><b>Generated at: %s</b></p>
</body>
</html>

_FOOTER;

if ($_SERVER['argc'] < 2) {
    die("Usage: {$_SERVER['argv'][0]} <file>[ <file> ...]\n");
}

$TOC = array();

printf($preface, basename(getcwd()));

foreach (array_slice($_SERVER['argv'], 1) as $fp) {
    foreach (glob($fp) as $f) {
        
        if (mf($f, $m)) {
            e("\nAnalyzing %s\n", basename($f));
            printf("<h1 id=\"%s\">%s</h1>\n", basename($f), basename($f));
            foreach ($m[1] as $i => $p) {
                e("Documenting $p\n");
                if ($o = preg_match('/^(.*), (.*)$/', $m[4][$i], $n)) {
                    if ($n[2] == '__construct') {
                        printf("<h2 id=\"%s\" class=\"o\">%s</h2>\n", $n[1], $n[1]);
                    }
                	$TOC[basename($f)][$n[1]][$n[2]] = $n[1].'::'.$n[2].'()';
                	printf("<h%d id=\"%s\">%s</h%d>\n", 3, $n[1].'_'.$n[2], $p, 3);
				} else {
					$TOC[basename($f)][$m[4][$i]] = $m[4][$i].'()';
					printf("<h%d id=\"%s\">%s</h%d>\n", 2, $m[4][$i], $p, 2);
				}
				print ff($m[3][$i]) ."\n";
            }
            print "<hr noshade>\n";
        }
    }
}
printf("<div class=\"toc\"><strong>Table of Contents</strong>\n<ul>\n");
foreach ($TOC as $file => $f) {
	printf("<li><a href=\"#%s\">%s\n<ul>\n", $file, $file);
	foreach ($f as $cof => $met) {
		if (is_array($met)) {
			foreach ($met as $id => $m) {
				printf("<li><a href=\"#%s_%s\">%s</a></li>\n", $cof, $id, $m);
			}
		} else {
			printf("<li><a href=\"#%s\">%s</a>\n", $cof, $cof);
		}
		printf("</li>\n");
	}
	printf("</ul>\n</li>\n");
}
printf("</ul>\n</div>\n");

printf($footer, date('r'));
e("\nDone\n");
?>

