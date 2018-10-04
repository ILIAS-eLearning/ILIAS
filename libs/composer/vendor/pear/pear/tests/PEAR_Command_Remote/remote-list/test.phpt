--TEST--
remote-list command - REST 1.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pf = new PEAR_PackageFile_v1;
$pf->setConfig($config);
$pf->setPackage('Archive_Zip');
$pf->setSummary('foo');
$pf->setDate(date('Y-m-d'));
$pf->setDescription('foo');
$pf->setVersion('1.0.0');
$pf->setState('stable');
$pf->setLicense('PHP License');
$pf->setNotes('foo');
$pf->addMaintainer('lead', 'cellog', 'Greg', 'cellog@php.net');
$pf->addFile('', 'foo.dat', array('role' => 'data'));
$pf->validate();
$phpunit->assertNoErrors('setup');
$reg->addPackage2($pf);


$pearweb->addRESTConfig("http://pear.php.net/rest/p/packages.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pear.php.net</c>
 <p>Archive_Tar</p>
 <p>Archive_Zip</p>
 <p>AsteriskManager</p>
 <p>Auth</p>
 <p>Auth_HTTP</p>
 <p>Auth_PrefManager</p>
 <p>Auth_PrefManager2</p>
 <p>Auth_RADIUS</p>
 <p>Auth_SASL</p>
 <p>Benchmark</p>
 <p>Cache</p>
 <p>Cache_Lite</p>
 <p>Calendar</p>
 <p>CodeGen</p>
 <p>CodeGen_MySQL</p>
 <p>CodeGen_MySQL_Plugin</p>
 <p>CodeGen_MySQL_UDF</p>
 <p>CodeGen_PECL</p>
 <p>Config</p>
 <p>Console_Color</p>
 <p>Console_CommandLine</p>
 <p>Console_Getargs</p>
 <p>Console_Getopt</p>
 <p>Console_GetoptPlus</p>
 <p>Console_ProgressBar</p>
 <p>Console_Table</p>
 <p>Contact_AddressBook</p>
 <p>Contact_Vcard_Build</p>
 <p>Contact_Vcard_Parse</p>
 <p>Crypt_Blowfish</p>
 <p>Crypt_CBC</p>
 <p>Crypt_CHAP</p>
 <p>Crypt_DiffieHellman</p>
 <p>Crypt_GPG</p>
 <p>Crypt_HMAC</p>
 <p>Crypt_HMAC2</p>
 <p>Crypt_MicroID</p>
 <p>Crypt_RC4</p>
 <p>Crypt_RSA</p>
 <p>Crypt_Xtea</p>
 <p>Crypt_XXTEA</p>
 <p>Date</p>
 <p>Date_Holidays</p>
 <p>Date_Holidays_Austria</p>
 <p>Date_Holidays_Brazil</p>
 <p>Date_Holidays_Denmark</p>
 <p>Date_Holidays_Discordian</p>
 <p>Date_Holidays_EnglandWales</p>
 <p>Date_Holidays_Germany</p>
 <p>Date_Holidays_Iceland</p>
 <p>Date_Holidays_Ireland</p>
 <p>Date_Holidays_Italy</p>
 <p>Date_Holidays_Japan</p>
 <p>Date_Holidays_Netherlands</p>
 <p>Date_Holidays_Norway</p>
 <p>Date_Holidays_PHPdotNet</p>
 <p>Date_Holidays_Romania</p>
 <p>Date_Holidays_Slovenia</p>
 <p>Date_Holidays_Sweden</p>
 <p>Date_Holidays_Ukraine</p>
 <p>Date_Holidays_UNO</p>
 <p>Date_Holidays_USA</p>
 <p>DB</p>
 <p>DBA</p>
 <p>DBA_Relational</p>
 <p>DB_ado</p>
 <p>DB_DataObject</p>
 <p>DB_DataObject_FormBuilder</p>
 <p>DB_ldap</p>
 <p>DB_ldap2</p>
 <p>DB_NestedSet</p>
 <p>DB_NestedSet2</p>
 <p>DB_odbtp</p>
 <p>DB_Pager</p>
 <p>DB_QueryTool</p>
 <p>DB_Sqlite_Tools</p>
 <p>DB_Table</p>
 <p>Event_Dispatcher</p>
 <p>Event_SignalEmitter</p>
 <p>File</p>
 <p>File_Archive</p>
 <p>File_Bittorrent</p>
 <p>File_Bittorrent2</p>
 <p>File_Cabinet</p>
 <p>File_CSV</p>
 <p>File_CSV_DataSource</p>
 <p>File_DeliciousLibrary</p>
 <p>File_DICOM</p>
 <p>File_DNS</p>
 <p>File_Find</p>
 <p>File_Fortune</p>
 <p>File_Fstab</p>
 <p>File_Gettext</p>
 <p>File_HtAccess</p>
 <p>File_IMC</p>
 <p>File_Infopath</p>
 <p>File_MARC</p>
 <p>File_Mogile</p>
 <p>File_Ogg</p>
 <p>File_Passwd</p>
 <p>File_PDF</p>
 <p>File_SearchReplace</p>
 <p>File_Sitemap</p>
 <p>File_SMBPasswd</p>
 <p>File_Util</p>
 <p>File_XSPF</p>
 <p>FSM</p>
 <p>Games_Chess</p>
 <p>Genealogy_Gedcom</p>
 <p>Gtk2_EntryDialog</p>
 <p>Gtk2_ExceptionDump</p>
 <p>Gtk2_FileDrop</p>
 <p>Gtk2_IndexedComboBox</p>
 <p>Gtk2_PHPConfig</p>
 <p>Gtk2_ScrollingLabel</p>
 <p>Gtk2_VarDump</p>
 <p>Gtk_FileDrop</p>
 <p>Gtk_MDB_Designer</p>
 <p>Gtk_ScrollingLabel</p>
 <p>Gtk_Styled</p>
 <p>Gtk_VarDump</p>
 <p>HTML_AJAX</p>
 <p>HTML_BBCodeParser</p>
 <p>HTML_Common</p>
 <p>HTML_Common2</p>
 <p>HTML_Crypt</p>
 <p>HTML_CSS</p>
 <p>HTML_Entities</p>
 <p>HTML_Form</p>
 <p>HTML_Javascript</p>
 <p>HTML_Menu</p>
 <p>HTML_Page</p>
 <p>HTML_Page2</p>
 <p>HTML_Progress</p>
 <p>HTML_Progress2</p>
 <p>HTML_QuickForm</p>
 <p>HTML_QuickForm2</p>
 <p>HTML_QuickForm_advmultiselect</p>
 <p>HTML_QuickForm_altselect</p>
 <p>HTML_QuickForm_CAPTCHA</p>
 <p>HTML_QuickForm_Controller</p>
 <p>HTML_QuickForm_DHTMLRulesTableless</p>
 <p>HTML_QuickForm_ElementGrid</p>
 <p>HTML_QuickForm_Livesearch</p>
 <p>HTML_QuickForm_Renderer_Tableless</p>
 <p>HTML_QuickForm_Rule_Spelling</p>
 <p>HTML_QuickForm_SelectFilter</p>
 <p>HTML_Safe</p>
 <p>HTML_Select</p>
 <p>HTML_Select_Common</p>
 <p>HTML_Table</p>
 <p>HTML_Table_Matrix</p>
 <p>HTML_TagCloud</p>
 <p>HTML_Template_Flexy</p>
 <p>HTML_Template_IT</p>
 <p>HTML_Template_PHPLIB</p>
 <p>HTML_Template_Sigma</p>
 <p>HTML_Template_Xipe</p>
 <p>HTML_TreeMenu</p>
 <p>HTTP</p>
 <p>HTTP_Client</p>
 <p>HTTP_Download</p>
 <p>HTTP_FloodControl</p>
 <p>HTTP_Header</p>
 <p>HTTP_Request</p>
 <p>HTTP_Request2</p>
 <p>HTTP_Server</p>
 <p>HTTP_Session</p>
 <p>HTTP_Session2</p>
 <p>HTTP_SessionServer</p>
 <p>HTTP_Upload</p>
 <p>HTTP_WebDAV_Client</p>
 <p>HTTP_WebDAV_Server</p>
 <p>I18N</p>
 <p>I18Nv2</p>
 <p>I18N_UnicodeNormalizer</p>
 <p>I18N_UnicodeString</p>
 <p>Image_3D</p>
 <p>Image_Barcode</p>
 <p>Image_Canvas</p>
 <p>Image_Color</p>
 <p>Image_Color2</p>
 <p>Image_GIS</p>
 <p>Image_Graph</p>
 <p>Image_GraphViz</p>
 <p>Image_IPTC</p>
 <p>Image_JpegMarkerReader</p>
 <p>Image_JpegXmpReader</p>
 <p>Image_MonoBMP</p>
 <p>Image_Puzzle</p>
 <p>Image_Remote</p>
 <p>Image_Text</p>
 <p>Image_Tools</p>
 <p>Image_Transform</p>
 <p>Image_WBMP</p>
 <p>Image_XBM</p>
 <p>Inline_C</p>
 <p>LiveUser</p>
 <p>LiveUser_Admin</p>
 <p>Log</p>
 <p>Mail</p>
 <p>Mail_IMAP</p>
 <p>Mail_IMAPv2</p>
 <p>Mail_Mbox</p>
 <p>Mail_Mime</p>
 <p>Mail_mimeDecode</p>
 <p>Mail_Queue</p>
 <p>Math_Basex</p>
 <p>Math_BigInteger</p>
 <p>Math_BinaryUtils</p>
 <p>Math_Combinatorics</p>
 <p>Math_Complex</p>
 <p>Math_Derivative</p>
 <p>Math_Fibonacci</p>
 <p>Math_Finance</p>
 <p>Math_Fraction</p>
 <p>Math_Histogram</p>
 <p>Math_Integer</p>
 <p>Math_Matrix</p>
 <p>Math_Numerical_RootFinding</p>
 <p>Math_Polynomial</p>
 <p>Math_Quaternion</p>
 <p>Math_RPN</p>
 <p>Math_Stats</p>
 <p>Math_TrigOp</p>
 <p>Math_Vector</p>
 <p>MDB</p>
 <p>MDB2</p>
 <p>MDB2_Driver_fbsql</p>
 <p>MDB2_Driver_ibase</p>
 <p>MDB2_Driver_mssql</p>
 <p>MDB2_Driver_mysql</p>
 <p>MDB2_Driver_mysqli</p>
 <p>MDB2_Driver_oci8</p>
 <p>MDB2_Driver_pgsql</p>
 <p>MDB2_Driver_querysim</p>
 <p>MDB2_Driver_sqlite</p>
 <p>MDB2_Schema</p>
 <p>MDB2_TableBrowser</p>
 <p>MDB_QueryTool</p>
 <p>Message</p>
 <p>MIME_Type</p>
 <p>MP3_Id</p>
 <p>MP3_IDv2</p>
 <p>MP3_Playlist</p>
 <p>Net_CDDB</p>
 <p>Net_CheckIP</p>
 <p>Net_CheckIP2</p>
 <p>Net_Curl</p>
 <p>Net_Cyrus</p>
 <p>Net_Dict</p>
 <p>Net_Dig</p>
 <p>Net_DIME</p>
 <p>Net_DNS</p>
 <p>Net_DNSBL</p>
 <p>Net_Finger</p>
 <p>Net_FTP</p>
 <p>Net_FTP2</p>
 <p>Net_GameServerQuery</p>
 <p>Net_Gearman</p>
 <p>Net_Geo</p>
 <p>Net_GeoIP</p>
 <p>Net_Growl</p>
 <p>Net_HL7</p>
 <p>Net_Ident</p>
 <p>Net_IDNA</p>
 <p>Net_IMAP</p>
 <p>Net_IPv4</p>
 <p>Net_IPv6</p>
 <p>Net_IRC</p>
 <p>Net_LDAP</p>
 <p>Net_LDAP2</p>
 <p>Net_LMTP</p>
 <p>Net_MAC</p>
 <p>Net_Monitor</p>
 <p>Net_MPD</p>
 <p>Net_Nmap</p>
 <p>Net_NNTP</p>
 <p>Net_Ping</p>
 <p>Net_POP3</p>
 <p>Net_Portscan</p>
 <p>Net_Server</p>
 <p>Net_Sieve</p>
 <p>Net_SmartIRC</p>
 <p>Net_SMPP</p>
 <p>Net_SMPP_Client</p>
 <p>Net_SMS</p>
 <p>Net_SMTP</p>
 <p>Net_Socket</p>
 <p>Net_Traceroute</p>
 <p>Net_URL</p>
 <p>Net_URL2</p>
 <p>Net_URL_Mapper</p>
 <p>Net_UserAgent_Detect</p>
 <p>Net_UserAgent_Mobile</p>
 <p>Net_UserAgent_Mobile_GPS</p>
 <p>Net_Vpopmaild</p>
 <p>Net_Whois</p>
 <p>Net_Wifi</p>
 <p>Numbers_Roman</p>
 <p>Numbers_Words</p>
 <p>OLE</p>
 <p>OpenDocument</p>
 <p>Pager</p>
 <p>Pager_Sliding</p>
 <p>Payment_Clieop</p>
 <p>Payment_DTA</p>
 <p>Payment_PayPal_SOAP</p>
 <p>Payment_Process</p>
 <p>PEAR</p>
 <p>pearweb</p>
 <p>pearweb_channelxml</p>
 <p>pearweb_gopear</p>
 <p>pearweb_index</p>
 <p>pearweb_phars</p>
 <p>PEAR_Command_Packaging</p>
 <p>PEAR_Delegator</p>
 <p>PEAR_ErrorStack</p>
 <p>PEAR_Frontend_Gtk</p>
 <p>PEAR_Frontend_Gtk2</p>
 <p>PEAR_Frontend_Web</p>
 <p>PEAR_Info</p>
 <p>PEAR_PackageFileManager</p>
 <p>PEAR_PackageFileManager2</p>
 <p>PEAR_PackageFileManager_Cli</p>
 <p>PEAR_PackageFileManager_Frontend</p>
 <p>PEAR_PackageFileManager_Frontend_Web</p>
 <p>PEAR_PackageFileManager_GUI_Gtk</p>
 <p>PEAR_PackageFileManager_Plugins</p>
 <p>PEAR_PackageUpdate</p>
 <p>PEAR_PackageUpdate_Gtk2</p>
 <p>PEAR_PackageUpdate_Web</p>
 <p>PEAR_RemoteInstaller</p>
 <p>PEAR_Size</p>
 <p>PHPDoc</p>
 <p>PhpDocumentor</p>
 <p>PHPUnit</p>
 <p>PHPUnit2</p>
 <p>PHP_Annotation</p>
 <p>PHP_Archive</p>
 <p>PHP_ArrayOf</p>
 <p>PHP_Beautifier</p>
 <p>PHP_CodeSniffer</p>
 <p>PHP_Compat</p>
 <p>PHP_CompatInfo</p>
 <p>PHP_Debug</p>
 <p>PHP_DocBlockGenerator</p>
 <p>PHP_Fork</p>
 <p>PHP_FunctionCallTracer</p>
 <p>PHP_LexerGenerator</p>
 <p>PHP_Parser</p>
 <p>PHP_ParserGenerator</p>
 <p>PHP_Parser_DocblockParser</p>
 <p>PHP_Shell</p>
 <p>PHP_UML</p>
 <p>QA_Peardoc_Coverage</p>
 <p>RDF</p>
 <p>RDF_N3</p>
 <p>RDF_NTriple</p>
 <p>RDF_RDQL</p>
 <p>Science_Chemistry</p>
 <p>ScriptReorganizer</p>
 <p>Search_Mnogosearch</p>
 <p>Services_Akismet</p>
 <p>Services_Akismet2</p>
 <p>Services_Amazon</p>
 <p>Services_Amazon_S3</p>
 <p>Services_Amazon_SQS</p>
 <p>Services_Atlassian_Crowd</p>
 <p>Services_Blogging</p>
 <p>Services_Compete</p>
 <p>Services_Delicious</p>
 <p>Services_Digg</p>
 <p>Services_DynDNS</p>
 <p>Services_Ebay</p>
 <p>Services_ExchangeRates</p>
 <p>Services_Facebook</p>
 <p>Services_GeoNames</p>
 <p>Services_Google</p>
 <p>Services_Hatena</p>
 <p>Services_oEmbed</p>
 <p>Services_OpenSearch</p>
 <p>Services_Pingback</p>
 <p>Services_ProjectHoneyPot</p>
 <p>Services_SharedBook</p>
 <p>Services_Technorati</p>
 <p>Services_TinyURL</p>
 <p>Services_Trackback</p>
 <p>Services_TwitPic</p>
 <p>Services_Twitter</p>
 <p>Services_urlTea</p>
 <p>Services_W3C_CSSValidator</p>
 <p>Services_W3C_HTMLValidator</p>
 <p>Services_Weather</p>
 <p>Services_Webservice</p>
 <p>Services_Yadis</p>
 <p>Services_Yahoo</p>
 <p>Services_Yahoo_JP</p>
 <p>Services_YouTube</p>
 <p>SOAP</p>
 <p>SOAP_Interop</p>
 <p>Spreadsheet_Excel_Writer</p>
 <p>SQL_Parser</p>
 <p>Stream_SHM</p>
 <p>Stream_Var</p>
 <p>Structures_BibTex</p>
 <p>Structures_DataGrid</p>
 <p>Structures_DataGrid_DataSource_Array</p>
 <p>Structures_DataGrid_DataSource_CSV</p>
 <p>Structures_DataGrid_DataSource_DataObject</p>
 <p>Structures_DataGrid_DataSource_DB</p>
 <p>Structures_DataGrid_DataSource_DBQuery</p>
 <p>Structures_DataGrid_DataSource_DBTable</p>
 <p>Structures_DataGrid_DataSource_Excel</p>
 <p>Structures_DataGrid_DataSource_MDB2</p>
 <p>Structures_DataGrid_DataSource_PDO</p>
 <p>Structures_DataGrid_DataSource_RSS</p>
 <p>Structures_DataGrid_DataSource_XML</p>
 <p>Structures_DataGrid_Renderer_Console</p>
 <p>Structures_DataGrid_Renderer_CSV</p>
 <p>Structures_DataGrid_Renderer_Flexy</p>
 <p>Structures_DataGrid_Renderer_HTMLSortForm</p>
 <p>Structures_DataGrid_Renderer_HTMLTable</p>
 <p>Structures_DataGrid_Renderer_Pager</p>
 <p>Structures_DataGrid_Renderer_Smarty</p>
 <p>Structures_DataGrid_Renderer_XLS</p>
 <p>Structures_DataGrid_Renderer_XML</p>
 <p>Structures_DataGrid_Renderer_XUL</p>
 <p>Structures_Form</p>
 <p>Structures_Form_Gtk2</p>
 <p>Structures_Graph</p>
 <p>Structures_LinkedList</p>
 <p>System_Command</p>
 <p>System_Daemon</p>
 <p>System_Folders</p>
 <p>System_Mount</p>
 <p>System_ProcWatch</p>
 <p>System_SharedMemory</p>
 <p>System_Socket</p>
 <p>System_WinDrives</p>
 <p>Testing_DocTest</p>
 <p>Testing_FIT</p>
 <p>Testing_Selenium</p>
 <p>Text_CAPTCHA</p>
 <p>Text_CAPTCHA_Numeral</p>
 <p>Text_Diff</p>
 <p>Text_Figlet</p>
 <p>Text_Highlighter</p>
 <p>Text_Huffman</p>
 <p>Text_LanguageDetect</p>
 <p>Text_Password</p>
 <p>Text_PathNavigator</p>
 <p>Text_Spell_Audio</p>
 <p>Text_Statistics</p>
 <p>Text_TeXHyphen</p>
 <p>Text_Wiki</p>
 <p>Text_Wiki_BBCode</p>
 <p>Text_Wiki_Cowiki</p>
 <p>Text_Wiki_Creole</p>
 <p>Text_Wiki_Doku</p>
 <p>Text_Wiki_Mediawiki</p>
 <p>Text_Wiki_Tiki</p>
 <p>Translation</p>
 <p>Translation2</p>
 <p>Tree</p>
 <p>UDDI</p>
 <p>URI_Template</p>
 <p>Validate</p>
 <p>Validate_AR</p>
 <p>Validate_AT</p>
 <p>Validate_AU</p>
 <p>Validate_BE</p>
 <p>Validate_CA</p>
 <p>Validate_CH</p>
 <p>Validate_DE</p>
 <p>Validate_DK</p>
 <p>Validate_ES</p>
 <p>Validate_FI</p>
 <p>Validate_Finance</p>
 <p>Validate_Finance_CreditCard</p>
 <p>Validate_FR</p>
 <p>Validate_HU</p>
 <p>Validate_IE</p>
 <p>Validate_IN</p>
 <p>Validate_IS</p>
 <p>Validate_ISPN</p>
 <p>Validate_IT</p>
 <p>Validate_LV</p>
 <p>Validate_NL</p>
 <p>Validate_NZ</p>
 <p>Validate_PL</p>
 <p>Validate_ptBR</p>
 <p>Validate_RU</p>
 <p>Validate_UK</p>
 <p>Validate_US</p>
 <p>Validate_ZA</p>
 <p>Var_Dump</p>
 <p>VersionControl_SVN</p>
 <p>VFS</p>
 <p>XML_Beautifier</p>
 <p>XML_CSSML</p>
 <p>XML_DB_eXist</p>
 <p>XML_DTD</p>
 <p>XML_FastCreate</p>
 <p>XML_Feed_Parser</p>
 <p>XML_fo2pdf</p>
 <p>XML_FOAF</p>
 <p>XML_GRDDL</p>
 <p>XML_HTMLSax</p>
 <p>XML_HTMLSax3</p>
 <p>XML_image2svg</p>
 <p>XML_Indexing</p>
 <p>XML_MXML</p>
 <p>XML_NITF</p>
 <p>XML_Parser</p>
 <p>XML_Query2XML</p>
 <p>XML_RDDL</p>
 <p>XML_RPC</p>
 <p>XML_RPC2</p>
 <p>XML_RSS</p>
 <p>XML_SaxFilters</p>
 <p>XML_Serializer</p>
 <p>XML_sql2xml</p>
 <p>XML_Statistics</p>
 <p>XML_SVG</p>
 <p>XML_svg2image</p>
 <p>XML_Transformer</p>
 <p>XML_Tree</p>
 <p>XML_Util</p>
 <p>XML_Wddx</p>
 <p>XML_XPath</p>
 <p>XML_XPath2</p>
 <p>XML_XSLT_Wrapper</p>
 <p>XML_XUL</p>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/stable.txt", '1.3.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_zip/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/asteriskmanager/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth/stable.txt", '1.6.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_http/stable.txt", '2.1.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_prefmanager/stable.txt", '1.2.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_prefmanager2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_radius/stable.txt", '1.0.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_sasl/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/benchmark/stable.txt", '1.2.7', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/cache/stable.txt", '1.5.5', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/cache_lite/stable.txt", '1.7.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/calendar/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen/stable.txt", '1.0.5', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen_mysql/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen_mysql_plugin/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen_mysql_udf/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen_pecl/stable.txt", '1.1.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/config/stable.txt", '1.10.11', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_color/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_commandline/stable.txt", '1.0.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getargs/stable.txt", '1.3.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/stable.txt", '1.2.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getoptplus/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_progressbar/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_table/stable.txt", '1.1.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/contact_addressbook/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/contact_vcard_build/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/contact_vcard_parse/stable.txt", '1.31.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_blowfish/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_cbc/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_chap/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_diffiehellman/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_gpg/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_hmac/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_hmac2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_microid/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_rc4/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_rsa/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_xtea/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_xxtea/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date/stable.txt", '1.4.7', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_austria/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_brazil/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_denmark/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_discordian/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_englandwales/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_germany/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_iceland/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_ireland/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_italy/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_japan/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_netherlands/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_norway/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_phpdotnet/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_romania/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_slovenia/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_sweden/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_ukraine/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_uno/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays_usa/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db/stable.txt", '1.7.13', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/dba/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/dba_relational/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_ado/stable.txt", '1.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_dataobject/stable.txt", '1.8.8', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_dataobject_formbuilder/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_ldap/stable.txt", '1.2.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_ldap2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_nestedset/stable.txt", '1.2.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_nestedset2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_odbtp/stable.txt", '1.0.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_pager/stable.txt", '0.7', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_querytool/stable.txt", '1.1.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_sqlite_tools/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_table/stable.txt", '1.5.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/event_dispatcher/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/event_signalemitter/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file/stable.txt", '1.3.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_archive/stable.txt", '1.5.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_bittorrent/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_bittorrent2/stable.txt", '1.2.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_cabinet/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_csv/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_csv_datasource/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_deliciouslibrary/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_dicom/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_dns/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_find/stable.txt", '1.3.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_fortune/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_fstab/stable.txt", '2.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_gettext/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_htaccess/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_imc/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_infopath/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_marc/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_mogile/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_ogg/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_passwd/stable.txt", '1.1.7', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_pdf/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_searchreplace/stable.txt", '1.1.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_sitemap/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_smbpasswd/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_util/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_xspf/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/fsm/stable.txt", '1.3.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/games_chess/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/genealogy_gedcom/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk2_entrydialog/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk2_exceptiondump/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk2_filedrop/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk2_indexedcombobox/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk2_phpconfig/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk2_scrollinglabel/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk2_vardump/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_filedrop/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_mdb_designer/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_scrollinglabel/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_styled/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_vardump/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_ajax/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_bbcodeparser/stable.txt", '1.2.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_common/stable.txt", '1.2.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_common2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_crypt/stable.txt", '1.3.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_css/stable.txt", '1.5.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_entities/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_form/stable.txt", '1.3.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_javascript/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_menu/stable.txt", '2.1.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_page/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_page2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_progress/stable.txt", '1.2.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_progress2/stable.txt", '2.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform/stable.txt", '3.2.10', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_advmultiselect/stable.txt", '1.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_altselect/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_captcha/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_controller/stable.txt", '1.0.9', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_dhtmlrulestableless/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_elementgrid/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_livesearch/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_renderer_tableless/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_rule_spelling/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_selectfilter/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_safe/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_select/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_select_common/stable.txt", '1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_table/stable.txt", '1.8.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_table_matrix/stable.txt", '1.0.9', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_tagcloud/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_flexy/stable.txt", '1.3.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_it/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_phplib/stable.txt", '1.4.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_sigma/stable.txt", '1.2.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_xipe/stable.txt", '1.7.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_treemenu/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http/stable.txt", '1.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_client/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_download/stable.txt", '1.1.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_floodcontrol/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_header/stable.txt", '1.2.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_request/stable.txt", '1.4.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_request2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_server/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_session/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_session2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_sessionserver/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_upload/stable.txt", '0.9.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_webdav_client/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_webdav_server/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18n/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18nv2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18n_unicodenormalizer/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18n_unicodestring/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_3d/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_barcode/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_canvas/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_color/stable.txt", '1.0.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_color2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_gis/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_graph/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_graphviz/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_iptc/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_jpegmarkerreader/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_jpegxmpreader/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_monobmp/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_puzzle/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_remote/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_text/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_tools/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_transform/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_wbmp/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_xbm/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/inline_c/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/liveuser/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/liveuser_admin/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/log/stable.txt", '1.11.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail/stable.txt", '1.1.14', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_imap/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_imapv2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_mbox/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_mime/stable.txt", '1.5.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_mimedecode/stable.txt", '1.5.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_queue/stable.txt", '1.2.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_basex/stable.txt", '0.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_biginteger/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_binaryutils/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_combinatorics/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_complex/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_derivative/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_fibonacci/stable.txt", '0.8', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_finance/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_fraction/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_histogram/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_integer/stable.txt", '0.8', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_matrix/stable.txt", '0.8.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_numerical_rootfinding/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_polynomial/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_quaternion/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_rpn/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_stats/stable.txt", '0.8.5', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_trigop/stable.txt", '1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_vector/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb/stable.txt", '1.3.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2/stable.txt", '2.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_fbsql/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_ibase/stable.txt", '1.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_mssql/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_mysql/stable.txt", '1.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_mysqli/stable.txt", '1.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_oci8/stable.txt", '1.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_pgsql/stable.txt", '1.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_querysim/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_sqlite/stable.txt", '1.4.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_schema/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_tablebrowser/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb_querytool/stable.txt", '1.2.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/message/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mime_type/stable.txt", '1.2.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mp3_id/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mp3_idv2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mp3_playlist/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_cddb/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_checkip/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_checkip2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_curl/stable.txt", '1.2.5', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_cyrus/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dict/stable.txt", '1.0.5', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dig/stable.txt", '0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dime/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dns/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dnsbl/stable.txt", '1.3.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_finger/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ftp/stable.txt", '1.3.7', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ftp2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_gameserverquery/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_gearman/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_geo/stable.txt", '1.0.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_geoip/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_growl/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_hl7/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ident/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_idna/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_imap/stable.txt", '1.0.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ipv4/stable.txt", '1.3.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ipv6/stable.txt", '1.0.5', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_irc/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ldap/stable.txt", '1.1.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ldap2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_lmtp/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_mac/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_monitor/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_mpd/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_nmap/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_nntp/stable.txt", '1.4.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ping/stable.txt", '2.4.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_pop3/stable.txt", '1.3.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_portscan/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_server/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_sieve/stable.txt", '1.1.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smartirc/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smpp/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smpp_client/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_sms/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smtp/stable.txt", '1.3.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_socket/stable.txt", '1.0.9', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_traceroute/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_url/stable.txt", '1.0.15', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_url2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_url_mapper/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_useragent_detect/stable.txt", '2.5.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_useragent_mobile/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_useragent_mobile_gps/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_vpopmaild/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_whois/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_wifi/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/numbers_roman/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/numbers_words/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/ole/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/opendocument/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pager/stable.txt", '2.4.7', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pager_sliding/stable.txt", '1.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_clieop/stable.txt", '0.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_dta/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_paypal_soap/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_process/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/stable.txt", '1.7.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pearweb/stable.txt", '1.17.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pearweb_channelxml/stable.txt", '1.13.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pearweb_gopear/stable.txt", '1.1.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pearweb_index/stable.txt", '1.16.13', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pearweb_phars/stable.txt", '1.4.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_command_packaging/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_delegator/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_errorstack/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk2/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_web/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_info/stable.txt", '1.9.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager/stable.txt", '1.6.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager_cli/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager_frontend/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager_frontend_web/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager_gui_gtk/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager_plugins/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packageupdate/stable.txt", '1.0.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packageupdate_gtk2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packageupdate_web/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_remoteinstaller/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_size/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpdoc/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpdocumentor/stable.txt", '1.4.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpunit/stable.txt", '1.3.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpunit2/stable.txt", '2.3.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_annotation/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_archive/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_arrayof/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_beautifier/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_codesniffer/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_compat/stable.txt", '1.5.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_compatinfo/stable.txt", '1.8.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_debug/stable.txt", '1.0.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_docblockgenerator/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_fork/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_functioncalltracer/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_lexergenerator/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_parser/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_parsergenerator/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_parser_docblockparser/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_shell/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_uml/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/qa_peardoc_coverage/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf_n3/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf_ntriple/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf_rdql/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/science_chemistry/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/scriptreorganizer/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/search_mnogosearch/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_akismet/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_akismet2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_amazon/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_amazon_s3/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_amazon_sqs/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_atlassian_crowd/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_blogging/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_compete/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_delicious/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_digg/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_dyndns/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_ebay/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_exchangerates/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_facebook/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_geonames/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_google/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_hatena/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_oembed/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_opensearch/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_pingback/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_projecthoneypot/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_sharedbook/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_technorati/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_tinyurl/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_trackback/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_twitpic/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_twitter/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_urltea/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_w3c_cssvalidator/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_w3c_htmlvalidator/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_weather/stable.txt", '1.4.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_webservice/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_yadis/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_yahoo/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_yahoo_jp/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_youtube/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/soap/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/soap_interop/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/spreadsheet_excel_writer/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/sql_parser/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/stream_shm/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/stream_var/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_bibtex/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_array/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_csv/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_dataobject/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_db/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_dbquery/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_dbtable/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_excel/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_mdb2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_pdo/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_rss/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_datasource_xml/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_console/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_csv/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_flexy/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_htmlsortform/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_htmltable/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_pager/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_smarty/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_xls/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_xml/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid_renderer_xul/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_form/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_form_gtk2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_graph/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_linkedlist/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_command/stable.txt", '1.0.6', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_daemon/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_folders/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_mount/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_procwatch/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_sharedmemory/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_socket/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_windrives/stable.txt", '1.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/testing_doctest/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/testing_fit/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/testing_selenium/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_captcha/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_captcha_numeral/stable.txt", '1.2.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_diff/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_figlet/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_highlighter/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_huffman/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_languagedetect/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_password/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_pathnavigator/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_spell_audio/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_statistics/stable.txt", '1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_texhyphen/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki/stable.txt", '1.2.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_bbcode/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_cowiki/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_creole/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_doku/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_mediawiki/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_tiki/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/translation/stable.txt", '1.2.6pl1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/translation2/stable.txt", '2.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/tree/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/uddi/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/uri_template/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_ar/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_at/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_au/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_be/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_ca/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_ch/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_de/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_dk/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_es/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_fi/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_finance/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_finance_creditcard/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_fr/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_hu/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_ie/stable.txt", '1.0.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_in/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_is/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_ispn/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_it/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_lv/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_nl/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_nz/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_pl/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_ptbr/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_ru/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_uk/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_us/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_za/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/var_dump/stable.txt", '1.0.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/versioncontrol_svn/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/vfs/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_beautifier/stable.txt", '1.2.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_cssml/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_db_exist/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_dtd/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_fastcreate/stable.txt", '1.0.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_feed_parser/stable.txt", '1.0.3', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_fo2pdf/stable.txt", '0.98', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_foaf/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_grddl/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_htmlsax/stable.txt", '2.1.2', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_htmlsax3/stable.txt", '3.0.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_image2svg/stable.txt", '0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_indexing/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_mxml/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_nitf/stable.txt", '1.1.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_parser/stable.txt", '1.3.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_query2xml/stable.txt", '1.7.0', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rddl/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/stable.txt", '1.5.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc2/stable.txt", '1.0.5', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rss/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_saxfilters/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_serializer/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_sql2xml/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_statistics/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_svg/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_svg2image/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_transformer/stable.txt", '1.1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_tree/stable.txt", '1.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_util/stable.txt", '1.2.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_wddx/stable.txt", '1.0.1', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_xpath/stable.txt", '1.2.4', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_xpath2/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_xslt_wrapper/stable.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_xul/stable.txt", false, false);

$reg = &$config->getRegistry();
$ch = new PEAR_ChannelFile;
$ch->setName('smoog');
$ch->setSummary('smoog');
$ch->setBaseURL('REST1.0', 'http://smoog/rest/');
$reg->addChannel($ch);

$pearweb->addRESTConfig('http://smoog/rest/p/packages.xml',
'<?xml version="1.0" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
 <c>smoog</c>
 <p>APC</p>
</a>',
'text/xml');

$pearweb->addRESTConfig('http://smoog/rest/r/apc/allreleases.xml',
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>APC</p>
 <c>smoog</c>
 <r>
  <v>2.0.4</v>
  <s>stable</s>
 </r>
</a>',
'text/xml');

$pearweb->addRESTConfig('http://smoog/rest/r/apc/2.0.4.xml',
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/apc">APC</p>
 <c>smoog</c>
 <v>2.0.4</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>rasmus</m>
 <s>Alternative PHP Cache</s>
 <d>APC is the Alternative PHP Cache. It was conceived of to provide a free, open, and robust framework for caching and optimizing PHP intermediate code.</d>
 <da>2005-04-17 18:40:51</da>
 <n>Release notes</n>
 <f>252733</f>
 <g>http://smoog/get/APC-2.0.4/g>
 <x xlink:href="package.2.0.4.xml"/>
</r>',
'text/xml');

$pearweb->addRESTConfig("http://smoog/rest/r/apc/stable.txt", '2.0.4', 'text/xml');

$ch->setName('empty');
$reg->addChannel($ch);

$e = $command->run('remote-list', array(), array());
$phpunit->assertNoErrors('pear.php.net');
//$phpunit->showall();

$log = $fakelog->getLog();
$phpunit->assertEquals(
array (
  0 =>
  array (
    0 => 'Retrieving data...0%',
    1 => true,
  ),
  1 =>
  array (
    0 => '.',
    1 => false,
  ),
  2 =>
  array (
    0 => '.',
    1 => false,
  ),
  3 =>
  array (
    0 => '.',
    1 => false,
  ),
  4 =>
  array (
    0 => '.',
    1 => false,
  ),
  5 =>
  array (
    0 => '50%',
    1 => false,
  ),
  6 =>
  array (
    0 => '.',
    1 => false,
  ),
  7 =>
  array (
    0 => '.',
    1 => false,
  ),
  8 =>
  array (
    0 => '.',
    1 => false,
  ),
  9 =>
  array (
    0 => '.',
    1 => false,
  ),
  10 =>
  array (
    'info' =>
    array (
      'caption' => 'Channel pear.php.net Available packages:',
      'border' => true,
      'headline' =>
      array (
        0 => 'Package',
        1 => 'Version',
      ),
      'channel' => 'pear.php.net',
      'data' =>
      array (
        0 =>
        array (
          0 => 'Archive_Tar',
          1 => '-n/a-',
        ),
        1 =>
        array (
          0 => 'Archive_Zip',
          1 => '-n/a-',
        ),
        2 =>
        array (
          0 => 'AsteriskManager',
          1 => '-n/a-',
        ),
        3 =>
        array (
          0 => 'Auth',
          1 => '-n/a-',
        ),
        4 =>
        array (
          0 => 'Auth_HTTP',
          1 => '-n/a-',
        ),
        5 =>
        array (
          0 => 'Auth_PrefManager',
          1 => '-n/a-',
        ),
        6 =>
        array (
          0 => 'Auth_PrefManager2',
          1 => '-n/a-',
        ),
        7 =>
        array (
          0 => 'Auth_RADIUS',
          1 => '-n/a-',
        ),
        8 =>
        array (
          0 => 'Auth_SASL',
          1 => '-n/a-',
        ),
        9 =>
        array (
          0 => 'Benchmark',
          1 => '-n/a-',
        ),
        10 =>
        array (
          0 => 'Cache',
          1 => '-n/a-',
        ),
        11 =>
        array (
          0 => 'Cache_Lite',
          1 => '-n/a-',
        ),
        12 =>
        array (
          0 => 'Calendar',
          1 => '-n/a-',
        ),
        13 =>
        array (
          0 => 'CodeGen',
          1 => '-n/a-',
        ),
        14 =>
        array (
          0 => 'CodeGen_MySQL',
          1 => '-n/a-',
        ),
        15 =>
        array (
          0 => 'CodeGen_MySQL_Plugin',
          1 => '-n/a-',
        ),
        16 =>
        array (
          0 => 'CodeGen_MySQL_UDF',
          1 => '-n/a-',
        ),
        17 =>
        array (
          0 => 'CodeGen_PECL',
          1 => '-n/a-',
        ),
        18 =>
        array (
          0 => 'Config',
          1 => '-n/a-',
        ),
        19 =>
        array (
          0 => 'Console_Color',
          1 => '-n/a-',
        ),
        20 =>
        array (
          0 => 'Console_CommandLine',
          1 => '-n/a-',
        ),
        21 =>
        array (
          0 => 'Console_Getargs',
          1 => '-n/a-',
        ),
        22 =>
        array (
          0 => 'Console_Getopt',
          1 => '-n/a-',
        ),
        23 =>
        array (
          0 => 'Console_GetoptPlus',
          1 => '-n/a-',
        ),
        24 =>
        array (
          0 => 'Console_ProgressBar',
          1 => '-n/a-',
        ),
        25 =>
        array (
          0 => 'Console_Table',
          1 => '-n/a-',
        ),
        26 =>
        array (
          0 => 'Contact_AddressBook',
          1 => '-n/a-',
        ),
        27 =>
        array (
          0 => 'Contact_Vcard_Build',
          1 => '-n/a-',
        ),
        28 =>
        array (
          0 => 'Contact_Vcard_Parse',
          1 => '-n/a-',
        ),
        29 =>
        array (
          0 => 'Crypt_Blowfish',
          1 => '-n/a-',
        ),
        30 =>
        array (
          0 => 'Crypt_CBC',
          1 => '-n/a-',
        ),
        31 =>
        array (
          0 => 'Crypt_CHAP',
          1 => '-n/a-',
        ),
        32 =>
        array (
          0 => 'Crypt_DiffieHellman',
          1 => '-n/a-',
        ),
        33 =>
        array (
          0 => 'Crypt_GPG',
          1 => '-n/a-',
        ),
        34 =>
        array (
          0 => 'Crypt_HMAC',
          1 => '-n/a-',
        ),
        35 =>
        array (
          0 => 'Crypt_HMAC2',
          1 => '-n/a-',
        ),
        36 =>
        array (
          0 => 'Crypt_MicroID',
          1 => '-n/a-',
        ),
        37 =>
        array (
          0 => 'Crypt_RC4',
          1 => '-n/a-',
        ),
        38 =>
        array (
          0 => 'Crypt_RSA',
          1 => '-n/a-',
        ),
        39 =>
        array (
          0 => 'Crypt_Xtea',
          1 => '-n/a-',
        ),
        40 =>
        array (
          0 => 'Crypt_XXTEA',
          1 => '-n/a-',
        ),
        41 =>
        array (
          0 => 'Date',
          1 => '-n/a-',
        ),
        42 =>
        array (
          0 => 'Date_Holidays',
          1 => '-n/a-',
        ),
        43 =>
        array (
          0 => 'Date_Holidays_Austria',
          1 => '-n/a-',
        ),
        44 =>
        array (
          0 => 'Date_Holidays_Brazil',
          1 => '-n/a-',
        ),
        45 =>
        array (
          0 => 'Date_Holidays_Denmark',
          1 => '-n/a-',
        ),
        46 =>
        array (
          0 => 'Date_Holidays_Discordian',
          1 => '-n/a-',
        ),
        47 =>
        array (
          0 => 'Date_Holidays_EnglandWales',
          1 => '-n/a-',
        ),
        48 =>
        array (
          0 => 'Date_Holidays_Germany',
          1 => '-n/a-',
        ),
        49 =>
        array (
          0 => 'Date_Holidays_Iceland',
          1 => '-n/a-',
        ),
        50 =>
        array (
          0 => 'Date_Holidays_Ireland',
          1 => '-n/a-',
        ),
        51 =>
        array (
          0 => 'Date_Holidays_Italy',
          1 => '-n/a-',
        ),
        52 =>
        array (
          0 => 'Date_Holidays_Japan',
          1 => '-n/a-',
        ),
        53 =>
        array (
          0 => 'Date_Holidays_Netherlands',
          1 => '-n/a-',
        ),
        54 =>
        array (
          0 => 'Date_Holidays_Norway',
          1 => '-n/a-',
        ),
        55 =>
        array (
          0 => 'Date_Holidays_PHPdotNet',
          1 => '-n/a-',
        ),
        56 =>
        array (
          0 => 'Date_Holidays_Romania',
          1 => '-n/a-',
        ),
        57 =>
        array (
          0 => 'Date_Holidays_Slovenia',
          1 => '-n/a-',
        ),
        58 =>
        array (
          0 => 'Date_Holidays_Sweden',
          1 => '-n/a-',
        ),
        59 =>
        array (
          0 => 'Date_Holidays_Ukraine',
          1 => '-n/a-',
        ),
        60 =>
        array (
          0 => 'Date_Holidays_UNO',
          1 => '-n/a-',
        ),
        61 =>
        array (
          0 => 'Date_Holidays_USA',
          1 => '-n/a-',
        ),
        62 =>
        array (
          0 => 'DB',
          1 => '-n/a-',
        ),
        63 =>
        array (
          0 => 'DBA',
          1 => '-n/a-',
        ),
        64 =>
        array (
          0 => 'DBA_Relational',
          1 => '-n/a-',
        ),
        65 =>
        array (
          0 => 'DB_ado',
          1 => '-n/a-',
        ),
        66 =>
        array (
          0 => 'DB_DataObject',
          1 => '-n/a-',
        ),
        67 =>
        array (
          0 => 'DB_DataObject_FormBuilder',
          1 => '-n/a-',
        ),
        68 =>
        array (
          0 => 'DB_ldap',
          1 => '-n/a-',
        ),
        69 =>
        array (
          0 => 'DB_ldap2',
          1 => '-n/a-',
        ),
        70 =>
        array (
          0 => 'DB_NestedSet',
          1 => '-n/a-',
        ),
        71 =>
        array (
          0 => 'DB_NestedSet2',
          1 => '-n/a-',
        ),
        72 =>
        array (
          0 => 'DB_odbtp',
          1 => '-n/a-',
        ),
        73 =>
        array (
          0 => 'DB_Pager',
          1 => '-n/a-',
        ),
        74 =>
        array (
          0 => 'DB_QueryTool',
          1 => '-n/a-',
        ),
        75 =>
        array (
          0 => 'DB_Sqlite_Tools',
          1 => '-n/a-',
        ),
        76 =>
        array (
          0 => 'DB_Table',
          1 => '-n/a-',
        ),
        77 =>
        array (
          0 => 'Event_Dispatcher',
          1 => '-n/a-',
        ),
        78 =>
        array (
          0 => 'Event_SignalEmitter',
          1 => '-n/a-',
        ),
        79 =>
        array (
          0 => 'File',
          1 => '-n/a-',
        ),
        80 =>
        array (
          0 => 'File_Archive',
          1 => '-n/a-',
        ),
        81 =>
        array (
          0 => 'File_Bittorrent',
          1 => '-n/a-',
        ),
        82 =>
        array (
          0 => 'File_Bittorrent2',
          1 => '-n/a-',
        ),
        83 =>
        array (
          0 => 'File_Cabinet',
          1 => '-n/a-',
        ),
        84 =>
        array (
          0 => 'File_CSV',
          1 => '-n/a-',
        ),
        85 =>
        array (
          0 => 'File_CSV_DataSource',
          1 => '-n/a-',
        ),
        86 =>
        array (
          0 => 'File_DeliciousLibrary',
          1 => '-n/a-',
        ),
        87 =>
        array (
          0 => 'File_DICOM',
          1 => '-n/a-',
        ),
        88 =>
        array (
          0 => 'File_DNS',
          1 => '-n/a-',
        ),
        89 =>
        array (
          0 => 'File_Find',
          1 => '-n/a-',
        ),
        90 =>
        array (
          0 => 'File_Fortune',
          1 => '-n/a-',
        ),
        91 =>
        array (
          0 => 'File_Fstab',
          1 => '-n/a-',
        ),
        92 =>
        array (
          0 => 'File_Gettext',
          1 => '-n/a-',
        ),
        93 =>
        array (
          0 => 'File_HtAccess',
          1 => '-n/a-',
        ),
        94 =>
        array (
          0 => 'File_IMC',
          1 => '-n/a-',
        ),
        95 =>
        array (
          0 => 'File_Infopath',
          1 => '-n/a-',
        ),
        96 =>
        array (
          0 => 'File_MARC',
          1 => '-n/a-',
        ),
        97 =>
        array (
          0 => 'File_Mogile',
          1 => '-n/a-',
        ),
        98 =>
        array (
          0 => 'File_Ogg',
          1 => '-n/a-',
        ),
        99 =>
        array (
          0 => 'File_Passwd',
          1 => '-n/a-',
        ),
        100 =>
        array (
          0 => 'File_PDF',
          1 => '-n/a-',
        ),
        101 =>
        array (
          0 => 'File_SearchReplace',
          1 => '-n/a-',
        ),
        102 =>
        array (
          0 => 'File_Sitemap',
          1 => '-n/a-',
        ),
        103 =>
        array (
          0 => 'File_SMBPasswd',
          1 => '-n/a-',
        ),
        104 =>
        array (
          0 => 'File_Util',
          1 => '-n/a-',
        ),
        105 =>
        array (
          0 => 'File_XSPF',
          1 => '-n/a-',
        ),
        106 =>
        array (
          0 => 'FSM',
          1 => '-n/a-',
        ),
        107 =>
        array (
          0 => 'Games_Chess',
          1 => '-n/a-',
        ),
        108 =>
        array (
          0 => 'Genealogy_Gedcom',
          1 => '-n/a-',
        ),
        109 =>
        array (
          0 => 'Gtk2_EntryDialog',
          1 => '-n/a-',
        ),
        110 =>
        array (
          0 => 'Gtk2_ExceptionDump',
          1 => '-n/a-',
        ),
        111 =>
        array (
          0 => 'Gtk2_FileDrop',
          1 => '-n/a-',
        ),
        112 =>
        array (
          0 => 'Gtk2_IndexedComboBox',
          1 => '-n/a-',
        ),
        113 =>
        array (
          0 => 'Gtk2_PHPConfig',
          1 => '-n/a-',
        ),
        114 =>
        array (
          0 => 'Gtk2_ScrollingLabel',
          1 => '-n/a-',
        ),
        115 =>
        array (
          0 => 'Gtk2_VarDump',
          1 => '-n/a-',
        ),
        116 =>
        array (
          0 => 'Gtk_FileDrop',
          1 => '-n/a-',
        ),
        117 =>
        array (
          0 => 'Gtk_MDB_Designer',
          1 => '-n/a-',
        ),
        118 =>
        array (
          0 => 'Gtk_ScrollingLabel',
          1 => '-n/a-',
        ),
        119 =>
        array (
          0 => 'Gtk_Styled',
          1 => '-n/a-',
        ),
        120 =>
        array (
          0 => 'Gtk_VarDump',
          1 => '-n/a-',
        ),
        121 =>
        array (
          0 => 'HTML_AJAX',
          1 => '-n/a-',
        ),
        122 =>
        array (
          0 => 'HTML_BBCodeParser',
          1 => '-n/a-',
        ),
        123 =>
        array (
          0 => 'HTML_Common',
          1 => '-n/a-',
        ),
        124 =>
        array (
          0 => 'HTML_Common2',
          1 => '-n/a-',
        ),
        125 =>
        array (
          0 => 'HTML_Crypt',
          1 => '-n/a-',
        ),
        126 =>
        array (
          0 => 'HTML_CSS',
          1 => '-n/a-',
        ),
        127 =>
        array (
          0 => 'HTML_Entities',
          1 => '-n/a-',
        ),
        128 =>
        array (
          0 => 'HTML_Form',
          1 => '-n/a-',
        ),
        129 =>
        array (
          0 => 'HTML_Javascript',
          1 => '-n/a-',
        ),
        130 =>
        array (
          0 => 'HTML_Menu',
          1 => '-n/a-',
        ),
        131 =>
        array (
          0 => 'HTML_Page',
          1 => '-n/a-',
        ),
        132 =>
        array (
          0 => 'HTML_Page2',
          1 => '-n/a-',
        ),
        133 =>
        array (
          0 => 'HTML_Progress',
          1 => '-n/a-',
        ),
        134 =>
        array (
          0 => 'HTML_Progress2',
          1 => '-n/a-',
        ),
        135 =>
        array (
          0 => 'HTML_QuickForm',
          1 => '-n/a-',
        ),
        136 =>
        array (
          0 => 'HTML_QuickForm2',
          1 => '-n/a-',
        ),
        137 =>
        array (
          0 => 'HTML_QuickForm_advmultiselect',
          1 => '-n/a-',
        ),
        138 =>
        array (
          0 => 'HTML_QuickForm_altselect',
          1 => '-n/a-',
        ),
        139 =>
        array (
          0 => 'HTML_QuickForm_CAPTCHA',
          1 => '-n/a-',
        ),
        140 =>
        array (
          0 => 'HTML_QuickForm_Controller',
          1 => '-n/a-',
        ),
        141 =>
        array (
          0 => 'HTML_QuickForm_DHTMLRulesTableless',
          1 => '-n/a-',
        ),
        142 =>
        array (
          0 => 'HTML_QuickForm_ElementGrid',
          1 => '-n/a-',
        ),
        143 =>
        array (
          0 => 'HTML_QuickForm_Livesearch',
          1 => '-n/a-',
        ),
        144 =>
        array (
          0 => 'HTML_QuickForm_Renderer_Tableless',
          1 => '-n/a-',
        ),
        145 =>
        array (
          0 => 'HTML_QuickForm_Rule_Spelling',
          1 => '-n/a-',
        ),
        146 =>
        array (
          0 => 'HTML_QuickForm_SelectFilter',
          1 => '-n/a-',
        ),
        147 =>
        array (
          0 => 'HTML_Safe',
          1 => '-n/a-',
        ),
        148 =>
        array (
          0 => 'HTML_Select',
          1 => '-n/a-',
        ),
        149 =>
        array (
          0 => 'HTML_Select_Common',
          1 => '-n/a-',
        ),
        150 =>
        array (
          0 => 'HTML_Table',
          1 => '-n/a-',
        ),
        151 =>
        array (
          0 => 'HTML_Table_Matrix',
          1 => '-n/a-',
        ),
        152 =>
        array (
          0 => 'HTML_TagCloud',
          1 => '-n/a-',
        ),
        153 =>
        array (
          0 => 'HTML_Template_Flexy',
          1 => '-n/a-',
        ),
        154 =>
        array (
          0 => 'HTML_Template_IT',
          1 => '-n/a-',
        ),
        155 =>
        array (
          0 => 'HTML_Template_PHPLIB',
          1 => '-n/a-',
        ),
        156 =>
        array (
          0 => 'HTML_Template_Sigma',
          1 => '-n/a-',
        ),
        157 =>
        array (
          0 => 'HTML_Template_Xipe',
          1 => '-n/a-',
        ),
        158 =>
        array (
          0 => 'HTML_TreeMenu',
          1 => '-n/a-',
        ),
        159 =>
        array (
          0 => 'HTTP',
          1 => '-n/a-',
        ),
        160 =>
        array (
          0 => 'HTTP_Client',
          1 => '-n/a-',
        ),
        161 =>
        array (
          0 => 'HTTP_Download',
          1 => '-n/a-',
        ),
        162 =>
        array (
          0 => 'HTTP_FloodControl',
          1 => '-n/a-',
        ),
        163 =>
        array (
          0 => 'HTTP_Header',
          1 => '-n/a-',
        ),
        164 =>
        array (
          0 => 'HTTP_Request',
          1 => '-n/a-',
        ),
        165 =>
        array (
          0 => 'HTTP_Request2',
          1 => '-n/a-',
        ),
        166 =>
        array (
          0 => 'HTTP_Server',
          1 => '-n/a-',
        ),
        167 =>
        array (
          0 => 'HTTP_Session',
          1 => '-n/a-',
        ),
        168 =>
        array (
          0 => 'HTTP_Session2',
          1 => '-n/a-',
        ),
        169 =>
        array (
          0 => 'HTTP_SessionServer',
          1 => '-n/a-',
        ),
        170 =>
        array (
          0 => 'HTTP_Upload',
          1 => '-n/a-',
        ),
        171 =>
        array (
          0 => 'HTTP_WebDAV_Client',
          1 => '-n/a-',
        ),
        172 =>
        array (
          0 => 'HTTP_WebDAV_Server',
          1 => '-n/a-',
        ),
        173 =>
        array (
          0 => 'I18N',
          1 => '-n/a-',
        ),
        174 =>
        array (
          0 => 'I18Nv2',
          1 => '-n/a-',
        ),
        175 =>
        array (
          0 => 'I18N_UnicodeNormalizer',
          1 => '-n/a-',
        ),
        176 =>
        array (
          0 => 'I18N_UnicodeString',
          1 => '-n/a-',
        ),
        177 =>
        array (
          0 => 'Image_3D',
          1 => '-n/a-',
        ),
        178 =>
        array (
          0 => 'Image_Barcode',
          1 => '-n/a-',
        ),
        179 =>
        array (
          0 => 'Image_Canvas',
          1 => '-n/a-',
        ),
        180 =>
        array (
          0 => 'Image_Color',
          1 => '-n/a-',
        ),
        181 =>
        array (
          0 => 'Image_Color2',
          1 => '-n/a-',
        ),
        182 =>
        array (
          0 => 'Image_GIS',
          1 => '-n/a-',
        ),
        183 =>
        array (
          0 => 'Image_Graph',
          1 => '-n/a-',
        ),
        184 =>
        array (
          0 => 'Image_GraphViz',
          1 => '-n/a-',
        ),
        185 =>
        array (
          0 => 'Image_IPTC',
          1 => '-n/a-',
        ),
        186 =>
        array (
          0 => 'Image_JpegMarkerReader',
          1 => '-n/a-',
        ),
        187 =>
        array (
          0 => 'Image_JpegXmpReader',
          1 => '-n/a-',
        ),
        188 =>
        array (
          0 => 'Image_MonoBMP',
          1 => '-n/a-',
        ),
        189 =>
        array (
          0 => 'Image_Puzzle',
          1 => '-n/a-',
        ),
        190 =>
        array (
          0 => 'Image_Remote',
          1 => '-n/a-',
        ),
        191 =>
        array (
          0 => 'Image_Text',
          1 => '-n/a-',
        ),
        192 =>
        array (
          0 => 'Image_Tools',
          1 => '-n/a-',
        ),
        193 =>
        array (
          0 => 'Image_Transform',
          1 => '-n/a-',
        ),
        194 =>
        array (
          0 => 'Image_WBMP',
          1 => '-n/a-',
        ),
        195 =>
        array (
          0 => 'Image_XBM',
          1 => '-n/a-',
        ),
        196 =>
        array (
          0 => 'Inline_C',
          1 => '-n/a-',
        ),
        197 =>
        array (
          0 => 'LiveUser',
          1 => '-n/a-',
        ),
        198 =>
        array (
          0 => 'LiveUser_Admin',
          1 => '-n/a-',
        ),
        199 =>
        array (
          0 => 'Log',
          1 => '-n/a-',
        ),
        200 =>
        array (
          0 => 'Mail',
          1 => '-n/a-',
        ),
        201 =>
        array (
          0 => 'Mail_IMAP',
          1 => '-n/a-',
        ),
        202 =>
        array (
          0 => 'Mail_IMAPv2',
          1 => '-n/a-',
        ),
        203 =>
        array (
          0 => 'Mail_Mbox',
          1 => '-n/a-',
        ),
        204 =>
        array (
          0 => 'Mail_Mime',
          1 => '-n/a-',
        ),
        205 =>
        array (
          0 => 'Mail_mimeDecode',
          1 => '-n/a-',
        ),
        206 =>
        array (
          0 => 'Mail_Queue',
          1 => '-n/a-',
        ),
        207 =>
        array (
          0 => 'Math_Basex',
          1 => '-n/a-',
        ),
        208 =>
        array (
          0 => 'Math_BigInteger',
          1 => '-n/a-',
        ),
        209 =>
        array (
          0 => 'Math_BinaryUtils',
          1 => '-n/a-',
        ),
        210 =>
        array (
          0 => 'Math_Combinatorics',
          1 => '-n/a-',
        ),
        211 =>
        array (
          0 => 'Math_Complex',
          1 => '-n/a-',
        ),
        212 =>
        array (
          0 => 'Math_Derivative',
          1 => '-n/a-',
        ),
        213 =>
        array (
          0 => 'Math_Fibonacci',
          1 => '-n/a-',
        ),
        214 =>
        array (
          0 => 'Math_Finance',
          1 => '-n/a-',
        ),
        215 =>
        array (
          0 => 'Math_Fraction',
          1 => '-n/a-',
        ),
        216 =>
        array (
          0 => 'Math_Histogram',
          1 => '-n/a-',
        ),
        217 =>
        array (
          0 => 'Math_Integer',
          1 => '-n/a-',
        ),
        218 =>
        array (
          0 => 'Math_Matrix',
          1 => '-n/a-',
        ),
        219 =>
        array (
          0 => 'Math_Numerical_RootFinding',
          1 => '-n/a-',
        ),
        220 =>
        array (
          0 => 'Math_Polynomial',
          1 => '-n/a-',
        ),
        221 =>
        array (
          0 => 'Math_Quaternion',
          1 => '-n/a-',
        ),
        222 =>
        array (
          0 => 'Math_RPN',
          1 => '-n/a-',
        ),
        223 =>
        array (
          0 => 'Math_Stats',
          1 => '-n/a-',
        ),
        224 =>
        array (
          0 => 'Math_TrigOp',
          1 => '-n/a-',
        ),
        225 =>
        array (
          0 => 'Math_Vector',
          1 => '-n/a-',
        ),
        226 =>
        array (
          0 => 'MDB',
          1 => '-n/a-',
        ),
        227 =>
        array (
          0 => 'MDB2',
          1 => '-n/a-',
        ),
        228 =>
        array (
          0 => 'MDB2_Driver_fbsql',
          1 => '-n/a-',
        ),
        229 =>
        array (
          0 => 'MDB2_Driver_ibase',
          1 => '-n/a-',
        ),
        230 =>
        array (
          0 => 'MDB2_Driver_mssql',
          1 => '-n/a-',
        ),
        231 =>
        array (
          0 => 'MDB2_Driver_mysql',
          1 => '-n/a-',
        ),
        232 =>
        array (
          0 => 'MDB2_Driver_mysqli',
          1 => '-n/a-',
        ),
        233 =>
        array (
          0 => 'MDB2_Driver_oci8',
          1 => '-n/a-',
        ),
        234 =>
        array (
          0 => 'MDB2_Driver_pgsql',
          1 => '-n/a-',
        ),
        235 =>
        array (
          0 => 'MDB2_Driver_querysim',
          1 => '-n/a-',
        ),
        236 =>
        array (
          0 => 'MDB2_Driver_sqlite',
          1 => '-n/a-',
        ),
        237 =>
        array (
          0 => 'MDB2_Schema',
          1 => '-n/a-',
        ),
        238 =>
        array (
          0 => 'MDB2_TableBrowser',
          1 => '-n/a-',
        ),
        239 =>
        array (
          0 => 'MDB_QueryTool',
          1 => '-n/a-',
        ),
        240 =>
        array (
          0 => 'Message',
          1 => '-n/a-',
        ),
        241 =>
        array (
          0 => 'MIME_Type',
          1 => '-n/a-',
        ),
        242 =>
        array (
          0 => 'MP3_Id',
          1 => '-n/a-',
        ),
        243 =>
        array (
          0 => 'MP3_IDv2',
          1 => '-n/a-',
        ),
        244 =>
        array (
          0 => 'MP3_Playlist',
          1 => '-n/a-',
        ),
        245 =>
        array (
          0 => 'Net_CDDB',
          1 => '-n/a-',
        ),
        246 =>
        array (
          0 => 'Net_CheckIP',
          1 => '-n/a-',
        ),
        247 =>
        array (
          0 => 'Net_CheckIP2',
          1 => '-n/a-',
        ),
        248 =>
        array (
          0 => 'Net_Curl',
          1 => '-n/a-',
        ),
        249 =>
        array (
          0 => 'Net_Cyrus',
          1 => '-n/a-',
        ),
        250 =>
        array (
          0 => 'Net_Dict',
          1 => '-n/a-',
        ),
        251 =>
        array (
          0 => 'Net_Dig',
          1 => '-n/a-',
        ),
        252 =>
        array (
          0 => 'Net_DIME',
          1 => '-n/a-',
        ),
        253 =>
        array (
          0 => 'Net_DNS',
          1 => '-n/a-',
        ),
        254 =>
        array (
          0 => 'Net_DNSBL',
          1 => '-n/a-',
        ),
        255 =>
        array (
          0 => 'Net_Finger',
          1 => '-n/a-',
        ),
        256 =>
        array (
          0 => 'Net_FTP',
          1 => '-n/a-',
        ),
        257 =>
        array (
          0 => 'Net_FTP2',
          1 => '-n/a-',
        ),
        258 =>
        array (
          0 => 'Net_GameServerQuery',
          1 => '-n/a-',
        ),
        259 =>
        array (
          0 => 'Net_Gearman',
          1 => '-n/a-',
        ),
        260 =>
        array (
          0 => 'Net_Geo',
          1 => '-n/a-',
        ),
        261 =>
        array (
          0 => 'Net_GeoIP',
          1 => '-n/a-',
        ),
        262 =>
        array (
          0 => 'Net_Growl',
          1 => '-n/a-',
        ),
        263 =>
        array (
          0 => 'Net_HL7',
          1 => '-n/a-',
        ),
        264 =>
        array (
          0 => 'Net_Ident',
          1 => '-n/a-',
        ),
        265 =>
        array (
          0 => 'Net_IDNA',
          1 => '-n/a-',
        ),
        266 =>
        array (
          0 => 'Net_IMAP',
          1 => '-n/a-',
        ),
        267 =>
        array (
          0 => 'Net_IPv4',
          1 => '-n/a-',
        ),
        268 =>
        array (
          0 => 'Net_IPv6',
          1 => '-n/a-',
        ),
        269 =>
        array (
          0 => 'Net_IRC',
          1 => '-n/a-',
        ),
        270 =>
        array (
          0 => 'Net_LDAP',
          1 => '-n/a-',
        ),
        271 =>
        array (
          0 => 'Net_LDAP2',
          1 => '-n/a-',
        ),
        272 =>
        array (
          0 => 'Net_LMTP',
          1 => '-n/a-',
        ),
        273 =>
        array (
          0 => 'Net_MAC',
          1 => '-n/a-',
        ),
        274 =>
        array (
          0 => 'Net_Monitor',
          1 => '-n/a-',
        ),
        275 =>
        array (
          0 => 'Net_MPD',
          1 => '-n/a-',
        ),
        276 =>
        array (
          0 => 'Net_Nmap',
          1 => '-n/a-',
        ),
        277 =>
        array (
          0 => 'Net_NNTP',
          1 => '-n/a-',
        ),
        278 =>
        array (
          0 => 'Net_Ping',
          1 => '-n/a-',
        ),
        279 =>
        array (
          0 => 'Net_POP3',
          1 => '-n/a-',
        ),
        280 =>
        array (
          0 => 'Net_Portscan',
          1 => '-n/a-',
        ),
        281 =>
        array (
          0 => 'Net_Server',
          1 => '-n/a-',
        ),
        282 =>
        array (
          0 => 'Net_Sieve',
          1 => '-n/a-',
        ),
        283 =>
        array (
          0 => 'Net_SmartIRC',
          1 => '-n/a-',
        ),
        284 =>
        array (
          0 => 'Net_SMPP',
          1 => '-n/a-',
        ),
        285 =>
        array (
          0 => 'Net_SMPP_Client',
          1 => '-n/a-',
        ),
        286 =>
        array (
          0 => 'Net_SMS',
          1 => '-n/a-',
        ),
        287 =>
        array (
          0 => 'Net_SMTP',
          1 => '-n/a-',
        ),
        288 =>
        array (
          0 => 'Net_Socket',
          1 => '-n/a-',
        ),
        289 =>
        array (
          0 => 'Net_Traceroute',
          1 => '-n/a-',
        ),
        290 =>
        array (
          0 => 'Net_URL',
          1 => '-n/a-',
        ),
        291 =>
        array (
          0 => 'Net_URL2',
          1 => '-n/a-',
        ),
        292 =>
        array (
          0 => 'Net_URL_Mapper',
          1 => '-n/a-',
        ),
        293 =>
        array (
          0 => 'Net_UserAgent_Detect',
          1 => '-n/a-',
        ),
        294 =>
        array (
          0 => 'Net_UserAgent_Mobile',
          1 => '-n/a-',
        ),
        295 =>
        array (
          0 => 'Net_UserAgent_Mobile_GPS',
          1 => '-n/a-',
        ),
        296 =>
        array (
          0 => 'Net_Vpopmaild',
          1 => '-n/a-',
        ),
        297 =>
        array (
          0 => 'Net_Whois',
          1 => '-n/a-',
        ),
        298 =>
        array (
          0 => 'Net_Wifi',
          1 => '-n/a-',
        ),
        299 =>
        array (
          0 => 'Numbers_Roman',
          1 => '-n/a-',
        ),
        300 =>
        array (
          0 => 'Numbers_Words',
          1 => '-n/a-',
        ),
        301 =>
        array (
          0 => 'OLE',
          1 => '-n/a-',
        ),
        302 =>
        array (
          0 => 'OpenDocument',
          1 => '-n/a-',
        ),
        303 =>
        array (
          0 => 'Pager',
          1 => '-n/a-',
        ),
        304 =>
        array (
          0 => 'Pager_Sliding',
          1 => '-n/a-',
        ),
        305 =>
        array (
          0 => 'Payment_Clieop',
          1 => '-n/a-',
        ),
        306 =>
        array (
          0 => 'Payment_DTA',
          1 => '-n/a-',
        ),
        307 =>
        array (
          0 => 'Payment_PayPal_SOAP',
          1 => '-n/a-',
        ),
        308 =>
        array (
          0 => 'Payment_Process',
          1 => '-n/a-',
        ),
        309 =>
        array (
          0 => 'PEAR',
          1 => '-n/a-',
        ),
        310 =>
        array (
          0 => 'pearweb',
          1 => '-n/a-',
        ),
        311 =>
        array (
          0 => 'pearweb_channelxml',
          1 => '-n/a-',
        ),
        312 =>
        array (
          0 => 'pearweb_gopear',
          1 => '-n/a-',
        ),
        313 =>
        array (
          0 => 'pearweb_index',
          1 => '-n/a-',
        ),
        314 =>
        array (
          0 => 'pearweb_phars',
          1 => '-n/a-',
        ),
        315 =>
        array (
          0 => 'PEAR_Command_Packaging',
          1 => '-n/a-',
        ),
        316 =>
        array (
          0 => 'PEAR_Delegator',
          1 => '-n/a-',
        ),
        317 =>
        array (
          0 => 'PEAR_ErrorStack',
          1 => '-n/a-',
        ),
        318 =>
        array (
          0 => 'PEAR_Frontend_Gtk',
          1 => '-n/a-',
        ),
        319 =>
        array (
          0 => 'PEAR_Frontend_Gtk2',
          1 => '-n/a-',
        ),
        320 =>
        array (
          0 => 'PEAR_Frontend_Web',
          1 => '-n/a-',
        ),
        321 =>
        array (
          0 => 'PEAR_Info',
          1 => '-n/a-',
        ),
        322 =>
        array (
          0 => 'PEAR_PackageFileManager',
          1 => '-n/a-',
        ),
        323 =>
        array (
          0 => 'PEAR_PackageFileManager2',
          1 => '-n/a-',
        ),
        324 =>
        array (
          0 => 'PEAR_PackageFileManager_Cli',
          1 => '-n/a-',
        ),
        325 =>
        array (
          0 => 'PEAR_PackageFileManager_Frontend',
          1 => '-n/a-',
        ),
        326 =>
        array (
          0 => 'PEAR_PackageFileManager_Frontend_Web',
          1 => '-n/a-',
        ),
        327 =>
        array (
          0 => 'PEAR_PackageFileManager_GUI_Gtk',
          1 => '-n/a-',
        ),
        328 =>
        array (
          0 => 'PEAR_PackageFileManager_Plugins',
          1 => '-n/a-',
        ),
        329 =>
        array (
          0 => 'PEAR_PackageUpdate',
          1 => '-n/a-',
        ),
        330 =>
        array (
          0 => 'PEAR_PackageUpdate_Gtk2',
          1 => '-n/a-',
        ),
        331 =>
        array (
          0 => 'PEAR_PackageUpdate_Web',
          1 => '-n/a-',
        ),
        332 =>
        array (
          0 => 'PEAR_RemoteInstaller',
          1 => '-n/a-',
        ),
        333 =>
        array (
          0 => 'PEAR_Size',
          1 => '-n/a-',
        ),
        334 =>
        array (
          0 => 'PHPDoc',
          1 => '-n/a-',
        ),
        335 =>
        array (
          0 => 'PhpDocumentor',
          1 => '-n/a-',
        ),
        336 =>
        array (
          0 => 'PHPUnit',
          1 => '-n/a-',
        ),
        337 =>
        array (
          0 => 'PHPUnit2',
          1 => '-n/a-',
        ),
        338 =>
        array (
          0 => 'PHP_Annotation',
          1 => '-n/a-',
        ),
        339 =>
        array (
          0 => 'PHP_Archive',
          1 => '-n/a-',
        ),
        340 =>
        array (
          0 => 'PHP_ArrayOf',
          1 => '-n/a-',
        ),
        341 =>
        array (
          0 => 'PHP_Beautifier',
          1 => '-n/a-',
        ),
        342 =>
        array (
          0 => 'PHP_CodeSniffer',
          1 => '-n/a-',
        ),
        343 =>
        array (
          0 => 'PHP_Compat',
          1 => '-n/a-',
        ),
        344 =>
        array (
          0 => 'PHP_CompatInfo',
          1 => '-n/a-',
        ),
        345 =>
        array (
          0 => 'PHP_Debug',
          1 => '-n/a-',
        ),
        346 =>
        array (
          0 => 'PHP_DocBlockGenerator',
          1 => '-n/a-',
        ),
        347 =>
        array (
          0 => 'PHP_Fork',
          1 => '-n/a-',
        ),
        348 =>
        array (
          0 => 'PHP_FunctionCallTracer',
          1 => '-n/a-',
        ),
        349 =>
        array (
          0 => 'PHP_LexerGenerator',
          1 => '-n/a-',
        ),
        350 =>
        array (
          0 => 'PHP_Parser',
          1 => '-n/a-',
        ),
        351 =>
        array (
          0 => 'PHP_ParserGenerator',
          1 => '-n/a-',
        ),
        352 =>
        array (
          0 => 'PHP_Parser_DocblockParser',
          1 => '-n/a-',
        ),
        353 =>
        array (
          0 => 'PHP_Shell',
          1 => '-n/a-',
        ),
        354 =>
        array (
          0 => 'PHP_UML',
          1 => '-n/a-',
        ),
        355 =>
        array (
          0 => 'QA_Peardoc_Coverage',
          1 => '-n/a-',
        ),
        356 =>
        array (
          0 => 'RDF',
          1 => '-n/a-',
        ),
        357 =>
        array (
          0 => 'RDF_N3',
          1 => '-n/a-',
        ),
        358 =>
        array (
          0 => 'RDF_NTriple',
          1 => '-n/a-',
        ),
        359 =>
        array (
          0 => 'RDF_RDQL',
          1 => '-n/a-',
        ),
        360 =>
        array (
          0 => 'Science_Chemistry',
          1 => '-n/a-',
        ),
        361 =>
        array (
          0 => 'ScriptReorganizer',
          1 => '-n/a-',
        ),
        362 =>
        array (
          0 => 'Search_Mnogosearch',
          1 => '-n/a-',
        ),
        363 =>
        array (
          0 => 'Services_Akismet',
          1 => '-n/a-',
        ),
        364 =>
        array (
          0 => 'Services_Akismet2',
          1 => '-n/a-',
        ),
        365 =>
        array (
          0 => 'Services_Amazon',
          1 => '-n/a-',
        ),
        366 =>
        array (
          0 => 'Services_Amazon_S3',
          1 => '-n/a-',
        ),
        367 =>
        array (
          0 => 'Services_Amazon_SQS',
          1 => '-n/a-',
        ),
        368 =>
        array (
          0 => 'Services_Atlassian_Crowd',
          1 => '-n/a-',
        ),
        369 =>
        array (
          0 => 'Services_Blogging',
          1 => '-n/a-',
        ),
        370 =>
        array (
          0 => 'Services_Compete',
          1 => '-n/a-',
        ),
        371 =>
        array (
          0 => 'Services_Delicious',
          1 => '-n/a-',
        ),
        372 =>
        array (
          0 => 'Services_Digg',
          1 => '-n/a-',
        ),
        373 =>
        array (
          0 => 'Services_DynDNS',
          1 => '-n/a-',
        ),
        374 =>
        array (
          0 => 'Services_Ebay',
          1 => '-n/a-',
        ),
        375 =>
        array (
          0 => 'Services_ExchangeRates',
          1 => '-n/a-',
        ),
        376 =>
        array (
          0 => 'Services_Facebook',
          1 => '-n/a-',
        ),
        377 =>
        array (
          0 => 'Services_GeoNames',
          1 => '-n/a-',
        ),
        378 =>
        array (
          0 => 'Services_Google',
          1 => '-n/a-',
        ),
        379 =>
        array (
          0 => 'Services_Hatena',
          1 => '-n/a-',
        ),
        380 =>
        array (
          0 => 'Services_oEmbed',
          1 => '-n/a-',
        ),
        381 =>
        array (
          0 => 'Services_OpenSearch',
          1 => '-n/a-',
        ),
        382 =>
        array (
          0 => 'Services_Pingback',
          1 => '-n/a-',
        ),
        383 =>
        array (
          0 => 'Services_ProjectHoneyPot',
          1 => '-n/a-',
        ),
        384 =>
        array (
          0 => 'Services_SharedBook',
          1 => '-n/a-',
        ),
        385 =>
        array (
          0 => 'Services_Technorati',
          1 => '-n/a-',
        ),
        386 =>
        array (
          0 => 'Services_TinyURL',
          1 => '-n/a-',
        ),
        387 =>
        array (
          0 => 'Services_Trackback',
          1 => '-n/a-',
        ),
        388 =>
        array (
          0 => 'Services_TwitPic',
          1 => '-n/a-',
        ),
        389 =>
        array (
          0 => 'Services_Twitter',
          1 => '-n/a-',
        ),
        390 =>
        array (
          0 => 'Services_urlTea',
          1 => '-n/a-',
        ),
        391 =>
        array (
          0 => 'Services_W3C_CSSValidator',
          1 => '-n/a-',
        ),
        392 =>
        array (
          0 => 'Services_W3C_HTMLValidator',
          1 => '-n/a-',
        ),
        393 =>
        array (
          0 => 'Services_Weather',
          1 => '-n/a-',
        ),
        394 =>
        array (
          0 => 'Services_Webservice',
          1 => '-n/a-',
        ),
        395 =>
        array (
          0 => 'Services_Yadis',
          1 => '-n/a-',
        ),
        396 =>
        array (
          0 => 'Services_Yahoo',
          1 => '-n/a-',
        ),
        397 =>
        array (
          0 => 'Services_Yahoo_JP',
          1 => '-n/a-',
        ),
        398 =>
        array (
          0 => 'Services_YouTube',
          1 => '-n/a-',
        ),
        399 =>
        array (
          0 => 'SOAP',
          1 => '-n/a-',
        ),
        400 =>
        array (
          0 => 'SOAP_Interop',
          1 => '-n/a-',
        ),
        401 =>
        array (
          0 => 'Spreadsheet_Excel_Writer',
          1 => '-n/a-',
        ),
        402 =>
        array (
          0 => 'SQL_Parser',
          1 => '-n/a-',
        ),
        403 =>
        array (
          0 => 'Stream_SHM',
          1 => '-n/a-',
        ),
        404 =>
        array (
          0 => 'Stream_Var',
          1 => '-n/a-',
        ),
        405 =>
        array (
          0 => 'Structures_BibTex',
          1 => '-n/a-',
        ),
        406 =>
        array (
          0 => 'Structures_DataGrid',
          1 => '-n/a-',
        ),
        407 =>
        array (
          0 => 'Structures_DataGrid_DataSource_Array',
          1 => '-n/a-',
        ),
        408 =>
        array (
          0 => 'Structures_DataGrid_DataSource_CSV',
          1 => '-n/a-',
        ),
        409 =>
        array (
          0 => 'Structures_DataGrid_DataSource_DataObject',
          1 => '-n/a-',
        ),
        410 =>
        array (
          0 => 'Structures_DataGrid_DataSource_DB',
          1 => '-n/a-',
        ),
        411 =>
        array (
          0 => 'Structures_DataGrid_DataSource_DBQuery',
          1 => '-n/a-',
        ),
        412 =>
        array (
          0 => 'Structures_DataGrid_DataSource_DBTable',
          1 => '-n/a-',
        ),
        413 =>
        array (
          0 => 'Structures_DataGrid_DataSource_Excel',
          1 => '-n/a-',
        ),
        414 =>
        array (
          0 => 'Structures_DataGrid_DataSource_MDB2',
          1 => '-n/a-',
        ),
        415 =>
        array (
          0 => 'Structures_DataGrid_DataSource_PDO',
          1 => '-n/a-',
        ),
        416 =>
        array (
          0 => 'Structures_DataGrid_DataSource_RSS',
          1 => '-n/a-',
        ),
        417 =>
        array (
          0 => 'Structures_DataGrid_DataSource_XML',
          1 => '-n/a-',
        ),
        418 =>
        array (
          0 => 'Structures_DataGrid_Renderer_Console',
          1 => '-n/a-',
        ),
        419 =>
        array (
          0 => 'Structures_DataGrid_Renderer_CSV',
          1 => '-n/a-',
        ),
        420 =>
        array (
          0 => 'Structures_DataGrid_Renderer_Flexy',
          1 => '-n/a-',
        ),
        421 =>
        array (
          0 => 'Structures_DataGrid_Renderer_HTMLSortForm',
          1 => '-n/a-',
        ),
        422 =>
        array (
          0 => 'Structures_DataGrid_Renderer_HTMLTable',
          1 => '-n/a-',
        ),
        423 =>
        array (
          0 => 'Structures_DataGrid_Renderer_Pager',
          1 => '-n/a-',
        ),
        424 =>
        array (
          0 => 'Structures_DataGrid_Renderer_Smarty',
          1 => '-n/a-',
        ),
        425 =>
        array (
          0 => 'Structures_DataGrid_Renderer_XLS',
          1 => '-n/a-',
        ),
        426 =>
        array (
          0 => 'Structures_DataGrid_Renderer_XML',
          1 => '-n/a-',
        ),
        427 =>
        array (
          0 => 'Structures_DataGrid_Renderer_XUL',
          1 => '-n/a-',
        ),
        428 =>
        array (
          0 => 'Structures_Form',
          1 => '-n/a-',
        ),
        429 =>
        array (
          0 => 'Structures_Form_Gtk2',
          1 => '-n/a-',
        ),
        430 =>
        array (
          0 => 'Structures_Graph',
          1 => '-n/a-',
        ),
        431 =>
        array (
          0 => 'Structures_LinkedList',
          1 => '-n/a-',
        ),
        432 =>
        array (
          0 => 'System_Command',
          1 => '-n/a-',
        ),
        433 =>
        array (
          0 => 'System_Daemon',
          1 => '-n/a-',
        ),
        434 =>
        array (
          0 => 'System_Folders',
          1 => '-n/a-',
        ),
        435 =>
        array (
          0 => 'System_Mount',
          1 => '-n/a-',
        ),
        436 =>
        array (
          0 => 'System_ProcWatch',
          1 => '-n/a-',
        ),
        437 =>
        array (
          0 => 'System_SharedMemory',
          1 => '-n/a-',
        ),
        438 =>
        array (
          0 => 'System_Socket',
          1 => '-n/a-',
        ),
        439 =>
        array (
          0 => 'System_WinDrives',
          1 => '-n/a-',
        ),
        440 =>
        array (
          0 => 'Testing_DocTest',
          1 => '-n/a-',
        ),
        441 =>
        array (
          0 => 'Testing_FIT',
          1 => '-n/a-',
        ),
        442 =>
        array (
          0 => 'Testing_Selenium',
          1 => '-n/a-',
        ),
        443 =>
        array (
          0 => 'Text_CAPTCHA',
          1 => '-n/a-',
        ),
        444 =>
        array (
          0 => 'Text_CAPTCHA_Numeral',
          1 => '-n/a-',
        ),
        445 =>
        array (
          0 => 'Text_Diff',
          1 => '-n/a-',
        ),
        446 =>
        array (
          0 => 'Text_Figlet',
          1 => '-n/a-',
        ),
        447 =>
        array (
          0 => 'Text_Highlighter',
          1 => '-n/a-',
        ),
        448 =>
        array (
          0 => 'Text_Huffman',
          1 => '-n/a-',
        ),
        449 =>
        array (
          0 => 'Text_LanguageDetect',
          1 => '-n/a-',
        ),
        450 =>
        array (
          0 => 'Text_Password',
          1 => '-n/a-',
        ),
        451 =>
        array (
          0 => 'Text_PathNavigator',
          1 => '-n/a-',
        ),
        452 =>
        array (
          0 => 'Text_Spell_Audio',
          1 => '-n/a-',
        ),
        453 =>
        array (
          0 => 'Text_Statistics',
          1 => '-n/a-',
        ),
        454 =>
        array (
          0 => 'Text_TeXHyphen',
          1 => '-n/a-',
        ),
        455 =>
        array (
          0 => 'Text_Wiki',
          1 => '-n/a-',
        ),
        456 =>
        array (
          0 => 'Text_Wiki_BBCode',
          1 => '-n/a-',
        ),
        457 =>
        array (
          0 => 'Text_Wiki_Cowiki',
          1 => '-n/a-',
        ),
        458 =>
        array (
          0 => 'Text_Wiki_Creole',
          1 => '-n/a-',
        ),
        459 =>
        array (
          0 => 'Text_Wiki_Doku',
          1 => '-n/a-',
        ),
        460 =>
        array (
          0 => 'Text_Wiki_Mediawiki',
          1 => '-n/a-',
        ),
        461 =>
        array (
          0 => 'Text_Wiki_Tiki',
          1 => '-n/a-',
        ),
        462 =>
        array (
          0 => 'Translation',
          1 => '-n/a-',
        ),
        463 =>
        array (
          0 => 'Translation2',
          1 => '-n/a-',
        ),
        464 =>
        array (
          0 => 'Tree',
          1 => '-n/a-',
        ),
        465 =>
        array (
          0 => 'UDDI',
          1 => '-n/a-',
        ),
        466 =>
        array (
          0 => 'URI_Template',
          1 => '-n/a-',
        ),
        467 =>
        array (
          0 => 'Validate',
          1 => '-n/a-',
        ),
        468 =>
        array (
          0 => 'Validate_AR',
          1 => '-n/a-',
        ),
        469 =>
        array (
          0 => 'Validate_AT',
          1 => '-n/a-',
        ),
        470 =>
        array (
          0 => 'Validate_AU',
          1 => '-n/a-',
        ),
        471 =>
        array (
          0 => 'Validate_BE',
          1 => '-n/a-',
        ),
        472 =>
        array (
          0 => 'Validate_CA',
          1 => '-n/a-',
        ),
        473 =>
        array (
          0 => 'Validate_CH',
          1 => '-n/a-',
        ),
        474 =>
        array (
          0 => 'Validate_DE',
          1 => '-n/a-',
        ),
        475 =>
        array (
          0 => 'Validate_DK',
          1 => '-n/a-',
        ),
        476 =>
        array (
          0 => 'Validate_ES',
          1 => '-n/a-',
        ),
        477 =>
        array (
          0 => 'Validate_FI',
          1 => '-n/a-',
        ),
        478 =>
        array (
          0 => 'Validate_Finance',
          1 => '-n/a-',
        ),
        479 =>
        array (
          0 => 'Validate_Finance_CreditCard',
          1 => '-n/a-',
        ),
        480 =>
        array (
          0 => 'Validate_FR',
          1 => '-n/a-',
        ),
        481 =>
        array (
          0 => 'Validate_HU',
          1 => '-n/a-',
        ),
        482 =>
        array (
          0 => 'Validate_IE',
          1 => '-n/a-',
        ),
        483 =>
        array (
          0 => 'Validate_IN',
          1 => '-n/a-',
        ),
        484 =>
        array (
          0 => 'Validate_IS',
          1 => '-n/a-',
        ),
        485 =>
        array (
          0 => 'Validate_ISPN',
          1 => '-n/a-',
        ),
        486 =>
        array (
          0 => 'Validate_IT',
          1 => '-n/a-',
        ),
        487 =>
        array (
          0 => 'Validate_LV',
          1 => '-n/a-',
        ),
        488 =>
        array (
          0 => 'Validate_NL',
          1 => '-n/a-',
        ),
        489 =>
        array (
          0 => 'Validate_NZ',
          1 => '-n/a-',
        ),
        490 =>
        array (
          0 => 'Validate_PL',
          1 => '-n/a-',
        ),
        491 =>
        array (
          0 => 'Validate_ptBR',
          1 => '-n/a-',
        ),
        492 =>
        array (
          0 => 'Validate_RU',
          1 => '-n/a-',
        ),
        493 =>
        array (
          0 => 'Validate_UK',
          1 => '-n/a-',
        ),
        494 =>
        array (
          0 => 'Validate_US',
          1 => '-n/a-',
        ),
        495 =>
        array (
          0 => 'Validate_ZA',
          1 => '-n/a-',
        ),
        496 =>
        array (
          0 => 'Var_Dump',
          1 => '-n/a-',
        ),
        497 =>
        array (
          0 => 'VersionControl_SVN',
          1 => '-n/a-',
        ),
        498 =>
        array (
          0 => 'VFS',
          1 => '-n/a-',
        ),
        499 =>
        array (
          0 => 'XML_Beautifier',
          1 => '-n/a-',
        ),
        500 =>
        array (
          0 => 'XML_CSSML',
          1 => '-n/a-',
        ),
        501 =>
        array (
          0 => 'XML_DB_eXist',
          1 => '-n/a-',
        ),
        502 =>
        array (
          0 => 'XML_DTD',
          1 => '-n/a-',
        ),
        503 =>
        array (
          0 => 'XML_FastCreate',
          1 => '-n/a-',
        ),
        504 =>
        array (
          0 => 'XML_Feed_Parser',
          1 => '-n/a-',
        ),
        505 =>
        array (
          0 => 'XML_fo2pdf',
          1 => '-n/a-',
        ),
        506 =>
        array (
          0 => 'XML_FOAF',
          1 => '-n/a-',
        ),
        507 =>
        array (
          0 => 'XML_GRDDL',
          1 => '-n/a-',
        ),
        508 =>
        array (
          0 => 'XML_HTMLSax',
          1 => '-n/a-',
        ),
        509 =>
        array (
          0 => 'XML_HTMLSax3',
          1 => '-n/a-',
        ),
        510 =>
        array (
          0 => 'XML_image2svg',
          1 => '-n/a-',
        ),
        511 =>
        array (
          0 => 'XML_Indexing',
          1 => '-n/a-',
        ),
        512 =>
        array (
          0 => 'XML_MXML',
          1 => '-n/a-',
        ),
        513 =>
        array (
          0 => 'XML_NITF',
          1 => '-n/a-',
        ),
        514 =>
        array (
          0 => 'XML_Parser',
          1 => '-n/a-',
        ),
        515 =>
        array (
          0 => 'XML_Query2XML',
          1 => '-n/a-',
        ),
        516 =>
        array (
          0 => 'XML_RDDL',
          1 => '-n/a-',
        ),
        517 =>
        array (
          0 => 'XML_RPC',
          1 => '-n/a-',
        ),
        518 =>
        array (
          0 => 'XML_RPC2',
          1 => '-n/a-',
        ),
        519 =>
        array (
          0 => 'XML_RSS',
          1 => '-n/a-',
        ),
        520 =>
        array (
          0 => 'XML_SaxFilters',
          1 => '-n/a-',
        ),
        521 =>
        array (
          0 => 'XML_Serializer',
          1 => '-n/a-',
        ),
        522 =>
        array (
          0 => 'XML_sql2xml',
          1 => '-n/a-',
        ),
        523 =>
        array (
          0 => 'XML_Statistics',
          1 => '-n/a-',
        ),
        524 =>
        array (
          0 => 'XML_SVG',
          1 => '-n/a-',
        ),
        525 =>
        array (
          0 => 'XML_svg2image',
          1 => '-n/a-',
        ),
        526 =>
        array (
          0 => 'XML_Transformer',
          1 => '-n/a-',
        ),
        527 =>
        array (
          0 => 'XML_Tree',
          1 => '-n/a-',
        ),
        528 =>
        array (
          0 => 'XML_Util',
          1 => '-n/a-',
        ),
        529 =>
        array (
          0 => 'XML_Wddx',
          1 => '-n/a-',
        ),
        530 =>
        array (
          0 => 'XML_XPath',
          1 => '-n/a-',
        ),
        531 =>
        array (
          0 => 'XML_XPath2',
          1 => '-n/a-',
        ),
        532 =>
        array (
          0 => 'XML_XSLT_Wrapper',
          1 => '-n/a-',
        ),
        533 =>
        array (
          0 => 'XML_XUL',
          1 => '-n/a-',
        ),
      ),
    ),
    'cmd' => 'remote-list',
  ),
)
, $log, 'smoog log');

$phpunit->assertNoErrors('smoog');
$e = $command->run('remote-list', array('channel' => 'empty'), array());
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'Retrieving data...0%',
    1 => true,
  ),
  1 =>
  array (
    'info' =>
    array (
      'caption' => 'Channel empty Available packages:',
      'border' => true,
      'headline' =>
      array (
        0 => 'Package',
        1 => 'Version',
      ),
      'channel' => 'empty',
      'data' =>
      array (
        0 =>
        array (
          0 => 'APC',
          1 => '-n/a-',
        ),
      ),
    ),
    'cmd' => 'remote-list',
  )
), $fakelog->getLog(), 'empty log');
$phpunit->assertNoErrors('empty');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
