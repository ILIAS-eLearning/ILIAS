<?

require_once("SeqTreeBuilder.php");

$builder=new SeqTreeBuilder();
$ret=$builder->buildNodeSeqTree("file:///Users/hendrikh/Development/eclipse/ilias3_scorm2004/ilias3_scorm2004/Modules/Scorm2004/classes/adlparser/imsmanifest2.xml");
echo json_encode($ret);
?>