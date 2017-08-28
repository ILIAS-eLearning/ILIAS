<?php
    /* 
     * Retrieve the fields from the POST request. This file assumes that the results are
     * submitted in XML format (YAHOO.tool.TestFormat.XML).
     */

    $results = $_POST['results'];
    $userAgent = $_POST['useragent'];
    $timestamp = $_POST['timestamp'];
   
    /*
     * Parse the XML into a series of structs. This makes it easier to handle the data
     * from PHP.
     */
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $results, $values, $tags);
    xml_parser_free($parser);

    /*
     * Construct some output. Example data structure:
     * [163] => Array
     *   (
     *       [tag] => test
     *       [type] => complete
     *       [level] => 7
     *       [attributes] => Array
     *           (
     *               [name] => testMetaKey
     *               [result] => pass
     *               [message] => Test passed
     *           )
     *
     *   )
     *
     * [164] => Array
     *   (
     *       [tag] => testcase
     *       [type] => close
     *       [level] => 6
     *   )     
     */
    $data = '';
    for ($i=0; $i < count($values); $i++){
        $element = $values[$i];
        
        switch($element['tag']){
            case 'testsuite':
                if ($element['type'] == 'open'){
                    $temp = 'Test Suite: '.$element['attributes']['name'].' (Passed: '.$element['attributes']['passed'].', Failed: '.$element['attributes']['failed'].', Ignored: '.$element['attributes']['ignored'].', Total: '.$element['attributes']['total'].', Duration: '.$element['attributes']['duration'].')'; 
                    $data .= str_pad($temp, strlen($temp) + ((int)$element['level']), ' ', STR_PAD_LEFT);
                }
                break;
            case 'testcase':
                if ($element['type'] == 'open'){
                    $temp = 'Test Case: '.$element['attributes']['name'].' (Passed: '.$element['attributes']['passed'].', Failed: '.$element['attributes']['failed'].', Ignored: '.$element['attributes']['ignored'].', Total: '.$element['attributes']['total'].')'; 
                    $data .= str_pad($temp, strlen($temp) + ((int)$element['level']), ' ', STR_PAD_LEFT);
                }
                break;        
            case 'test':
                $temp = $element['attributes']['name'].': '.$element['attributes']['message'];
                $data .= str_pad($temp, strlen($temp) + ((int)$element['level']), ' ', STR_PAD_LEFT);
                break;
        }
        $data .= '\n';
        
    }
    
    echo $results;
    
    //write to some file
    $handle = fopen("examplefile.txt", "w");
    fwrite($handle, $data);
    fclose($handle);
?>