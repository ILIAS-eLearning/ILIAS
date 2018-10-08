--TEST--
PEAR_Installer->sortPackagesForUninstall() - real-world example (uninstall the SOAP package)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
/*
 * Deptree:
 * - SOAP         wants: HTTP_Request, Mail_Mime, Net_DIME, Net_URL
 * - HTTP_Request wants: Net_URL, Net_Socket
 * - Mail_Mime    wants: -nothing-
 * - Net_DIME     wants: -nothing-
 * - Net_Socket   wants: -nothing-
 * - Net_URL      wants: -nothing-
 *
 * Expected order:
 * 1. SOAP
 * 2. HTTP_Request
 * 3. Mail_Mime, Net_DIME, Net_Socket, Net_URL
 */
$p1 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_sortPackagesForUninstall' . DIRECTORY_SEPARATOR . 'SOAP-0.8.1.tgz';
$p2 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_sortPackagesForUninstall' . DIRECTORY_SEPARATOR . 'Mail_Mime-1.2.1.tgz';
$p3 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_sortPackagesForUninstall' . DIRECTORY_SEPARATOR . 'HTTP_Request-1.2.4.tgz';
$p4 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_sortPackagesForUninstall' . DIRECTORY_SEPARATOR . 'Net_URL-1.0.14.tgz';
$p5 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_sortPackagesForUninstall' . DIRECTORY_SEPARATOR . 'Net_DIME-0.3.tgz';
$p6 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_sortPackagesForUninstall' . DIRECTORY_SEPARATOR . 'Net_Socket-1.0.5.tgz';

for ($i = 1; $i <= 6; $i++) {
    $packages[] = ${"p$i"};
}
$dl = new PEAR_Installer($fakelog);
$config = &test_PEAR_Config::singleton($temp_path . '/pear.ini', $temp_path . '/pear.conf');

test_PEAR_Dependency2::singleton($config);
$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setExtensions(array('pcre' => '1.0'));
require_once 'PEAR/Command/Install.php';
class test_PEAR_Command_Install extends PEAR_Command_Install
{
    function &getDownloader(&$ui, $options, &$config)
    {
        if (!isset($GLOBALS['__Stupid_php4_a'])) {
            $GLOBALS['__Stupid_php4_a'] = new test_PEAR_Downloader($this->ui, array(), $this->config);
        }
        return $GLOBALS['__Stupid_php4_a'];
    }

    function &getInstaller(&$ui)
    {
        if (!isset($GLOBALS['__Stupid_php4_b'])) {
            $GLOBALS['__Stupid_php4_b'] = new test_PEAR_Installer($this->ui, array(), $this->config);
        }
        return $GLOBALS['__Stupid_php4_b'];
    }
}
$command = new test_PEAR_Command_Install($fakelog, $config);
$command->run('install', array(), $packages);
$phpunit->assertNoErrors('after install');
$fakelog->getLog();
$paramnames = array('Mail_Mime', 'SOAP', 'Net_DIME', 'HTTP_Request', 'Net_URL', 'Net_Socket');
$reg = &$config->getRegistry();
$params = array();
foreach ($paramnames as $name) {
    $params[] = &$reg->getPackage($name);
}
$dl->sortPackagesForUninstall($params);
$phpunit->assertEquals('SOAP', $params[0]->getPackage(), '0');
$phpunit->assertEquals('HTTP_Request', $params[1]->getPackage(), '1');

$packages = array('Mail_Mime', 'Net_DIME', 'Net_URL', 'Net_Socket');
$phpunit->assertTrue(in_array($params[2]->getPackage(), $packages), 2);
$phpunit->assertTrue(in_array($params[3]->getPackage(), $packages), 3);
$phpunit->assertTrue(in_array($params[4]->getPackage(), $packages), 4);
$phpunit->assertTrue(in_array($params[5]->getPackage(), $packages), 5);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
