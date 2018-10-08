<?php
require_once 'PEAR.php';
require_once 'System.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/Command.php';
require_once 'PEAR/Common.php';
class PEAR_Start extends PEAR
{
    var $bin_dir;
    var $data_dir;
    var $cfg_dir;
    var $www_dir;
    var $man_dir;
    var $install_pfc;
    var $corePackages =
        array(
            'Archive_Tar',
            'Console_Getopt',
            'PEAR',
            'Structures_Graph',
            'XML_Util',
        );
    var $local_dir = array();
    var $origpwd;
    var $pfc_packages = array(
            'DB',
            'Net_Socket',
            'Net_SMTP',
            'Mail',
            'XML_Parser',
            'XML_RPC',
            'PHPUnit'
        );
    var $php_dir;
    var $php_bin;
    var $pear_conf;
    var $validPHPBin = false;
    var $test_dir;
    var $download_dir;
    var $temp_dir;
    var $config =
        array(
            'prefix',
            'bin_dir',
            'php_dir',
            'doc_dir',
            'data_dir',
            'cfg_dir',
            'www_dir',
            'man_dir',
            'test_dir',
            'temp_dir',
            'download_dir',
            'pear_conf',
        );
    var $prefix;
    var $progress = 0;
    var $configPrompt =
        array(
            'prefix' => 'Installation base ($prefix)',
            'temp_dir' => 'Temporary directory for processing',
            'download_dir' => 'Temporary directory for downloads',
            'bin_dir' => 'Binaries directory',
            'php_dir' => 'PHP code directory ($php_dir)',
            'doc_dir' => 'Documentation directory',
            'data_dir' => 'Data directory',
            'cfg_dir' => 'User-modifiable configuration files directory',
            'www_dir' => 'Public Web Files directory',
            'man_dir' => 'System manual pages directory',
            'test_dir' => 'Tests directory',
            'pear_conf' => 'Name of configuration file',
        );

    var $localInstall;
    var $PEARConfig;
    var $tarball = array();
    var $ptmp;

    function __construct()
    {
        parent::__construct();
        if (OS_WINDOWS) {
            $this->configPrompt['php_bin'] = 'Path to CLI php.exe';
            $this->config[] = 'php_bin';
            $this->prefix = getcwd();

            if (!@is_dir($this->prefix)) {
                if (@is_dir('c:\php5')) {
                    $this->prefix = 'c:\php5';
                } elseif (@is_dir('c:\php4')) {
                    $this->prefix = 'c:\php4';
                } elseif (@is_dir('c:\php')) {
                    $this->prefix = 'c:\php';
                }
            }

            $slash = "\\";
            if (strrpos($this->prefix, '\\') === (strlen($this->prefix) - 1)) {
                $slash = '';
            }

            $this->localInstall = false;
            $this->bin_dir   = '$prefix';
            $this->temp_dir   = '$prefix' . $slash . 'tmp';
            $this->download_dir   = '$prefix' . $slash . 'tmp';
            $this->php_dir   = '$prefix' . $slash . 'pear';
            $this->doc_dir   = '$prefix' . $slash . 'docs';
            $this->data_dir  = '$prefix' . $slash . 'data';
            $this->test_dir  = '$prefix' . $slash . 'tests';
            $this->www_dir  = '$prefix' . $slash . 'www';
            $this->man_dir  = '$prefix' . $slash . 'man';
            $this->cfg_dir  = '$prefix' . $slash . 'cfg';
            $this->pear_conf = PEAR_CONFIG_SYSCONFDIR . '\\pear.ini';
            /*
             * Detects php.exe
             */
            $this->validPHPBin = true;
            if ($t = $this->safeGetenv('PHP_PEAR_PHP_BIN')) {
                $this->php_bin   = dirname($t);
            } elseif ($t = $this->safeGetenv('PHP_BIN')) {
                $this->php_bin   = dirname($t);
            } elseif ($t = System::which('php')) {
                $this->php_bin = dirname($t);
            } elseif (is_file($this->prefix . '\cli\php.exe')) {
                $this->php_bin = $this->prefix . '\cli';
            } elseif (is_file($this->prefix . '\php.exe')) {
                $this->php_bin = $this->prefix;
            }
            $phpexe = OS_WINDOWS ? '\\php.exe' : '/php';
            if ($this->php_bin && !is_file($this->php_bin . $phpexe)) {
                $this->php_bin = '';
            } else {
                if (strpos($this->php_bin, ':') === 0) {
                    $this->php_bin = getcwd() . DIRECTORY_SEPARATOR . $this->php_bin;
                }
            }
            if (!is_file($this->php_bin . $phpexe)) {
                if (is_file('c:/php/cli/php.exe')) {
                    $this->php_bin = 'c"\\php\\cli';
                } elseif (is_file('c:/php5/php.exe')) {
                    $this->php_bin = 'c:\\php5';
                } elseif (is_file('c:/php4/cli/php.exe')) {
                    $this->php_bin = 'c:\\php4\\cli';
                } else {
                    $this->validPHPBin = false;
                }
            }
        } else {
            $this->prefix = dirname(PHP_BINDIR);
            $this->pear_conf = PEAR_CONFIG_SYSCONFDIR . '/pear.conf';
            if ($this->getCurrentUser() != 'root') {
                $this->prefix = $this->safeGetenv('HOME') . '/pear';
                $this->pear_conf = $this->safeGetenv('HOME') . '.pearrc';
            }
            $this->bin_dir   = '$prefix/bin';
            $this->php_dir   = '$prefix/share/pear';
            $this->temp_dir  = '/tmp/pear/install';
            $this->download_dir  = '/tmp/pear/install';
            $this->doc_dir   = '$prefix/docs';
            $this->www_dir   = '$prefix/www';
            $this->cfg_dir   = '$prefix/cfg';
            $this->data_dir  = '$prefix/data';
            $this->test_dir  = '$prefix/tests';
            $this->man_dir  = '$prefix/man';
            // check if the user has installed PHP with PHP or GNU layout
            if (@is_dir("$this->prefix/lib/php/.registry")) {
                $this->php_dir = '$prefix/lib/php';
            } elseif (@is_dir("$this->prefix/share/pear/lib/.registry")) {
                $this->php_dir = '$prefix/share/pear/lib';
                $this->doc_dir   = '$prefix/share/pear/docs';
                $this->data_dir  = '$prefix/share/pear/data';
                $this->test_dir  = '$prefix/share/pear/tests';
            } elseif (@is_dir("$this->prefix/share/php/.registry")) {
                $this->php_dir = '$prefix/share/php';
            }
        }
    }

    /**
     * Get the name of the user running the script.
     * Only needed on unix for now.
     *
     * @return string Name of the user ("root", "cweiske")
     */
    function getCurrentUser()
    {
        if (isset($_ENV['USER'])) {
            return $_ENV['USER'];
        } else {
            return trim(`whoami`);
        }
    }

    function safeGetenv($var)
    {
        if (is_array($_ENV) && isset($_ENV[$var])) {
            return $_ENV[$var];
        }

        return getenv($var);
    }

    function show($stuff)
    {
        print $stuff;
    }

    function locatePackagesToInstall()
    {
        $dp = @opendir(dirname(__FILE__) . '/go-pear-tarballs');
        if (empty($dp)) {
            return PEAR::raiseError("while locating packages to install: opendir('" .
                dirname(__FILE__) . "/go-pear-tarballs') failed");
        }

        $potentials = array();
        while (false !== ($entry = readdir($dp))) {
            if ($entry{0} == '.' || !in_array(substr($entry, -4), array('.tar', '.tgz'))) {
                continue;
            }
            $potentials[] = $entry;
        }

        closedir($dp);
        $notfound = array();
        foreach ($this->corePackages as $package) {
            foreach ($potentials as $i => $candidate) {
                if (preg_match('/^' . $package . '-' . _PEAR_COMMON_PACKAGE_VERSION_PREG
                      . '\.(tar|tgz)\\z/', $candidate)) {
                    $this->tarball[$package] = dirname(__FILE__) . '/go-pear-tarballs/' . $candidate;
                    unset($potentials[$i]);
                    continue 2;
                }
            }

            $notfound[] = $package;
        }

        if (count($notfound)) {
            return PEAR::raiseError("No tarballs found for core packages: " .
                    implode(', ', $notfound));
        }

        $this->tarball = array_merge($this->tarball, $potentials);
    }

    function setupTempStuff()
    {
        if (!($this->ptmp = System::mktemp(array('-d')))) {
            $this->show("System's Tempdir failed, trying to use \$prefix/tmp ...");
            $res = System::mkDir(array($this->prefix . '/tmp'));
            if (!$res) {
                return PEAR::raiseError('mkdir ' . $this->prefix . '/tmp ... failed');
            }

            $_temp = tempnam($this->prefix . '/tmp', 'gope');
            System::rm(array('-rf', $_temp));
            System::mkdir(array('-p','-m', '0700', $_temp));
            $this->ptmp = $this->prefix . '/tmp';
            $ok = @chdir($this->ptmp);

            if (!$ok) { // This should not happen, really ;)
                $this->bail('chdir ' . $this->ptmp . ' ... failed');
            }

            print "ok\n";

            // Adjust TEMPDIR envvars
            if (!isset($_ENV)) {
                $_ENV = array();
            };
            $_ENV['TMPDIR'] = $_ENV['TEMP'] = $this->prefix . '/tmp';
        }

        return @chdir($this->ptmp);
    }

    /**
     * Try to detect the kind of SAPI used by the
     * the given php.exe.
     * @author Pierrre-Alain Joye
     */
    function win32DetectPHPSAPI()
    {
        if ($this->php_bin != '') {
            if (OS_WINDOWS) {
                exec('"' . $this->php_bin . '\\php.exe" -v', $res);
            } else {
                exec('"' . $this->php_bin . '/php" -v', $res);
            }

            if (is_array($res)) {
                if (isset($res[0]) && strpos($res[0],"(cli)")) {
                    return 'cli';
                }

                if (isset($res[0]) && strpos($res[0],"cgi")) {
                    return 'cgi';
                }

                if (isset($res[0]) && strpos($res[0],"cgi-fcgi")) {
                    return 'cgi';
                }

                return 'unknown';
            }
        }

        return 'unknown';
    }

    function doInstall()
    {
        print "Beginning install...\n";
        // finish php_bin config
        if (OS_WINDOWS) {
            $this->php_bin .= '\\php.exe';
        } else {
            $this->php_bin .= '/php';
        }
        $this->PEARConfig = &PEAR_Config::singleton($this->pear_conf, $this->pear_conf);
        $this->PEARConfig->set('preferred_state', 'stable');
        foreach ($this->config as $var) {
            if ($var == 'pear_conf' || $var == 'prefix') {
                continue;
            }
            $this->PEARConfig->set($var, $this->$var);
        }

        $this->PEARConfig->store();
//       $this->PEARConfig->set('verbose', 6);
        print "Configuration written to $this->pear_conf...\n";
        $this->registry = &$this->PEARConfig->getRegistry();
        print "Initialized registry...\n";
        $install = &PEAR_Command::factory('install', $this->PEARConfig);
        print "Preparing to install...\n";
        $options = array(
            'nodeps' => true,
            'force' => true,
            'upgrade' => true,
            );
        foreach ($this->tarball as $pkg => $src) {
            print "installing $src...\n";
        }
        $install->run('install', $options, array_values($this->tarball));
    }

    function postProcessConfigVars()
    {
        foreach ($this->config as $n => $var) {
            for ($m = 1; $m <= count($this->config); $m++) {
                $var2 = $this->config[$m];
                $this->$var = str_replace('$'.$var2, $this->$var2, $this->$var);
            }
        }

        foreach ($this->config as $var) {
            $dir = $this->$var;

            if (!preg_match('/_dir\\z/', $var)) {
                continue;
            }

            if (!@is_dir($dir)) {
                if (!System::mkDir(array('-p', $dir))) {
                    $root = OS_WINDOWS ? 'administrator' : 'root';
                    return PEAR::raiseError("Unable to create {$this->configPrompt[$var]} $dir.
Run this script as $root or pick another location.\n");
                }
            }
        }
    }

    /**
     * Get the php.ini file used with the current
     * process or with the given php.exe
     *
     * Horrible hack, but well ;)
     *
     * Not used yet, will add the support later
     * @author Pierre-Alain Joye <paj@pearfr.org>
     */
    function getPhpiniPath()
    {
        $pathIni = get_cfg_var('cfg_file_path');
        if ($pathIni && is_file($pathIni)) {
            return $pathIni;
        }

        // Oh well, we can keep this too :)
        // I dunno if get_cfg_var() is safe on every OS
        if (OS_WINDOWS) {
            // on Windows, we can be pretty sure that there is a php.ini
            // file somewhere
            do {
                $php_ini = PHP_CONFIG_FILE_PATH . DIRECTORY_SEPARATOR . 'php.ini';
                if (@file_exists($php_ini)) {
                    break;
                }
                $php_ini = 'c:\winnt\php.ini';
                if (@file_exists($php_ini)) {
                    break;
                }
                $php_ini = 'c:\windows\php.ini';
            } while (false);
        } else {
            $php_ini = PHP_CONFIG_FILE_PATH . DIRECTORY_SEPARATOR . 'php.ini';
        }

        if (@is_file($php_ini)) {
            return $php_ini;
        }

        // We re running in hackz&troubles :)
        ob_implicit_flush(false);
        ob_start();
        phpinfo(INFO_GENERAL);
        $strInfo = ob_get_contents();
        ob_end_clean();
        ob_implicit_flush(true);

        if (php_sapi_name() != 'cli') {
            $strInfo = strip_tags($strInfo,'<td>');
            $arrayInfo = explode("</td>", $strInfo );
            $cli = false;
        } else {
            $arrayInfo = explode("\n", $strInfo);
            $cli = true;
        }

        foreach ($arrayInfo as $val) {
            if (strpos($val,"php.ini")) {
                if ($cli) {
                    list(,$pathIni) = explode('=>', $val);
                } else {
                    $pathIni = strip_tags(trim($val));
                }
                $pathIni = trim($pathIni);
                if (is_file($pathIni)) {
                    return $pathIni;
                }
            }
        }

        return false;
    }
}
?>
