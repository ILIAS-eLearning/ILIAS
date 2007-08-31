<?

require_once("SeqTreeBuilder.php");

$builder=new SeqTreeBuilder();
$ret=$builder->buildNodeSeqTree("file:///Users/hendrikh/Desktop/imsmanifest.xml");
echo json_encode($ret);
?>