<?php
/**
 * Generate the Command/<command>.xml file from its php file
 * Outputs the XML to stdout.
 */

echo "Starting the XML generation process\n\n";

// Name of the command, eg 'Remote' for Remote.php's XML output
$dir = 'PEAR/Command/';
foreach (scandir($dir) as $file) {
    $file = explode('.', $file);
    if (isset($file[1]) && $file[1] === 'php' && $file[0] != 'Common') {
        echo "Generating XML for " . $file[0] . " \n";
        generateXML($file[0]);
    }
}

echo "\nDone.\n";

function generateXML($name)
{
    $file = 'PEAR/Command/' . $name . '.php';
    if (!file_exists($file)) {
        die('File '.$file.' doesn\'t exist, perhaps '.$name.' is not a valid command name.');
    }

    require_once $file;
    $cmd_name = 'PEAR_Command_' . $name;
    $a = 't';
    $cmd = new $cmd_name($a, $a);

    $xml = '<commands version="1.0">'."\n";
    foreach ($cmd->commands as $command => $docs) {
        $command = htmlentities($command, ENT_QUOTES, 'UTF-8');
        $xml .= ' <'.$command.'>'."\n";
        $xml .= '  <summary>'.htmlentities($docs['summary'], ENT_QUOTES, 'UTF-8', false)."</summary>\n";
        $xml .= '  <function>'.htmlentities($docs['function'], ENT_QUOTES, 'UTF-8', false)."</function>\n";
        $xml .= '  <shortcut>'.htmlentities($docs['shortcut'], ENT_QUOTES, 'UTF-8', false)."</shortcut>\n";
        if (count($docs['options']) === 0) {
            $xml .= "  <options />\n";
        } else {
            $xml .= "  <options>\n";
            foreach($docs['options'] as $option => $opt_docs) {
                $option = htmlentities($option, ENT_QUOTES, 'UTF-8', false);
                $xml .= '   <'.$option.'>'."\n";
                $xml .= '    <shortopt>';
                if (isset($opt_docs['shortopt'])) {
                    $xml .= htmlentities($opt_docs['shortopt'], ENT_QUOTES, 'UTF-8', false);
                }

                $xml .= "</shortopt>\n";
                $xml .= '    <doc>'.htmlentities($opt_docs['doc'], ENT_QUOTES, 'UTF-8', false)."</doc>\n";
                if (isset($opt_docs['arg']) && $opt_docs['arg'] != '') {
                    $xml .= '    <arg>'.htmlentities($opt_docs['arg'], ENT_QUOTES, 'UTF-8', false)."</arg>\n";
                }
                $xml .= '   </'.$option.'>'."\n";
            }
            $xml .= "  </options>\n";
        }
        $xml .= '  <doc>'.htmlentities($docs['doc'], ENT_QUOTES, 'UTF-8', false)."</doc>\n";
        $xml .= ' </'.$command.'>'."\n";
    }
    $xml .= '</commands>';

    file_put_contents('PEAR/Command/' . $name . '.xml', $xml);
}
