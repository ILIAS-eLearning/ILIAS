<?php

/**
* $Id$
*/

$fpath = getcwd();
$ffile = "test.xml";

function walktree ($node,$left)
{
	global $tree;
	static $left;
	
	//echo "<pre>";var_dump($node);echo "</pre>";
	$node2 = (array)$node;
	
	if ($node->type == 1)
	{
		$name = "tagname";
	
	}
	else
	{
		$name = "name";
	}
	
	$tree[$node2[0]]["name"] = $node->$name;
	$tree[$node2[0]]["left"] = $left;
	$left++;

	if ($node->has_child_nodes())
	{
		$childs = $node->child_nodes();
		
		foreach ($childs as $child)
		{
			walktree($child,$left);
		}
	}

	$tree[$node2[0]]["right"] = $left;
	$left++;

		
/*
if ($child->has_attributes())
{
foreach ($child->attributes() as $attribute)
{
$attribute2 = (array)$attribute;
//echo "<b>ATTR: ".$attribute->name."</b>";
//echo " (".$attribute2[0].")<br>";
$tree[$attribute2[0]]["name"] = $attribute->name;

$tree[$attribute2[0]]["left"] = $left;
$left++;
$tree[$attribute2[0]]["right"] = $left;
$left++;
//echo "<pre>";var_dump($attribute);echo "</pre>";
}
}
*/
}


///////////////// MAIN //////////////////////

if(!$xml = domxml_open_file($fpath."/".$ffile))
{
  echo "Error while parsing the document\n";
  exit;
}

$root = $xml->document_element();

walktree($root,1);

//echo "<pre><b>";var_dump($tree);echo "</b></pre>";

// outout tree
foreach ($tree as $key => $val)
{
	echo "(".$key.", ".$val["left"].", ".$val["right"].") <b>".$val["name"]."</b></br>";
}

/*
$node = $xml->get_element_by_id("LaTeX1");
echo "<pre><b>";var_dump($node);echo "</b></pre>";

$node = $root->child_nodes();
echo "<pre><b>";var_dump($node);echo "</b></pre>";
exit;


$last_child = $root->last_child();

$parent_node = $last_child->parent_node();

$sub_node = $last_child->last_child();

echo "<pre>";var_dump($sub_node);echo "</pre>";

echo "<pre>";var_dump($last_child->node_name());echo "</pre>";
*/
//echo "test: ".XML_PI_NODE;


//$bla = $root->new_child("WILLI", "");
//$xml->dump_file($fpath."test2.xml", false, true);
?>