<?php

include "include/inc.header.php";

$xml = "<Test>content</Test>";

$dom = domxml_open_mem($xml);

// xml tests
$xpc = xpath_new_context($dom);
$path = "//Test";
$res = & xpath_eval($xpc, $path);
for($i = 0; $i < count($res->nodeset); $i++)
{
	$node = $res->nodeset[$i];
	$node->set_content("foo&bar");
}

$xml = $dom->dump_mem(0, "UTF-8");

// xsl tests
$xsl = file_get_contents("xmlcompliancetest.xsl");
//$xsl = file_get_contents("content/page.xsl");
$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
$xh = xslt_create();
$params = array();
$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);

echo htmlentities($output);
?>