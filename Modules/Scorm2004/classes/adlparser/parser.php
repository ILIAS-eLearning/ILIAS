<?

require_once("SeqTreeBuilder.php");

$builder=new SeqTreeBuilder();
$ret=$builder->buildNodeSeqTree("file:///Users/hendrikh/Desktop/parser/imsmanifest.xml");
echo json_encode($ret);
?>