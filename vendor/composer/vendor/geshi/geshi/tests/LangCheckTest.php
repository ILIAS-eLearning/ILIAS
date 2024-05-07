<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/LangCheck.php';

class LangCheckTest extends TestCase
{
    /**
     * Read all available language files
     *
     * @return array
     */
    public function languageProvider()
    {
        $data = array();
        foreach (glob(__DIR__ . '/../src/geshi/*.php') as $file) {
            $base = basename($file, '.php');
            $data[$base] = array($file);
        }
        return $data;
    }

    /**
     * @dataProvider languageProvider
     * @param string $file
     */
    public function test_langfile($file)
    {
        $check = new LangCheck($file);
        $result = $check->runChecks();
        $issues = $check->getIssuesAsString();

        $this->assertTrue($result, "The following issues were found in $file:\n" . $issues);
    }
}
