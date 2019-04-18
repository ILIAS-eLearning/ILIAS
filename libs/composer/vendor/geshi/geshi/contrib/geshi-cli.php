#!/usr/bin/env php
<?php
/**
* GeSHi command line interface.
* Allows you to run geshi from shell scripts or simply yourself via cli.
*
* You will need Console_CommandLine and MIME_Type from PEAR:
* $ pear install console_commandline mime_type
*
* @author Christian Weiske <cweiske@php.net>
*/
require_once 'Console/CommandLine.php';
require_once dirname(__FILE__) . '/../src/geshi.php';

$cli = new GeSHi_Cli();
$cli->run();

class GeSHi_Cli
{
    /**
    * Maps MIME types to GeSHi file formats.
    *
    * @var array
    */
    protected static $typeToFormat = array(
        //FIXME
    );

    /**
    * Runs the script and outputs highlighted code.
    *
    * @return void
    */
    public function run()
    {
        $parser = $this->createParser();
        try {
            $result = $parser->parse();
            $file   = $result->args['infile'];
            $format = $result->options['format'];
            $css    = $result->options['css'];

            if ($format == '') {
                $format = $this->detectFormat($file);
            }

            $geshi = new GeSHi(file_get_contents($file), $format);
            if ($css) {
                $geshi->enable_classes();
                echo '<style type="text/css">' . "\n"
                    . $geshi->get_stylesheet(true)
                    . "</style>\n";
            }
            echo $geshi->parse_code() . "\n";
        } catch (Exception $e) {
            $parser->displayError($e->getMessage());
            exit(1);
        }
    }



    /**
    * Creates the command line parser and populates it with all allowed
    * options and parameters.
    *
    * @return Console_CommandLine CommandLine object
    */
    protected function createParser()
    {
        $geshi = new GeSHi();
        $parser = new Console_CommandLine();
        $parser->description = 'CLI interface to GeSHi, the generic syntax highlighter';
        $parser->version     = '0.1.0';

        $parser->addOption('css', array(
            'long_name'   => '--css',
            'description' => 'Use CSS classes',
            'action'      => 'StoreTrue',
            'default'     => false,
        ));
        $langs = $geshi->get_supported_languages();
        sort($langs);
        $parser->addOption('format', array(
            'short_name'  => '-f',
            'long_name'   => '--format',
            'description' => 'Format of file to highlight (e.g. php).',
            'help_name'   => 'FORMAT',
            'action'      => 'StoreString',
            'default'     => false,
            'choices'     => $langs,
        ));

        $parser->addArgument('infile', array('help_name' => 'source file'));

        return $parser;
    }



    /**
    * Detects the GeSHi language format of a file.
    * It first detects the MIME type of the file and uses
    * $typeToFormat to map that to GeSHi language formats.
    *
    * @param string $filename Full or relative path of the file to detect
    *
    * @return string GeSHi language format (e.g. 'php')
    */
    protected function detectFormat($filename)
    {
        require_once 'MIME/Type.php';
        $type = MIME_Type::autoDetect($filename);

        if (!isset(self::$typeToFormat[$type])) {
            throw new Exception(
                'No idea which format this is: ' . $type . "\n"
                . 'Please use "-f $format"'
            );
        }
        return self::$typeToFormat[$type];
    }
}
?>
