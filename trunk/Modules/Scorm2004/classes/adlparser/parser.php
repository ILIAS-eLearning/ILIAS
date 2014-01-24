<?php

require_once("SeqTreeBuilder.php");

$builder=new SeqTreeBuilder();
$ret=$builder->buildNodeSeqTree("imsmanifest.xml");
echo json_encode($ret);
?>