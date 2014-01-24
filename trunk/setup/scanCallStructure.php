<?php

include "./setup/classes/class.ilCtrlStructureReader.php";

$reader = new ilCtrlStructureReader();

$reader->getStructure();

$class_script = $reader->class_script;
$class_childs = $reader->class_childs;

foreach($class_script as $class => $script)
{
	echo "<br><br><b>$class: $script</b>";
	if (is_array($class_childs[$class]))
	{
		echo "<br>calls:";
		foreach($class_childs[$class] as $child)
		{
			echo " ".$child;
		}
	}
}


?>
