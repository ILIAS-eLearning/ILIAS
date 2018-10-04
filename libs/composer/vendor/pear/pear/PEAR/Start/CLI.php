<?php
require_once 'PEAR/Start.php';
class PEAR_Start_CLI extends PEAR_Start
{

    var $descLength;
    var $descFormat;
    var $first;
    var $last;
    var $origpwd;
    var $tty;

    /**
     * Path to .vbs file for directory selection
     * @var string
     */
    var $cscript;

    /**
     * SAPI for selected php.exe
     * @var string
     */
    var $php_bin_sapi;

    function __construct()
    {
        parent::__construct();
        ini_set('html_errors', 0);
        define('WIN32GUI', OS_WINDOWS && php_sapi_name() == 'cli' && System::which('cscript'));
        $this->tty = OS_WINDOWS ? @fopen('\con', 'r') : @fopen('/dev/tty', 'r');

        if (!$this->tty) {
            $this->tty = fopen('php://stdin', 'r');
        }
        $this->origpwd = getcwd();
        $this->config = array_keys($this->configPrompt);

        // make indices run from 1...
        array_unshift($this->config, "");
        unset($this->config[0]);
        reset($this->config);
        $this->descLength = max(array_map('strlen', $this->configPrompt));
        $this->descFormat = "%-{$this->descLength}s";
        $this->first = key($this->config);
        end($this->config);
        $this->last = key($this->config);
        PEAR_Command::setFrontendType('CLI');
    }

    function _PEAR_Start_CLI()
    {
        if ($this->tty) {
            @fclose($this->tty);
        }
        if ($this->cscript) {
            @unlink($this->cscript);
        }
    }

    function run()
    {
        if (PEAR::isError($err = $this->locatePackagesToInstall())) {
            return $err;
        }
        $this->startupQuestion();
        $this->setupTempStuff();
        $this->getInstallLocations();
        $this->displayPreamble();
        if (PEAR::isError($err = $this->postProcessConfigVars())) {
            return $err;
        }
        $this->doInstall();
        $this->finishInstall();
    }

    function startupQuestion()
    {
        if (OS_WINDOWS) {
            print "
Are you installing a system-wide PEAR or a local copy?
(system|local) [system] : ";
            $tmp = trim(fgets($this->tty, 1024));
            if (!empty($tmp) && strtolower($tmp) !== 'system') {
                print "Please confirm local copy by typing 'yes' : ";
                $tmp = trim(fgets($this->tty, 1024));
                if (strtolower($tmp) == 'yes') {
                    $slash = "\\";
                    if (strrpos($this->prefix, '\\') === (strlen($this->prefix) - 1)) {
                        $slash = '';
                    }

                    $this->localInstall = true;
                    $this->pear_conf = '$prefix' . $slash . 'pear.ini';
                }
            }
        } else {
            if ($this->getCurrentUser() == 'root') {
                return;
            }
            $this->pear_conf = $this->safeGetenv('HOME') . '/.pearrc';
        }
    }

    function getInstallLocations()
    {
        while (true) {
            print "
Below is a suggested file layout for your new PEAR installation.  To
change individual locations, type the number in front of the
directory.  Type 'all' to change all of them or simply press Enter to
accept these locations.

";

            foreach ($this->config as $n => $var) {
                $fullvar = $this->$var;
                foreach ($this->config as $blah => $unused) {
                    foreach ($this->config as $m => $var2) {
                        $fullvar = str_replace('$'.$var2, $this->$var2, $fullvar);
                    }
                }
                printf("%2d. $this->descFormat : %s\n", $n, $this->configPrompt[$var], $fullvar);
            }

            print "\n$this->first-$this->last, 'all' or Enter to continue: ";
            $tmp = trim(fgets($this->tty, 1024));
            if (empty($tmp)) {
                if (OS_WINDOWS && !$this->validPHPBin) {
                    echo "**ERROR**
Please, enter the php.exe path.

";
                } else {
                    break;
                }
            }

            if (isset($this->config[(int)$tmp])) {
                $var = $this->config[(int)$tmp];
                $desc = $this->configPrompt[$var];
                $current = $this->$var;
                if (WIN32GUI && $var != 'pear_conf'){
                    $tmp = $this->win32BrowseForFolder("Choose a Folder for $desc [$current] :");
                    $tmp.= '\\';
                } else {
                    print "(Use \$prefix as a shortcut for '$this->prefix', etc.)
$desc [$current] : ";
                    $tmp = trim(fgets($this->tty, 1024));
                }
                $old = $this->$var;
                $this->$var = $$var = $tmp;
                if (OS_WINDOWS && $var=='php_bin') {
                    if ($this->validatePhpExecutable($tmp)) {
                        $this->php_bin = $tmp;
                    } else {
                        $this->php_bin = $old;
                    }
                }
            } elseif ($tmp == 'all') {
                foreach ($this->config as $n => $var) {
                    $desc = $this->configPrompt[$var];
                    $current = $this->$var;
                    print "$desc [$current] : ";
                    $tmp = trim(fgets($this->tty, 1024));
                    if (!empty($tmp)) {
                        $this->$var = $tmp;
                    }
                }
            }
        }
    }

    function validatePhpExecutable($tmp)
    {
        if (OS_WINDOWS) {
            if (strpos($tmp, 'php.exe')) {
                $tmp = str_replace('php.exe', '', $tmp);
            }
            if (file_exists($tmp . DIRECTORY_SEPARATOR . 'php.exe')) {
                $tmp = $tmp . DIRECTORY_SEPARATOR . 'php.exe';
                $this->php_bin_sapi = $this->win32DetectPHPSAPI();
                if ($this->php_bin_sapi=='cgi'){
                    print "
******************************************************************************
NOTICE! We found php.exe under $this->php_bin, it uses a $this->php_bin_sapi SAPI.
PEAR commandline tool works well with it.
If you have a CLI php.exe available, we recommend using it.

Press Enter to continue...";
                    $tmp = trim(fgets($this->tty, 1024));
                } elseif ($this->php_bin_sapi=='unknown') {
                    print "
******************************************************************************
WARNING! We found php.exe under $this->php_bin, it uses an $this->php_bin_sapi SAPI.
PEAR commandline tool has NOT been tested with it.
If you have a CLI (or CGI) php.exe available, we strongly recommend using it.

Press Enter to continue...";
                    $tmp = trim(fgets($this->tty, 1024));
                }
                echo "php.exe (sapi: $this->php_bin_sapi) found.\n\n";
                return $this->validPHPBin = true;
            } else {
                echo "**ERROR**: not a folder, or no php.exe found in this folder.
Press Enter to continue...";
                $tmp = trim(fgets($this->tty, 1024));
                return $this->validPHPBin = false;
            }
        }
    }

    /**
     * Create a vbs script to browse the getfolder dialog, called
     * by cscript, if it's available.
     * $label is the label text in the header of the dialog box
     *
     * TODO:
     * - Do not show Control panel
     * - Replace WSH with calls to w32 as soon as callbacks work
     * @author Pierrre-Alain Joye
     */
    function win32BrowseForFolder($label)
    {
        $wsh_browserfolder = 'Option Explicit
Dim ArgObj, var1, var2, sa, sFld
Set ArgObj = WScript.Arguments
Const BIF_EDITBOX = &H10
Const BIF_NEWDIALOGSTYLE = &H40
Const BIF_RETURNONLYFSDIRS   = &H0001
Const BIF_DONTGOBELOWDOMAIN  = &H0002
Const BIF_STATUSTEXT         = &H0004
Const BIF_RETURNFSANCESTORS  = &H0008
Const BIF_VALIDATE           = &H0020
Const BIF_BROWSEFORCOMPUTER  = &H1000
Const BIF_BROWSEFORPRINTER   = &H2000
Const BIF_BROWSEINCLUDEFILES = &H4000
Const OFN_LONGNAMES = &H200000
Const OFN_NOLONGNAMES = &H40000
Const ssfDRIVES = &H11
Const ssfNETWORK = &H12
Set sa = CreateObject("Shell.Application")
var1=ArgObj(0)
Set sFld = sa.BrowseForFolder(0, var1, BIF_EDITBOX + BIF_VALIDATE + BIF_BROWSEINCLUDEFILES + BIF_RETURNFSANCESTORS+BIF_NEWDIALOGSTYLE , ssfDRIVES )
if not sFld is nothing Then
    if not left(sFld.items.item.path,1)=":" Then
        WScript.Echo sFld.items.item.path
    Else
        WScript.Echo "invalid"
    End If
Else
    WScript.Echo "cancel"
End If
';
        if (!$this->cscript) {
            $this->cscript = $this->ptmp . DIRECTORY_SEPARATOR . "bf.vbs";
            // TODO: use file_put_contents()
            $fh = fopen($this->cscript, "wb+");
            fwrite($fh, $wsh_browserfolder, strlen($wsh_browserfolder));
            fclose($fh);
        }

        exec('cscript ' . escapeshellarg($this->cscript) . ' "' . escapeshellarg($label) . '" //noLogo', $arPath);
        if (!count($arPath) || $arPath[0]=='' || $arPath[0]=='cancel') {
            return '';
        } elseif ($arPath[0]=='invalid') {
            echo "Invalid Path.\n";
            return '';
        }

        return $arPath[0];
    }

    function displayPreamble()
    {
        if (OS_WINDOWS) {
            /*
             * Checks PHP SAPI version under windows/CLI
             */
            if ($this->php_bin == '') {
                print "
We do not find any php.exe, please select the php.exe folder (CLI is
recommended, usually in c:\php\cli\php.exe)
";
                $this->validPHPBin = false;
            } elseif (strlen($this->php_bin)) {
                $this->php_bin_sapi = $this->win32DetectPHPSAPI();
                $this->validPHPBin = true;
                switch ($this->php_bin_sapi) {
                    case 'cli':
                    break;
                    case 'cgi':
                    case 'cgi-fcgi':
                        print "
*NOTICE*
We found php.exe under $this->php_bin, it uses a $this->php_bin_sapi SAPI. PEAR commandline
tool works well with it, if you have a CLI php.exe available, we
recommend using it.
";
                    break;
                    default:
                        print "
*WARNING*
We found php.exe under $this->php_bin, it uses an unknown SAPI. PEAR commandline
tool has not been tested with it, if you have a CLI (or CGI) php.exe available,
we strongly recommend using it.

";
                    break;
                }
            }
        }
    }

    function finishInstall()
    {
        $sep = OS_WINDOWS ? ';' : ':';
        $include_path = explode($sep, ini_get('include_path'));
        if (OS_WINDOWS) {
            $found = false;
            $t = strtolower($this->php_dir);
            foreach ($include_path as $path) {
                if ($t == strtolower($path)) {
                    $found = true;
                    break;
                }
            }
        } else {
            $found = in_array($this->php_dir, $include_path);
        }
        if (!$found) {
            print "
******************************************************************************
WARNING!  The include_path defined in the currently used php.ini does not
contain the PEAR PHP directory you just specified:
<$this->php_dir>
If the specified directory is also not in the include_path used by
your scripts, you will have problems getting any PEAR packages working.
";

            if ($php_ini = $this->getPhpiniPath()) {
                print "\n\nWould you like to alter php.ini <$php_ini>? [Y/n] : ";
                $alter_phpini = !stristr(fgets($this->tty, 1024), "n");
                if ($alter_phpini) {
                    $this->alterPhpIni($php_ini);
                } else {
                    if (OS_WINDOWS) {
                        print "
Please look over your php.ini file to make sure
$this->php_dir is in your include_path.";
                    } else {
                        print "
I will add a workaround for this in the 'pear' command to make sure
the installer works, but please look over your php.ini or Apache
configuration to make sure $this->php_dir is in your include_path.
";
                    }
                }
            }

        print "
Current include path           : ".ini_get('include_path')."
Configured directory           : $this->php_dir
Currently used php.ini (guess) : $php_ini
";

            print "Press Enter to continue: ";
            fgets($this->tty, 1024);
        }

        $pear_cmd = $this->bin_dir . DIRECTORY_SEPARATOR . 'pear';
        $pear_cmd = OS_WINDOWS ? strtolower($pear_cmd).'.bat' : $pear_cmd;

        // check that the installed pear and the one in the path are the same (if any)
        $pear_old = System::which(OS_WINDOWS ? 'pear.bat' : 'pear', $this->bin_dir);
        if ($pear_old && ($pear_old != $pear_cmd)) {
            // check if it is a link or symlink
            $islink = OS_WINDOWS ? false : is_link($pear_old) ;
            if ($islink && readlink($pear_old) != $pear_cmd) {
                print "\n** WARNING! The link $pear_old does not point to the " .
                      "installed $pear_cmd\n";
            } elseif (!$this->localInstall && is_writable($pear_old) && !is_dir($pear_old)) {
                rename($pear_old, "{$pear_old}_old");
                print "\n** WARNING! Backed up old pear to {$pear_old}_old\n";
            } else {
                print "\n** WARNING! Old version found at $pear_old, please remove it or ".
                      "be sure to use the new $pear_cmd command\n";
            }
        }

        print "\nThe 'pear' command is now at your service at $pear_cmd\n";

        // Alert the user if the pear cmd is not in PATH
        $old_dir = $pear_old ? dirname($pear_old) : false;
        if (!$this->which('pear', $old_dir)) {
            print "
** The 'pear' command is not currently in your PATH, so you need to
** use '$pear_cmd' until you have added
** '$this->bin_dir' to your PATH environment variable.

";

        print "Run it without parameters to see the available actions, try 'pear list'
to see what packages are installed, or 'pear help' for help.

For more information about PEAR, see:

  http://pear.php.net/faq.php
  http://pear.php.net/manual/

Thanks for using go-pear!

";
        }

        if (OS_WINDOWS && !$this->localInstall) {
            $this->win32CreateRegEnv();
        }
    }

    /**
     * System::which() does not allow path exclusion
     */
    function which($program, $dont_search_in = false)
    {
        if (OS_WINDOWS) {
            if ($_path = $this->safeGetEnv('Path')) {
                $dirs = explode(';', $_path);
            } else {
                $dirs = explode(';', $this->safeGetEnv('PATH'));
            }
            foreach ($dirs as $i => $dir) {
                $dirs[$i] = strtolower(realpath($dir));
            }
            if ($dont_search_in) {
                $dont_search_in = strtolower(realpath($dont_search_in));
            }
            if ($dont_search_in &&
                ($key = array_search($dont_search_in, $dirs)) !== false)
            {
                unset($dirs[$key]);
            }

            foreach ($dirs as $dir) {
                $dir = str_replace('\\\\', '\\', $dir);
                if (!strlen($dir)) {
                    continue;
                }
                if ($dir{strlen($dir) - 1} != '\\') {
                    $dir .= '\\';
                }
                $tmp = $dir . $program;
                $info = pathinfo($tmp);
                if (isset($info['extension']) && in_array(strtolower($info['extension']),
                      array('exe', 'com', 'bat', 'cmd'))) {
                    if (file_exists($tmp)) {
                        return strtolower($tmp);
                    }
                } elseif (file_exists($ret = $tmp . '.exe') ||
                    file_exists($ret = $tmp . '.com') ||
                    file_exists($ret = $tmp . '.bat') ||
                    file_exists($ret = $tmp . '.cmd')) {
                    return strtolower($ret);
                }
            }
        } else {
            $dirs = explode(':', $this->safeGetEnv('PATH'));
            if ($dont_search_in &&
                ($key = array_search($dont_search_in, $dirs)) !== false)
            {
                unset($dirs[$key]);
            }
            foreach ($dirs as $dir) {
                if (is_executable("$dir/$program")) {
                    return "$dir/$program";
                }
            }
        }
        return false;
    }

    /**
     * Not optimized, but seems to work, if some nice
     * peardev will test it? :)
     *
     * @author Pierre-Alain Joye <paj@pearfr.org>
     */
    function alterPhpIni($pathIni='')
    {
        $foundAt = array();
        $iniSep = OS_WINDOWS ? ';' : ':';

        if ($pathIni=='') {
            $pathIni =  $this->getPhpiniPath();
        }

        $arrayIni = file($pathIni);
        $i=0;
        $found=0;

        // Looks for each active include_path directives
        foreach ($arrayIni as $iniLine) {
            $iniLine = trim($iniLine);
            $iniLine = str_replace(array("\n", "\r"), array('', ''), $iniLine);
            if (preg_match("/^\s*include_path/", $iniLine)) {
                $foundAt[] = $i;
                $found++;
            }
            $i++;
        }

        if ($found) {
            $includeLine = $arrayIni[$foundAt[0]];
            list(, $currentPath) = explode('=', $includeLine);

            $currentPath = trim($currentPath);
            if (substr($currentPath,0,1) == '"') {
                $currentPath = substr($currentPath, 1, strlen($currentPath) - 2);
            }

            $arrayPath = explode($iniSep, $currentPath);
            $newPath = array();
            if ($arrayPath[0]=='.') {
                $newPath[0] = '.';
                $newPath[1] = $this->php_dir;
                array_shift($arrayPath);
            } else {
                $newPath[0] = $this->php_dir;
            }

            foreach ($arrayPath as $path) {
                $newPath[]= $path;
            }
        } else {
            $newPath = array();
            $newPath[0] = '.';
            $newPath[1] = $this->php_dir;
            $foundAt[] = count($arrayIni); // add a new line if none is present
        }
        $nl = OS_WINDOWS ? "\r\n" : "\n";
        $includepath = 'include_path="' . implode($iniSep,$newPath) . '"';
        $newInclude = "$nl$nl;***** Added by go-pear$nl" .
                       $includepath .
                       $nl . ";*****" .
                       $nl . $nl;

        $arrayIni[$foundAt[0]] = $newInclude;

        for ($i=1; $i<$found; $i++) {
            $arrayIni[$foundAt[$i]]=';' . trim($arrayIni[$foundAt[$i]]);
        }

        $newIni = implode("", $arrayIni);
        if (!($fh = @fopen($pathIni, "wb+"))) {
            $prefixIni = $this->prefix . DIRECTORY_SEPARATOR . "php.ini-gopear";
            $fh = fopen($prefixIni, "wb+");
            if (!$fh) {
                echo "
******************************************************************************
WARNING: Cannot write to $pathIni nor in $this->prefix/php.ini-gopear. Please
modify manually your php.ini by adding:

$includepath

";
                return false;
            } else {
                fwrite($fh, $newIni, strlen($newIni));
                fclose($fh);
                echo "
******************************************************************************
WARNING: Cannot write to $pathIni, but php.ini was successfully created
at <$this->prefix/php.ini-gopear>. Please replace the file <$pathIni> with
<$prefixIni> or modify your php.ini by adding:

$includepath

";

            }
        } else {
            fwrite($fh, $newIni, strlen($newIni));
            fclose($fh);
            echo "
php.ini <$pathIni> include_path updated.
";
        }
        return true;
    }

    /**
     * Generates a registry addOn for Win32 platform
     * This addon set PEAR environment variables
     * @author Pierrre-Alain Joye
     */
    function win32CreateRegEnv()
    {
        $nl = "\r\n";
        $reg ='REGEDIT4'.$nl.
                '[HKEY_CURRENT_USER\Environment]'. $nl .
                '"PHP_PEAR_SYSCONF_DIR"="' . addslashes($this->prefix) . '"' . $nl .
                '"PHP_PEAR_INSTALL_DIR"="' . addslashes($this->php_dir) . '"' . $nl .
                '"PHP_PEAR_DOC_DIR"="' . addslashes($this->doc_dir) . '"' . $nl .
                '"PHP_PEAR_BIN_DIR"="' . addslashes($this->bin_dir) . '"' . $nl .
                '"PHP_PEAR_DATA_DIR"="' . addslashes($this->data_dir) . '"' . $nl .
                '"PHP_PEAR_PHP_BIN"="' . addslashes($this->php_bin) . '"' . $nl .
                '"PHP_PEAR_TEST_DIR"="' . addslashes($this->test_dir) . '"' . $nl;

        $fh = fopen($this->prefix . DIRECTORY_SEPARATOR . 'PEAR_ENV.reg', 'wb');
        if($fh){
            fwrite($fh, $reg, strlen($reg));
            fclose($fh);
            echo "

* WINDOWS ENVIRONMENT VARIABLES *
For convenience, a REG file is available under {$this->prefix}PEAR_ENV.reg .
This file creates ENV variables for the current user.

Double-click this file to add it to the current user registry.

";
        }
    }

    function displayHTMLProgress()
    {
    }
}
?>
