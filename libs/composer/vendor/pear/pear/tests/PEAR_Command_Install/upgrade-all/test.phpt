--TEST--
upgrade-all command - real-world example from Bug #3388
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$packageDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR;

$reg = &$config->getRegistry();

$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$chan = $reg->getChannel('pecl.php.net');
$chan->setBaseURL('REST1.0', 'http://pecl.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addHtmlConfig('http://pear.php.net/get/Net_Sieve-1.1.1.tgz',        $packageDir . 'Net_Sieve-1.1.1.tgz');
$pearweb->addHtmlConfig('http://pear.php.net/get/File-1.1.0RC5.tgz',          $packageDir . 'File-1.1.0RC5.tgz');
$pearweb->addHtmlConfig('http://pear.php.net/get/file-1.1.0RC5.tgz',          $packageDir . 'File-1.1.0RC5.tgz');
$pearweb->addHtmlConfig('http://pear.php.net/get/file-1.1.0RC5.tgz',          $packageDir . 'File-1.1.0RC5.tgz');
$pearweb->addHtmlConfig('http://pear.php.net/get/Text_Highlighter-0.6.2.tgz', $packageDir . 'Text_Highlighter-0.6.2.tgz');
$pearweb->addHtmlConfig('http://pear.php.net/get/Text_Wiki-0.25.0.tgz',       $packageDir . 'Text_Wiki-0.25.0.tgz');
$pearweb->addHtmlConfig('http://pear.php.net/get/XML_RPC-1.2.0RC6.tgz',       $packageDir . 'XML_RPC-1.2.0RC6.tgz');


$pearweb->addRESTConfig("http://pecl.php.net/rest/p/packages.xml", false, false);

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

$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_tar/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Archive_Tar</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.</d>
 <r xlink:href="/rest/r/archive_tar"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Tar</p>
 <c>pear.php.net</c>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.10-b1</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/deps.1.3.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_zip/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Archive_Zip</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Zip file management class</s>
 <d>This class provides handling of zip files in PHP.
It supports creating, listing, extracting and adding to zip files.</d>
 <r xlink:href="/rest/r/archive_zip"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_zip/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>PHP License</l>
 <s>Creating an authentication system.</s>
 <d>The PEAR::Auth package provides methods for creating an authentication
system using PHP.

Currently it supports the following storage containers to read/write
the login data:

* All databases supported by the PEAR database layer
* All databases supported by the MDB database layer
* All databases supported by the MDB2 database layer
* Plaintext files
* LDAP servers
* POP3 servers
* IMAP servers
* vpopmail accounts
* RADIUS
* SAMBA password files
* SOAP</d>
 <r xlink:href="/rest/r/auth"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Auth</p>
 <c>pear.php.net</c>
 <r><v>1.3.0r3</v><s>beta</s></r>
 <r><v>1.3.0r2</v><s>beta</s></r>
 <r><v>1.3.0r1</v><s>beta</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s></s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth/deps.1.3.0r3.txt", 'a:7:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.9.5";s:8:"optional";s:3:"yes";s:4:"name";s:11:"File_Passwd";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.3";s:8:"optional";s:3:"yes";s:4:"name";s:8:"Net_POP3";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:5;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:4:"MDB2";}i:6;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:11:"Auth_RADIUS";}i:7;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:14:"File_SMBPasswd";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth_enterprise/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth_Enterprise</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>PHP License</l>
 <s>Enterprise quality Authentication and Authorization Service</s>
 <d>As the name implies, this package aims to provide an enterprise level
               authentication and authorization service. There are two parts to this package, the
               service layer which handles requests and a PHP5 client. Support for other clients
               (e.g. PHP4, Java, ASP/VB, etc) is possible further supporting cross-platform
               enterprise needs. Main features are: Web Service-based, implements notion of a
               Provider which is capable of hitting a specific data store (DBMS, LDAP, etc),
               Implements a single credential set across a single provider, 100% OO-PHP with the
               client producing a user object that can be serialized to a PHP session.</d>
 <r xlink:href="/rest/r/auth_enterprise"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_enterprise/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth_http/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth_HTTP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>PHP License</l>
 <s>HTTP authentication</s>
 <d>The PEAR::Auth_HTTP class provides methods for creating an HTTP
authentication system using PHP, that is similar to Apache\'s
realm-based .htaccess authentication.</d>
 <r xlink:href="/rest/r/auth_http"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_http/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Auth_HTTP</p>
 <c>pear.php.net</c>
 <r><v>2.1.6</v><s>stable</s></r>
 <r><v>2.1.6RC1</v><s>beta</s></r>
 <r><v>2.1.4</v><s>stable</s></r>
 <r><v>2.1.3rc1</v><s>beta</s></r>
 <r><v>2.1.1</v><s>beta</s></r>
 <r><v>2.1.0</v><s>beta</s></r>
 <r><v>2.1.0RC2</v><s>beta</s></r>
 <r><v>2.1RC1</v><s>beta</s></r>
 <r><v>2.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_http/deps.2.1.6.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.1.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a2";}s:7:"package";a:3:{s:4:"name";s:4:"Auth";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.2.0";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth_prefmanager/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth_PrefManager</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>PHP License</l>
 <s>Preferences management class</s>
 <d>Preference Manager is a class to handle user preferences in a web application, looking them up in a table
using a combination of their userid, and the preference name to get a value, and (optionally) returning
a default value for the preference if no value could be found for that user.

It is designed to be used alongside the PEAR Auth class, but can be used with anything that allows you
to obtain the user\'s id - including your own code.</d>
 <r xlink:href="/rest/r/auth_prefmanager"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_prefmanager/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Auth_PrefManager</p>
 <c>pear.php.net</c>
 <r><v>1.1.4</v><s>stable</s></r>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.2.31</v><s>stable</s></r>
 <r><v>0.2.3</v><s>devel</s></r>
 <r><v>0.2.2</v><s>devel</s></r>
 <r><v>0.2.1</v><s>devel</s></r>
 <r><v>0.2.0</v><s>devel</s></r>
 <r><v>0.11</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_prefmanager/deps.1.1.4.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.6.0";s:4:"name";s:2:"DB";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth_prefmanager2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth_PrefManager2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>PHP License</l>
 <s>Preferences management class</s>
 <d>Preference Manager is a class to handle user preferences in a web application, looking them up in a table
using a combination of their userid, and the preference name to get a value, and (optionally) returning
a default value for the preference if no value could be found for that user.

Auth_PrefManager2 supports data containers to allow reading/writing with different sources, currently PEAR DB and a simple array based container are supported, although support is planned for an LDAP container as well. If you don\'t need support for different sources, or setting preferences for multiple applications you should probably use Auth_PrefManager instead.</d>
 <r xlink:href="/rest/r/auth_prefmanager2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_prefmanager2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Auth_PrefManager2</p>
 <c>pear.php.net</c>
 <r><v>2.0.0dev1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_prefmanager2/deps.2.0.0dev1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth_radius/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth_RADIUS</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>BSD</l>
 <s>Wrapper Classes for the RADIUS PECL.</s>
 <d>This package provides wrapper-classes for the RADIUS PECL.
There are different Classes for the different authentication methods.
If you are using CHAP-MD5 or MS-CHAP you need also the Crypt_CHAP package.
If you are using MS-CHAP you need also the mhash and mcrypt extension.</d>
 <r xlink:href="/rest/r/auth_radius"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_radius/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Auth_RADIUS</p>
 <c>pear.php.net</c>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_radius/deps.1.0.4.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.4";s:4:"name";s:6:"radius";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth_sasl/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth_SASL</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>BSD</l>
 <s>Abstraction of various SASL mechanism responses</s>
 <d>Provides code to generate responses to common SASL mechanisms, including:
o Digest-MD5
o CramMD5
o Plain
o Anonymous
o Login (Pseudo mechanism)</d>
 <r xlink:href="/rest/r/auth_sasl"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_sasl/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Auth_SASL</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_sasl/deps.1.0.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/benchmark/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Benchmark</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Benchmarking">Benchmarking</ca>
 <l>PHP License</l>
 <s>Framework to benchmark PHP scripts or function calls.</s>
 <d>Framework to benchmark PHP scripts or function calls.</d>
 <r xlink:href="/rest/r/benchmark"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/benchmark/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Benchmark</p>
 <c>pear.php.net</c>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.2beta1</v><s>beta</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/benchmark/deps.1.2.3.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:6:"bcmath";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/cache/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Cache</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Caching">Caching</ca>
 <l>PHP License</l>
 <s>Framework for caching of arbitrary data.</s>
 <d>With the PEAR Cache you can cache the result of certain function
calls, as well as the output of a whole script run or share data
between applications.</d>
 <r xlink:href="/rest/r/cache"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/cache/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Cache</p>
 <c>pear.php.net</c>
 <r><v>1.5.5RC4</v><s>beta</s></r>
 <r><v>1.5.5RC3</v><s>beta</s></r>
 <r><v>1.5.5RC2</v><s>beta</s></r>
 <r><v>1.5.5RC1</v><s>beta</s></r>
 <r><v>1.5.4</v><s>stable</s></r>
 <r><v>1.5.3</v><s>stable</s></r>
 <r><v>1.5.2</v><s>stable</s></r>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5</v><s>stable</s></r>
 <r><v>1.4</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s></s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/cache/deps.1.5.5RC4.txt", 'a:2:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.5";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:12:"HTTP_Request";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/cache_lite/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Cache_Lite</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Caching">Caching</ca>
 <l>lgpl</l>
 <s>Fast and Safe little cache system</s>
 <d>This package is a little cache system optimized for file containers. It is fast and safe (because it uses file locking and/or anti-corruption tests).</d>
 <r xlink:href="/rest/r/cache_lite"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/cache_lite/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Cache_Lite</p>
 <c>pear.php.net</c>
 <r><v>1.5.2</v><s>stable</s></r>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5.0</v><s>beta</s></r>
 <r><v>1.4.1</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.4.0beta1</v><s>beta</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1.1</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/cache_lite/deps.1.5.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/calendar/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Calendar</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Date+and+Time">Date and Time</ca>
 <l>PHP</l>
 <s>A package for building Calendar data structures (irrespective of output)</s>
 <d>Calendar provides an API for building Calendar data structures. Using
the simple iterator and it\'s &quot;query&quot; API, a user interface can easily be
built on top of the calendar data structure, at the same time easily connecting it
to some kind of underlying data store, where &quot;event&quot; information is
being held.

It provides different calculation &quot;engines&quot; the default being based on
Unix timestamps (offering fastest performance) with an alternative using PEAR::Date
which extends the calendar past the limitations of Unix timestamps. Other engines
should be implementable for other types of calendar (e.g. a Chinese Calendar based
on lunar cycles).</d>
 <r xlink:href="/rest/r/calendar"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/calendar/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Calendar</p>
 <c>pear.php.net</c>
 <r><v>0.5.2</v><s>beta</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/calendar/deps.0.5.2.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.0.5";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:4:"Date";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/codegen/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>CodeGen</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Tools+and+Utilities">Tools and Utilities</ca>
 <l>PHP</l>
 <s>Tool to create Code generaters that operate on XML descriptions</s>
 <d>This is an \'abstract\' package, it provides the base
framework for applications like CodeGen_PECL and
CodeGen_MySqlUDF (not released yet).</d>
 <r xlink:href="/rest/r/codegen"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>CodeGen</p>
 <c>pear.php.net</c>
 <r><v>1.0.0rc1</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
 <r><v>0.9.0rc1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen/deps.1.0.0rc1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:1:"5";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.3";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:14:"Console_Getopt";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/codegen_mysql_udf/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>CodeGen_MySQL_UDF</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Tools+and+Utilities">Tools and Utilities</ca>
 <l>PHP</l>
 <s>Tool to generate MySQL UDF extensions from an XML description</s>
 <d>UDF_Gen is a code generator for MySQL User Defined Function (UDF)
extensions similar to PECL_Gen for PHP.
It reads in configuration options, function prototypes and code fragments
from an XML description file and generates a complete ready-to-compile
UDF extension.
Preliminary documentation can be found here:
http://talks.php.net/show/UDF_Gen</d>
 <r xlink:href="/rest/r/codegen_mysql_udf"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen_mysql_udf/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>CodeGen_MySQL_UDF</p>
 <c>pear.php.net</c>
 <r><v>0.9.2dev</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen_mysql_udf/deps.0.9.2dev.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"5.0";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.9";s:4:"name";s:7:"CodeGen";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/codegen_pecl/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>CodeGen_PECL</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Tools+and+Utilities">Tools and Utilities</ca>
 <l>PHP</l>
 <s>Tool to generate PECL extensions from an XML description</s>
 <d>CodeGen_PECL (formerly known as PECL_Gen) is a pure PHP replacement
for the ext_skel shell script that comes with the PHP 4 source.
It reads in configuration options, function prototypes and code fragments
from an XML description file and generates a complete ready-to-compile
PECL extension.</d>
 <r xlink:href="/rest/r/codegen_pecl"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen_pecl/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>CodeGen_PECL</p>
 <c>pear.php.net</c>
 <r><v>1.0.0rc1</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
 <r><v>0.9.0rc5</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/codegen_pecl/deps.1.0.0rc1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:1:"5";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.9";s:4:"name";s:7:"CodeGen";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/config/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Config</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Configuration">Configuration</ca>
 <l>PHP License</l>
 <s>Your configurations swiss-army knife.</s>
 <d>The Config package provides methods for configuration manipulation.
* Creates configurations from scratch
* Parses and outputs different formats (XML, PHP, INI, Apache...)
* Edits existing configurations
* Converts configurations to other formats
* Allows manipulation of sections, comments, directives...
* Parses configurations into a tree structure
* Provides XPath like access to directives</d>
 <r xlink:href="/rest/r/config"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/config/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Config</p>
 <c>pear.php.net</c>
 <r><v>1.10.4</v><s>stable</s></r>
 <r><v>1.10.3</v><s>stable</s></r>
 <r><v>1.10.2</v><s>stable</s></r>
 <r><v>1.10.1</v><s>stable</s></r>
 <r><v>1.10</v><s>stable</s></r>
 <r><v>1.9</v><s>stable</s></r>
 <r><v>1.8.1</v><s>stable</s></r>
 <r><v>1.8</v><s>stable</s></r>
 <r><v>1.7</v><s>stable</s></r>
 <r><v>1.6</v><s>stable</s></r>
 <r><v>1.5</v><s>stable</s></r>
 <r><v>1.4</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>beta</s></r>
 <r><v>1.0</v><s>beta</s></r>
 <r><v>0.3.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/config/deps.1.10.4.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:10:"XML_Parser";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:8:"XML_Util";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/console_color/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Console_Color</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Console">Console</ca>
 <l>PHP</l>
 <s>This Class allows you to easily use ANSI console colors in your application.</s>
 <d>You can use Console_Color::convert to transform colorcodes like %r into ANSI
control codes. print Console_Color::convert(&quot;%rHello World!%n&quot;); would print
&quot;Hello World&quot; in red, for example.</d>
 <r xlink:href="/rest/r/console_color"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_color/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_Color</p>
 <c>pear.php.net</c>
 <r><v>0.0.3</v><s>beta</s></r>
 <r><v>0.0.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_color/deps.0.0.3.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/console_getargs/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Console_Getargs</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Console">Console</ca>
 <l>PHP License</l>
 <s>A command-line arguments parser</s>
 <d>The Console_Getargs package implements a Command Line arguments and
parameters parser for your CLI applications. It performs some basic
arguments validation and automatically creates a formatted help text,
based on the given configuration.</d>
 <r xlink:href="/rest/r/console_getargs"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getargs/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_Getargs</p>
 <c>pear.php.net</c>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getargs/deps.1.3.0.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/console_getopt/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Console_Getopt</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Console">Console</ca>
 <l>PHP License</l>
 <s>Command-line option parser</s>
 <d>This is a PHP implementation of &quot;getopt&quot; supporting both
short and long options.</d>
 <r xlink:href="/rest/r/console_getopt"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_Getopt</p>
 <c>pear.php.net</c>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.11</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/deps.1.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/console_progressbar/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Console_ProgressBar</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Console">Console</ca>
 <l>PHP</l>
 <s>This class provides you with an easy-to-use interface to progress bars.</s>
 <d>The class allows you to display progress bars in your terminal. You can use
this for displaying the status of downloads or other tasks that take some
time.</d>
 <r xlink:href="/rest/r/console_progressbar"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_progressbar/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_ProgressBar</p>
 <c>pear.php.net</c>
 <r><v>0.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_progressbar/deps.0.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/console_table/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Console_Table</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Console">Console</ca>
 <l>BSD</l>
 <s>Class that makes it easy to build console style tables</s>
 <d>Provides methods such as addRow(), insertRow(), addCol() etc to build Console
tables. Can be with or without headers, and has various configurable options.</d>
 <r xlink:href="/rest/r/console_table"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_table/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_Table</p>
 <c>pear.php.net</c>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.8</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_table/deps.1.0.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/contact_addressbook/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Contact_AddressBook</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>BSD License</l>
 <s>Address book export-import class</s>
 <d>Package provide export-import address book mechanism. Contact_AddressBook refers to needed structure, convert the various address book structure format into it, then you can easily to store it into file, database or another storage media.

Current supported formats:
1. Ms Windows Address Book (WAB) CSV
2. Ms Outlook CSV
3. Mozilla Mailer/Thunderbird/Netscape Mailer CSV
4. KMail Address Book CSV
5. Yahoo! Mail Address Book CSV
6. Palm Pilot Address Book CSV
7. Eudora Address Book</d>
 <r xlink:href="/rest/r/contact_addressbook"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/contact_addressbook/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Contact_AddressBook</p>
 <c>pear.php.net</c>
 <r><v>0.4.0alpha1</v><s>alpha</s></r>
 <r><v>0.1.0dev1</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/contact_addressbook/deps.0.4.0alpha1.txt", 'a:3:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.1";s:8:"optional";s:2:"no";s:4:"name";s:4:"File";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"2.0.1";s:8:"optional";s:2:"no";s:4:"name";s:20:"Net_UserAgent_Detect";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/contact_vcard_build/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Contact_Vcard_Build</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Build (create) and fetch vCard 2.1 and 3.0 text blocks.</s>
 <d>Allows you to programmatically create a vCard, version 2.1 or 3.0, and fetch the vCard text.</d>
 <r xlink:href="/rest/r/contact_vcard_build"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/contact_vcard_build/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Contact_Vcard_Build</p>
 <c>pear.php.net</c>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/contact_vcard_build/deps.1.1.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/contact_vcard_parse/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Contact_Vcard_Parse</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Parse vCard 2.1 and 3.0 files.</s>
 <d>Allows you to parse vCard files and text blocks, and get back an array of the elements of each vCard in the file or text.</d>
 <r xlink:href="/rest/r/contact_vcard_parse"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/contact_vcard_parse/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Contact_Vcard_Parse</p>
 <c>pear.php.net</c>
 <r><v>1.31.0</v><s>stable</s></r>
 <r><v>1.30</v><s>stable</s></r>
 <r><v>1.21</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/contact_vcard_parse/deps.1.31.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/crypt_blowfish/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Crypt_Blowfish</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Encryption">Encryption</ca>
 <l>PHP</l>
 <s>Allows for quick two-way blowfish encryption without requiring the Mcrypt PHP extension.</s>
 <d>This package allows you to preform two-way blowfish on the fly using only PHP. This package does not require the Mcrypt PHP extension to work.</d>
 <r xlink:href="/rest/r/crypt_blowfish"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_blowfish/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Crypt_Blowfish</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.8.1</v><s>beta</s></r>
 <r><v>0.7.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_blowfish/deps.1.0.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/crypt_cbc/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Crypt_CBC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Encryption">Encryption</ca>
 <l>PHP 2.02</l>
 <s>A class to emulate Perl\'s Crypt::CBC module.</s>
 <d>A class to emulate Perl\'s Crypt::CBC module.</d>
 <r xlink:href="/rest/r/crypt_cbc"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_cbc/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Crypt_CBC</p>
 <c>pear.php.net</c>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
 <r><v>0.2</v><s>stable</s></r>
 <r><v>0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_cbc/deps.0.4.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/crypt_chap/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Crypt_CHAP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Encryption">Encryption</ca>
 <l>BSD</l>
 <s>Generating CHAP packets.</s>
 <d>This package provides Classes for generating CHAP packets.
Currently these types of CHAP are supported:
* CHAP-MD5
* MS-CHAPv1
* MS-CHAPv2
For MS-CHAP the mhash and mcrypt extensions must be loaded.</d>
 <r xlink:href="/rest/r/crypt_chap"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_chap/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Crypt_CHAP</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.8.6</v><s>alpha</s></r>
 <r><v>0.8.5</v><s>alpha</s></r>
 <r><v>0.8.2</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_chap/deps.1.0.0.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:5:"mhash";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:6:"mcrypt";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/crypt_crypt/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Crypt_Crypt</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Encryption">Encryption</ca>
 <l>BSD</l>
 <s>Abstraction class for encryption algorithms</s>
 <d>A generic class that allows a user to use a single set of functions to perform encryption and decryption.  The class prefers to use native extensions like mcrypt, but will automatically attempt to load crypto modules written in php if the requested algorithm is unsupported natively or by extensions.

**NEWS**  After a long hiatus, this is an active project again.  Updates will be posted as I get back up to speed.  Please contact the project lead with questions or suggestions.</d>
 <r xlink:href="/rest/r/crypt_crypt"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_crypt/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/crypt_hmac/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Crypt_HMAC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Encryption">Encryption</ca>
 <l>PHP</l>
 <s>A class to calculate RFC 2104 compliant hashes.</s>
 <d>A class to calculate RFC 2104 compliant hashes.</d>
 <r xlink:href="/rest/r/crypt_hmac"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_hmac/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Crypt_HMAC</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.9</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_hmac/deps.1.0.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/crypt_rc4/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Crypt_RC4</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Encryption">Encryption</ca>
 <l>PHP</l>
 <s>Encryption class for RC4 encryption</s>
 <d>RC4 encryption class</d>
 <r xlink:href="/rest/r/crypt_rc4"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_rc4/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Crypt_RC4</p>
 <c>pear.php.net</c>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_rc4/deps.1.0.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/crypt_rsa/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Crypt_RSA</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Encryption">Encryption</ca>
 <l>PHP</l>
 <s>Provides RSA-like key generation, encryption/decryption, signing and signature checking</s>
 <d>This package allows you to use two-key strong cryptography like RSA with arbitrary key length.
It uses one of the following extensions for math calculations:
 - PECL big_int extension ( http://pecl.php.net/packages/big_int ) version greater than or equal to 1.0.3
 - PHP GMP extension ( http://php.net/gmp )
 - PHP BCMath extension ( http://php.net/manual/en/ref.bc.php ) for both PHP4 and PHP5</d>
 <r xlink:href="/rest/r/crypt_rsa"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_rsa/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Crypt_RSA</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0RC6</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_rsa/deps.1.0.0.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:6:"bcmath";}i:2;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"gmp";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.3";s:8:"optional";s:3:"yes";s:4:"name";s:7:"big_int";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/crypt_xtea/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Crypt_Xtea</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Encryption">Encryption</ca>
 <l>PHP 2.02</l>
 <s>A class that implements the Tiny Encryption Algorithm (TEA) (New Variant).</s>
 <d>A class that implements the Tiny Encryption Algorithm (TEA) (New Variant).
This class does not depend on mcrypt.
Since the latest fix handles properly dealing with unsigned integers,
which where solved by introducing new functions _rshift(), _add(), the
speed of the encryption and decryption has radically dropped.
Do not use for large amounts of data.
Original code from http://vader.brad.ac.uk/tea/source.shtml#new_ansi
Currently to be found at: http://www.simonshepherd.supanet.com/source.shtml#new_ansi</d>
 <r xlink:href="/rest/r/crypt_xtea"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_xtea/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Crypt_Xtea</p>
 <c>pear.php.net</c>
 <r><v>1.1.0RC4</v><s>beta</s></r>
 <r><v>1.1.0RC3</v><s>beta</s></r>
 <r><v>1.1.0RC2</v><s>beta</s></r>
 <r><v>1.1.0RC1</v><s>beta</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/crypt_xtea/deps.1.1.0RC4.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/date/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Date</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Date+and+Time">Date and Time</ca>
 <l>PHP License</l>
 <s>Date and Time Zone Classes</s>
 <d>Generic classes for representation and manipulation of
dates, times and time zones without the need of timestamps,
which is a huge limitation for php programs.  Includes time zone data,
time zone conversions and many date/time conversions.
It does not rely on 32-bit system date stamps, so
you can display calendars and compare dates that date
pre 1970 and post 2038. This package also provides a class
to convert date strings between Gregorian and Human calendar formats.</d>
 <r xlink:href="/rest/r/date"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Date</p>
 <c>pear.php.net</c>
 <r><v>1.4.3</v><s>stable</s></r>
 <r><v>1.4.2</v><s>stable</s></r>
 <r><v>1.4.1</v><s>stable</s></r>
 <r><v>1.4</v><s>stable</s></r>
 <r><v>1.4rc1</v><s>beta</s></r>
 <r><v>1.3.1beta</v><s>beta</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date/deps.1.4.3.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.2";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/date_holidays/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Date_Holidays</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Date+and+Time">Date and Time</ca>
 <l>PHP License</l>
 <s>Driver based class to calculate holidays.</s>
 <d>Date_Holidays helps you calculating the dates and titles of holidays and other special celebrations. The calculation is driver-based so it is easy to add new drivers that calculate a country\'s holidays. The methods of the class can be used to get a holiday\'s date and title in various languages.</d>
 <r xlink:href="/rest/r/date_holidays"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Date_Holidays</p>
 <c>pear.php.net</c>
 <r><v>0.13.0</v><s>alpha</s></r>
 <r><v>0.12.0</v><s>alpha</s></r>
 <r><v>0.11.0</v><s>alpha</s></r>
 <r><v>0.10.0</v><s>alpha</s></r>
 <r><v>0.9.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/date_holidays/deps.0.13.0.txt", 'a:2:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"Date";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP License</l>
 <s>Database Abstraction Layer</s>
 <d>DB is a database abstraction layer providing:
* an OO-style query API
* portability features that make programs written for one DBMS work with other DBMS\'s
* a DSN (data source name) format for specifying database servers
* prepare/execute (bind) emulation for databases that don\'t support it natively
* a result object for each query response
* portable error codes
* sequence emulation
* sequential and non-sequential row fetching as well as bulk fetching
* formats fetched rows as associative arrays, ordered arrays or objects
* row limit support
* transactions support
* table information interface
* DocBook and phpDocumentor API documentation

DB layers itself on top of PHP\'s existing
database extensions.

Drivers for the following extensions pass
the complete test suite and provide
interchangeability when all of DB\'s
portability options are enabled:

  fbsql, ibase, informix, msql, mssql,
  mysql, mysqli, oci8, odbc, pgsql,
  sqlite and sybase.

There is also a driver for the dbase
extension, but it can\'t be used
interchangeably because dbase doesn\'t
support many standard DBMS features.

DB is compatible with both PHP 4 and PHP 5.</d>
 <r xlink:href="/rest/r/db"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB</p>
 <c>pear.php.net</c>
 <r><v>1.7.6</v><s>stable</s></r>
 <r><v>1.7.5</v><s>stable</s></r>
 <r><v>1.7.4</v><s>stable</s></r>
 <r><v>1.7.3</v><s>stable</s></r>
 <r><v>1.7.2</v><s>stable</s></r>
 <r><v>1.7.1</v><s>stable</s></r>
 <r><v>1.7.0</v><s>stable</s></r>
 <r><v>1.7.0RC1</v><s>beta</s></r>
 <r><v>1.6.8</v><s>stable</s></r>
 <r><v>1.6.7</v><s>stable</s></r>
 <r><v>1.6.6</v><s>stable</s></r>
 <r><v>1.6.5</v><s>stable</s></r>
 <r><v>1.6.4</v><s>stable</s></r>
 <r><v>1.6.3</v><s>stable</s></r>
 <r><v>1.6.2</v><s>stable</s></r>
 <r><v>1.6.1</v><s>stable</s></r>
 <r><v>1.6.0</v><s>stable</s></r>
 <r><v>1.6.0RC6</v><s>stable</s></r>
 <r><v>1.6.0RC5</v><s>beta</s></r>
 <r><v>1.6.0RC4</v><s>beta</s></r>
 <r><v>1.6.0RC3</v><s>beta</s></r>
 <r><v>1.6.0RC2</v><s>beta</s></r>
 <r><v>1.6.0RC1</v><s>beta</s></r>
 <r><v>1.5.0RC2</v><s>stable</s></r>
 <r><v>1.5.0RC1</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.4b1</v><s>beta</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db/deps.1.7.6.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:4:"name";s:4:"PEAR";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/dba/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DBA</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>LGPL</l>
 <s>Berkely-style database abstraction class</s>
 <d>DBA is a wrapper for the php DBA functions. It includes a file-based emulator and provides a uniform, object-based interface for the Berkeley-style database systems.</d>
 <r xlink:href="/rest/r/dba"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/dba/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DBA</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>1.0-rc1</v><s>stable</s></r>
 <r><v>0.9.5</v><s>beta</s></r>
 <r><v>0.9.4</v><s>beta</s></r>
 <r><v>0.9.3</v><s>beta</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.9</v><s>beta</s></r>
 <r><v>0.18</v><s>devel</s></r>
 <r><v>0.17</v><s>devel</s></r>
 <r><v>0.16</v><s>beta</s></r>
 <r><v>0.15</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/dba/deps.1.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/dba_relational/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DBA_Relational</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>LGPL</l>
 <s>Berkely-style database abstraction class</s>
 <d>Table management extension to DBA</d>
 <r xlink:href="/rest/r/dba_relational"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/dba_relational/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DBA_Relational</p>
 <c>pear.php.net</c>
 <r><v>0.19</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/dba_relational/deps.0.19.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:8:"4.0.4pl1";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:3:"DBA";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_ado/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_ado</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>LGPL</l>
 <s>DB driver which use MS ADODB library</s>
 <d>DB_ado is a database independent query interface definition for Microsoft\'s ADODB library using PHP\'s COM extension.
This class allows you to connect to different data sources like MS Access, MS SQL Server, Oracle and other RDBMS on a Win32 operating system.
Moreover the possibility exists to use MS Excel spreadsheets, XML, text files and other not relational data as data source.</d>
 <r xlink:href="/rest/r/db_ado"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_ado/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_ado</p>
 <c>pear.php.net</c>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_ado/deps.1.3.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"com";}i:3;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:2:"DB";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_dataobject/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_DataObject</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP License</l>
 <s>An SQL Builder, Object Interface to Database Tables</s>
 <d>DataObject performs 2 tasks:
  1. Builds SQL statements based on the objects vars and the builder methods.
  2. acts as a datastore for a table row.
  The core class is designed to be extended for each of your tables so that you put the
  data logic inside the data classes.
  included is a Generator to make your configuration files and your base classes.</d>
 <r xlink:href="/rest/r/db_dataobject"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_dataobject/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_DataObject</p>
 <c>pear.php.net</c>
 <r><v>1.7.15</v><s>stable</s></r>
 <r><v>1.7.14</v><s>stable</s></r>
 <r><v>1.7.13</v><s>stable</s></r>
 <r><v>1.7.12</v><s>stable</s></r>
 <r><v>1.7.11</v><s>stable</s></r>
 <r><v>1.7.10</v><s>stable</s></r>
 <r><v>1.7.9</v><s>stable</s></r>
 <r><v>1.7.8</v><s>stable</s></r>
 <r><v>1.7.7</v><s>stable</s></r>
 <r><v>1.7.6</v><s>stable</s></r>
 <r><v>1.7.5</v><s>stable</s></r>
 <r><v>1.7.2</v><s>stable</s></r>
 <r><v>1.7.1</v><s>stable</s></r>
 <r><v>1.7.0</v><s>stable</s></r>
 <r><v>1.6.1</v><s>stable</s></r>
 <r><v>1.6.0</v><s>stable</s></r>
 <r><v>1.5.3</v><s>stable</s></r>
 <r><v>1.5.2</v><s>stable</s></r>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5</v><s>stable</s></r>
 <r><v>1.4</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.19</v><s>stable</s></r>
 <r><v>0.18</v><s>stable</s></r>
 <r><v>0.17</v><s>stable</s></r>
 <r><v>0.16</v><s>stable</s></r>
 <r><v>0.15</v><s>stable</s></r>
 <r><v>0.14</v><s>stable</s></r>
 <r><v>0.13</v><s>stable</s></r>
 <r><v>0.12</v><s>stable</s></r>
 <r><v>0.11</v><s>stable</s></r>
 <r><v>0.10</v><s>stable</s></r>
 <r><v>0.9</v><s>stable</s></r>
 <r><v>0.8</v><s>stable</s></r>
 <r><v>0.6</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_dataobject/deps.1.7.15.txt", 'a:4:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.3";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.7.0";s:4:"name";s:2:"DB";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.1.1";s:8:"optional";s:3:"yes";s:4:"name";s:8:"Validate";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.4.3";s:4:"name";s:4:"Date";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_dataobject_formbuilder/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_DataObject_FormBuilder</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP License</l>
 <s>Class to automatically build HTML_QuickForm objects from a DB_DataObject-derived class</s>
 <d>DB_DataObject_FormBuilder will aid you in rapid application development using the packages DB_DataObject and HTML_QuickForm. For having a quick but working prototype of your application, simply model the database, run DataObject\'s createTable script over it and write a script that passes one of the resulting objects to the FormBuilder class. The FormBuilder will automatically generate a simple but working HTML_QuickForm object that you can use to test your application. It also provides a processing method that will automatically detect if an insert() or update() command has to be executed after the form has been submitted. If you have set up DataObject\'s links.ini file correctly, it will also automatically detect if a table field is a foreign key and will populate a selectbox with the linked table\'s entries. There are many optional parameters that you can place in your DataObjects.ini or in the properties of your derived classes, that you can use to fine-tune the form-generation, gradually turning the prototypes into fully-featured forms, and you can take control at any stage of the process.</d>
 <r xlink:href="/rest/r/db_dataobject_formbuilder"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_dataobject_formbuilder/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_DataObject_FormBuilder</p>
 <c>pear.php.net</c>
 <r><v>0.18.1</v><s>beta</s></r>
 <r><v>0.18.0</v><s>beta</s></r>
 <r><v>0.17.2</v><s>beta</s></r>
 <r><v>0.17.1</v><s>beta</s></r>
 <r><v>0.17.0</v><s>alpha</s></r>
 <r><v>0.16.0</v><s>alpha</s></r>
 <r><v>0.15.0</v><s>beta</s></r>
 <r><v>0.14.0</v><s>beta</s></r>
 <r><v>0.13.3</v><s>beta</s></r>
 <r><v>0.13.2</v><s>beta</s></r>
 <r><v>0.13.1</v><s>beta</s></r>
 <r><v>0.13.0</v><s>beta</s></r>
 <r><v>0.12.1</v><s>beta</s></r>
 <r><v>0.12.0</v><s>beta</s></r>
 <r><v>0.11.5</v><s>beta</s></r>
 <r><v>0.11.4</v><s>beta</s></r>
 <r><v>0.11.3</v><s>beta</s></r>
 <r><v>0.11.2</v><s>beta</s></r>
 <r><v>0.11.1</v><s>beta</s></r>
 <r><v>0.10.3</v><s>beta</s></r>
 <r><v>0.10.2</v><s>beta</s></r>
 <r><v>0.10.1</v><s>beta</s></r>
 <r><v>0.10.0</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
 <r><v>0.8.2</v><s>beta</s></r>
 <r><v>0.8.1</v><s>beta</s></r>
 <r><v>0.8</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_dataobject_formbuilder/deps.0.18.1.txt", 'a:2:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"4.3.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:2:{i:0;a:2:{s:4:"name";s:13:"DB_DataObject";s:7:"channel";s:12:"pear.php.net";}i:1;a:3:{s:4:"name";s:14:"HTML_QuickForm";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"3.2.4";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:2:{s:4:"name";s:4:"Date";s:7:"channel";s:12:"pear.php.net";}i:1;a:2:{s:4:"name";s:10:"HTML_Table";s:7:"channel";s:12:"pear.php.net";}}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_ldap/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_ldap</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>LGPL</l>
 <s>DB interface to LDAP server</s>
 <d>The PEAR::DB_ldap class provides a DB compliant interface to LDAP servers</d>
 <r xlink:href="/rest/r/db_ldap"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_ldap/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_ldap</p>
 <c>pear.php.net</c>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_ldap/deps.1.1.1.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:4:"PEAR";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:2:"DB";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_ldap2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_ldap2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>LGPL</l>
 <s>DB drivers for LDAP v2 and v3 database</s>
 <d>DB_ldap2 and DB_ldap3 classes extend DB_common to provide DB
compliant access to LDAP servers with protocol version 2 and 3. The
drivers provide common DB interface as much as possible and support
prepare/execute statements.</d>
 <r xlink:href="/rest/r/db_ldap2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_ldap2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_ldap2</p>
 <c>pear.php.net</c>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>devel</s></r>
 <r><v>0.1</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_ldap2/deps.0.4.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"ldap";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:2:"DB";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_nestedset/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_NestedSet</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP License</l>
 <s>API to build and query nested sets</s>
 <d>DB_NestedSet let\'s you create trees with infinite depth
inside a relational database.
The package provides a way to
o create/update/delete nodes
o query nodes, trees and subtrees
o copy (clone) nodes, trees and subtrees
o move nodes, trees and subtrees
o Works with PEAR::DB, PEAR::MDB, PEAR::MDB2
o output the tree with
  - PEAR::HTML_TreeMenu
  - TigraMenu (http://www.softcomplex.com/products/tigra_menu/)
  - CoolMenus (http://www.dhtmlcentral.com/projects/coolmenus/)
  - PEAR::Image_GraphViz (http://pear.php.net/package/Image_GraphViz)
  - PEAR::HTML_Menu</d>
 <r xlink:href="/rest/r/db_nestedset"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_nestedset/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_NestedSet</p>
 <c>pear.php.net</c>
 <r><v>1.3.6</v><s>beta</s></r>
 <r><v>1.3.5</v><s>beta</s></r>
 <r><v>1.3.4</v><s>beta</s></r>
 <r><v>1.3.3</v><s>beta</s></r>
 <r><v>1.3.2</v><s>beta</s></r>
 <r><v>1.3.1</v><s>beta</s></r>
 <r><v>1.3</v><s>beta</s></r>
 <r><v>1.2.4</v><s>stable</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1-beta</v><s>beta</s></r>
 <r><v>1.0-beta</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_nestedset/deps.1.3.6.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_odbtp/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_odbtp</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP</l>
 <s>DB interface for ODBTP</s>
 <d>DB_odbtp is a PEAR DB driver that uses the ODBTP extension to connect to a database.
It can be used to remotely access any Win32-ODBC accessible database from any platform.</d>
 <r xlink:href="/rest/r/db_odbtp"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_odbtp/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_odbtp</p>
 <c>pear.php.net</c>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1RC1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_odbtp/deps.1.0.3.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:5:"odbtp";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:2:"DB";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_pager/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_Pager</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>LGPL</l>
 <s>Retrieve and return information of database result sets</s>
 <d>This class handles all the stuff needed for displaying
paginated results from a database query of Pear DB.
including fetching only the needed rows and giving extensive information
for helping build an HTML or GTK query result display.</d>
 <r xlink:href="/rest/r/db_pager"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_pager/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_Pager</p>
 <c>pear.php.net</c>
 <r><v>0.7</v><s>stable</s></r>
 <r><v>0.6</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_pager/deps.0.7.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_querytool/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_QueryTool</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP</l>
 <s>An OO-interface for easily retrieving and modifying data in a DB.</s>
 <d>This package is an OO-abstraction to the SQL-Query language, it provides methods such
as setWhere, setOrder, setGroup, setJoin, etc. to easily build queries.
It also provides an easy to learn interface that interacts nicely with HTML-forms using
arrays that contain the column data, that shall be updated/added in a DB.
This package bases on an SQL-Builder which lets you easily build
SQL-Statements and execute them.</d>
 <r xlink:href="/rest/r/db_querytool"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_querytool/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_QueryTool</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.11.1</v><s>stable</s></r>
 <r><v>0.10.1</v><s>stable</s></r>
 <r><v>0.9.8</v><s>stable</s></r>
 <r><v>0.9.7</v><s>stable</s></r>
 <r><v>0.9.6</v><s>stable</s></r>
 <r><v>0.9.5</v><s>stable</s></r>
 <r><v>0.9.4</v><s>stable</s></r>
 <r><v>0.9.3</v><s>stable</s></r>
 <r><v>0.9.2</v><s>stable</s></r>
 <r><v>0.9.1</v><s>stable</s></r>
 <r><v>0.9</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_querytool/deps.1.0.1.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:2:"DB";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.7";s:4:"name";s:3:"Log";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_sqlite_tools/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_Sqlite_Tools</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>DB_Sqlite_Tools is an object oriented interface to effectively manage and backup Sqlite databases.</s>
 <d>DB_Sqlite_Tools is an object oriented interface to effectively manage and backup Sqlite databases.It extends the existing functionality by providing a comprehensive solution for database backup, live
  replication, export in XML format, performance optmization and other functionalities like the insertion and retrieval of encrypted data from an Sqlite database without any external extension.</d>
 <r xlink:href="/rest/r/db_sqlite_tools"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_sqlite_tools/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_Sqlite_Tools</p>
 <c>pear.php.net</c>
 <r><v>0.1.3</v><s>alpha</s></r>
 <r><v>0.1.2</v><s>alpha</s></r>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_sqlite_tools/deps.0.1.3.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db_table/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB_Table</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>LGPL</l>
 <s>Builds on PEAR DB to abstract datatypes and automate table creation, data validation, insert, update, delete, and select; combines these with PEAR HTML_QuickForm to automatically generate input forms that match the table column definitions.</s>
 <d>Builds on PEAR DB to abstract datatypes and automate table creation, data validation, insert, update, delete, and select; combines these with PEAR HTML_QuickForm to automatically generate input forms that match the table column definitions.</d>
 <r xlink:href="/rest/r/db_table"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_table/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB_Table</p>
 <c>pear.php.net</c>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.23.0</v><s>beta</s></r>
 <r><v>0.22.0</v><s>beta</s></r>
 <r><v>0.21.2</v><s>alpha</s></r>
 <r><v>0.21.1</v><s>alpha</s></r>
 <r><v>0.21</v><s>alpha</s></r>
 <r><v>0.18</v><s>alpha</s></r>
 <r><v>0.17</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db_table/deps.1.2.1.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:2:"DB";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"Date";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:14:"HTML_QuickForm";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/event_dispatcher/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Event_Dispatcher</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Event">Event</ca>
 <l>BSD License</l>
 <s>Dispatch notifications using PHP callbacks</s>
 <d>The Event_Dispatcher acts as a notification dispatch table.
It is used to notify other objects of interesting things. This
information is encapsulated in Event_Notification objects. Client
objects register themselves with the Event_Dispatcher as observers of
specific notifications posted by other objects. When an event occurs,
an object posts an appropriate notification to the Event_Dispatcher.
The Event_Dispatcher dispatches a message to each registered
observer, passing the notification as the sole argument.</d>
 <r xlink:href="/rest/r/event_dispatcher"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/event_dispatcher/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Event_Dispatcher</p>
 <c>pear.php.net</c>
 <r><v>0.9.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/event_dispatcher/deps.0.9.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+System">File System</ca>
 <l>PHP</l>
 <s>Common file and directory routines</s>
 <d>Provides easy access to read/write to files along with
some common routines to deal with paths. Also provides
interface for handling CSV files.</d>
 <r xlink:href="/rest/r/file"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File</p>
 <c>pear.php.net</c>
 <r><v>1.1.0RC5</v><s>beta</s></r>
 <r><v>1.1.0RC4</v><s>beta</s></r>
 <r><v>1.1.0RC3</v><s>beta</s></r>
 <r><v>1.1.0RC2</v><s>beta</s></r>
 <r><v>1.1.0RC1</v><s>beta</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file/deps.1.2.2.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:3:"yes";}i:3;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"pcre";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_archive/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_Archive</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>LGPL</l>
 <s>File_Archive will let you manipulate easily the tar, gz, tgz, bz2, tbz, zip, ar (or deb) files</s>
 <d>This library is strongly object oriented. It makes it very easy to use, writing simple code, yet the library is very powerfull.
It lets you easily read or generate tar, gz, tgz, bz2, tbz, zip, ar (or deb) archives to files, memory, mail or standard output.
See http://poocl.la-grotte.org for a tutorial</d>
 <r xlink:href="/rest/r/file_archive"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_archive/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_Archive</p>
 <c>pear.php.net</c>
 <r><v>1.5.3</v><s>stable</s></r>
 <r><v>1.5.2</v><s>stable</s></r>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5.0</v><s>stable</s></r>
 <r><v>1.4.1</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.3.0</v><s>beta</s></r>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_archive/deps.1.5.3.txt", 'a:5:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:9:"MIME_Type";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:9:"Mail_Mime";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:4:"Mail";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.5.0";s:8:"optional";s:3:"yes";s:4:"name";s:10:"Cache_Lite";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_bittorrent/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_Bittorrent</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Decode and Encode data in Bittorrent format</s>
 <d>This package consists of three classes which handles the encoding and decoding of data in Bittorrent format.
    You can also extract useful informations from .torrent files, create .torrent files and query the torrent\'s scrape page to get its statistics.</d>
 <r xlink:href="/rest/r/file_bittorrent"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_bittorrent/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_Bittorrent</p>
 <c>pear.php.net</c>
 <r><v>1.0.0RC2</v><s>beta</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.1.8</v><s>beta</s></r>
 <r><v>0.1.7</v><s>beta</s></r>
 <r><v>0.1.6</v><s>beta</s></r>
 <r><v>0.1.5</v><s>beta</s></r>
 <r><v>0.1.4</v><s>beta</s></r>
 <r><v>0.1.3</v><s>beta</s></r>
 <r><v>0.1.2</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_bittorrent/deps.1.0.0RC2.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.0";s:4:"name";s:10:"PHP_Compat";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_dicom/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_DICOM</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>LGPL</l>
 <s>Package for reading and modifying DICOM files</s>
 <d>File_DICOM allows reading and modifying of DICOM files.
 DICOM stands for Digital Imaging and COmmunications in Medicine, and is a standard
 for creating, storing and transfering digital images (X-rays, tomography) and related information used in medicine.
 This package in particular does not support the exchange/transfer of DICOM data, nor any network related functionality.
 More information on the DICOM standard can be found at: http://medical.nema.org/
 Please be aware that any use of the information produced by this package for diagnosing purposes is strongly discouraged by the author. See http://www.gnu.org/licenses/lgpl.html for more information.</d>
 <r xlink:href="/rest/r/file_dicom"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_dicom/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_DICOM</p>
 <c>pear.php.net</c>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_dicom/deps.0.3.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_dns/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_DNS</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Manipulate RFC1033-style DNS Zonefiles</s>
 <d>The File_DNS class provides a way to read, edit and write RFC1033 style DNS Zones.</d>
 <r xlink:href="/rest/r/file_dns"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_dns/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_DNS</p>
 <c>pear.php.net</c>
 <r><v>0.0.8</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_dns/deps.0.0.8.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"File";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_find/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_Find</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+System">File System</ca>
 <l>PHP</l>
 <s>A Class the facillitates the search of filesystems</s>
 <d>File_Find, created as a replacement for its Perl counterpart, also named
File_Find, is a directory searcher, which handles, globbing, recursive
directory searching, as well as a slew of other cool features.</d>
 <r xlink:href="/rest/r/file_find"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_find/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_Find</p>
 <c>pear.php.net</c>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.4.0</v><s>beta</s></r>
 <r><v>0.3.1</v><s>beta</s></r>
 <r><v>0.3.0</v><s>beta</s></r>
 <r><v>0.2.0</v><s>stable</s></r>
 <r><v>0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_find/deps.1.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_fortune/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_Fortune</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP</l>
 <s>File_Fortune and File_Fortune_Writer provide an interface for reading from and writing to fortune files.</s>
 <d>File_Fortune provides a PHP interface to reading fortune files. With it, you may
retrieve a single fortune, a random fortune, or all fortunes in the file.

File_Fortune_Writer provides an interface for manipulating the contents of a
fortune file. It allows you to write a complete fortune file and the associated
binary header file from an array of fortunes. You may also add fortunes, delete
fortunes, or update individual fortunes in a fortune file. All write operations
will produce a binary header file to allow for greater compatibility with the
fortune and fortune-mod programs (as well as other fortune interfaces).</d>
 <r xlink:href="/rest/r/file_fortune"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_fortune/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_Fortune</p>
 <c>pear.php.net</c>
 <r><v>0.9.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_fortune/deps.0.9.0.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"5.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:3:{s:4:"name";s:4:"PEAR";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.3.4";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_fstab/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_Fstab</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License v3.0</l>
 <s>Read and write fstab files</s>
 <d>File_Fstab is an easy-to-use package which can read &amp; write UNIX fstab files. It presents a pleasant object-oriented interface to the fstab.
Features:
* Supports blockdev, label, and UUID specification of mount device.
* Extendable to parse non-standard fstab formats by defining a new Entry class for that format.
* Easily examine and set mount options for an entry.
* Stable, functional interface.
* Fully documented with PHPDoc.</d>
 <r xlink:href="/rest/r/file_fstab"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_fstab/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_Fstab</p>
 <c>pear.php.net</c>
 <r><v>2.0.2</v><s>stable</s></r>
 <r><v>2.0.1</v><s>stable</s></r>
 <r><v>2.0.0</v><s>stable</s></r>
 <r><v>2.0.0beta1</v><s>beta</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_fstab/deps.2.0.2.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_gettext/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_Gettext</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP</l>
 <s>GNU Gettext file parser</s>
 <d>Reader and writer for GNU PO and MO files.</d>
 <r xlink:href="/rest/r/file_gettext"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_gettext/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_Gettext</p>
 <c>pear.php.net</c>
 <r><v>0.3.4</v><s>beta</s></r>
 <r><v>0.3.3</v><s>beta</s></r>
 <r><v>0.3.2</v><s>beta</s></r>
 <r><v>0.3.1</v><s>beta</s></r>
 <r><v>0.3.0</v><s>beta</s></r>
 <r><v>0.2.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_gettext/deps.0.3.4.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"pcre";}i:3;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_htaccess/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_HtAccess</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP</l>
 <s>Manipulate .htaccess files</s>
 <d>Provides methods to create and manipulate .htaccess files.</d>
 <r xlink:href="/rest/r/file_htaccess"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_htaccess/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_HtAccess</p>
 <c>pear.php.net</c>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_htaccess/deps.1.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_imc/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_IMC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Create and parse Internet Mail Consortium-style files (like vCard and vCalendar)</s>
 <d>Allows you to programmatically create a vCard or vCalendar, and fetch the text.

IMPORTANT: The array structure has changed slightly from Contact_Vcard_Parse.
See the example output for the new structure.  Also different from Contact_Vcard
is the use of a factory pattern.  Again, see the examples.</d>
 <r xlink:href="/rest/r/file_imc"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_imc/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_IMC</p>
 <c>pear.php.net</c>
 <r><v>0.3</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_imc/deps.0.3.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_ogg/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_Ogg</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Access Ogg bitstreams.</s>
 <d>This package provides access to various media types inside an Ogg bitsream.</d>
 <r xlink:href="/rest/r/file_ogg"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_ogg/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_Ogg</p>
 <c>pear.php.net</c>
 <r><v>0.1.3</v><s>alpha</s></r>
 <r><v>0.1.2</v><s>alpha</s></r>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_ogg/deps.0.1.3.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_passwd/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_Passwd</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP</l>
 <s>Manipulate many kinds of password files</s>
 <d>Provides methods to manipulate and authenticate against standard Unix,
SMB server, AuthUser (.htpasswd), AuthDigest (.htdigest), CVS pserver
and custom formatted password files.</d>
 <r xlink:href="/rest/r/file_passwd"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_passwd/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_Passwd</p>
 <c>pear.php.net</c>
 <r><v>1.1.5</v><s>stable</s></r>
 <r><v>1.1.4</v><s>stable</s></r>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0b2</v><s>beta</s></r>
 <r><v>1.0b1</v><s>beta</s></r>
 <r><v>0.9.5</v><s>beta</s></r>
 <r><v>0.9.4</v><s>beta</s></r>
 <r><v>0.9.3</v><s>beta</s></r>
 <r><v>0.9.2a</v><s>beta</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_passwd/deps.1.1.5.txt", 'a:5:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.0";s:8:"optional";s:3:"yes";s:4:"name";s:10:"Crypt_CHAP";}i:3;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:3:"yes";}i:4;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.0.6";s:8:"optional";s:2:"no";}i:5;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"pcre";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_pdf/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_PDF</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>LGPL</l>
 <s>PDF generation using only PHP.</s>
 <d>This package provides PDF generation using only PHP, without requiring any external libraries.</d>
 <r xlink:href="/rest/r/file_pdf"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_pdf/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_PDF</p>
 <c>pear.php.net</c>
 <r><v>0.0.2</v><s>beta</s></r>
 <r><v>0.0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_pdf/deps.0.0.2.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:13:"HTTP_Download";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_searchreplace/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_SearchReplace</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+System">File System</ca>
 <l>BSD</l>
 <s>Performs search and replace routines</s>
 <d>Provides various functions to perform search/replace
on files. Preg/Ereg regex supported along with faster
but more basic str_replace routine.</d>
 <r xlink:href="/rest/r/file_searchreplace"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_searchreplace/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_SearchReplace</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_searchreplace/deps.1.0.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_smbpasswd/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_SMBPasswd</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>BSD</l>
 <s>Class for managing SAMBA style password files.</s>
 <d>With this package, you can maintain smbpasswd-files, usually used by SAMBA.</d>
 <r xlink:href="/rest/r/file_smbpasswd"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_smbpasswd/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_SMBPasswd</p>
 <c>pear.php.net</c>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_smbpasswd/deps.1.0.2.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.0";s:4:"name";s:10:"Crypt_CHAP";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:5:"mhash";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/fsm/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>FSM</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Processing">Processing</ca>
 <l>PHP License</l>
 <s>Finite State Machine</s>
 <d>The FSM package provides a simple class that implements a Finite State Machine.</d>
 <r xlink:href="/rest/r/fsm"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/fsm/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>FSM</p>
 <c>pear.php.net</c>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/fsm/deps.1.2.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/games_chess/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Games_Chess</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Structures">Structures</ca>
 <l>PHP License</l>
 <s>Construct and validate a logical chess game, does not display</s>
 <d>The logic of handling a chessboard and parsing standard
FEN (Farnsworth-Edwards Notation) for describing a position as well as SAN
(Standard Algebraic Notation) for describing individual moves is handled.  This
class can be used as a backend driver for playing chess, or for validating
and/or creating PGN files using the File_ChessPGN package.

Although this package is alpha, it is fully unit-tested.  The code works, but
the API is fluid, and may change dramatically as it is put into use and better
ways are found to use it.  When the API stabilizes, the stability will increase.</d>
 <r xlink:href="/rest/r/games_chess"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/games_chess/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Games_Chess</p>
 <c>pear.php.net</c>
 <r><v>0.9.0</v><s>alpha</s></r>
 <r><v>0.8.1</v><s>alpha</s></r>
 <r><v>0.8.0</v><s>alpha</s></r>
 <r><v>0.7.0</v><s>alpha</s></r>
 <r><v>0.6alpha</v><s>alpha</s></r>
 <r><v>0.5alpha</v><s>alpha</s></r>
 <r><v>0.3</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/games_chess/deps.0.9.0.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/genealogy_gedcom/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Genealogy_Gedcom</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Gedcom parser</s>
 <d>This package was written to parse .ged (gedcom) file.</d>
 <r xlink:href="/rest/r/genealogy_gedcom"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/genealogy_gedcom/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Genealogy_Gedcom</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/genealogy_gedcom/deps.1.0.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/gtk_filedrop/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Gtk_FileDrop</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Gtk+Components">Gtk Components</ca>
 <l>PHP License</l>
 <s>Make Gtk widgets accept file drops</s>
 <d>A class which makes it easy to make a GtkWidget accept
  the dropping of files or folders</d>
 <r xlink:href="/rest/r/gtk_filedrop"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_filedrop/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Gtk_FileDrop</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_filedrop/deps.1.0.1.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"1.0.0beta3";s:4:"name";s:9:"MIME_Type";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/gtk_mdb_designer/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Gtk_MDB_Designer</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP License</l>
 <s>An Gtk Database schema designer</s>
 <d>A graphical database schema designer, based loosely around the MDB schema,
it features
  - table boxes which are dragged around a window to layout your database
  - add/delete tables
  - add delete columns
  - support for NotNull, Indexes, Sequences , Unique Indexes and  defaults
  - works totally in non-connected mode (eg. no database or setting up required)

  - stores in MDB like xml file.
  - saves to any supported database SQL create tables files.
  - screenshots at http://devel.akbkhome.com/screenshots/Gtk_MDB/


Future enhancements:
  - real MDB schema exports
  - relationships = with lines etc.
Note: the primary aim is to generate SQL files, (so that I can get my work done)
however it is eventually planned to support MDB schema\'s fully.. - just a matter of time..

To use - just pear install and run gtkmdbdesigner

** Note - this package is not activily being maintained. If you find it useful, and what to take over please contact the developer.</d>
 <r xlink:href="/rest/r/gtk_mdb_designer"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_mdb_designer/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Gtk_MDB_Designer</p>
 <c>pear.php.net</c>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_mdb_designer/deps.0.1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.3";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.1";s:4:"name";s:3:"MDB";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.1";s:4:"name";s:10:"XML_Parser";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/gtk_scrollinglabel/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Gtk_ScrollingLabel</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Gtk+Components">Gtk Components</ca>
 <l>PHP License</l>
 <s>A scrolling label for PHP-Gtk</s>
 <d>This is a class to encapsulate the functionality needed for a scrolling gtk label. This class provides a simple, easy to understand API for setting up and controlling the label.  It allows for the ability to scroll in either direction, start and stop the scroll, pause and unpause the scroll, get and set the text, and set display properites of the text.</d>
 <r xlink:href="/rest/r/gtk_scrollinglabel"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_scrollinglabel/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Gtk_ScrollingLabel</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.3.0beta1</v><s>beta</s></r>
 <r><v>0.2.0alpha1</v><s>alpha</s></r>
 <r><v>0.1.0dev1</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_scrollinglabel/deps.1.0.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/gtk_styled/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Gtk_Styled</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Gtk+Components">Gtk Components</ca>
 <l>PHP License</l>
 <s>PHP-GTK pseudo-widgets that mimic GtkData based objects and allow the look and feel to be controlled by the programmer.</s>
 <d>While it is possible to control some style elements of a GtkScrollBar,
other elements cannot be controlled so easily. Items such as the images
at the beginning and end (usually arrows) and the scroll bar that is
dragged to scroll the element cannot be changed. This leads to
applications that either must conform to the windowing systems look
and feel or appear incomplete. The goal of this family of PHP-GTK
classes is to provide all the same functionality as a normal scroll
bar but allow the user to have better control over the look and feel.</d>
 <r xlink:href="/rest/r/gtk_styled"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_styled/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Gtk_Styled</p>
 <c>pear.php.net</c>
 <r><v>0.9.0beta1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_styled/deps.0.9.0beta1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/gtk_vardump/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Gtk_VarDump</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Gtk+Components">Gtk Components</ca>
 <l>PHP License</l>
 <s>A simple GUI to example php data trees</s>
 <d>Just a regedit type interface to examine PHP data trees.</d>
 <r xlink:href="/rest/r/gtk_vardump"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_vardump/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Gtk_VarDump</p>
 <c>pear.php.net</c>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/gtk_vardump/deps.0.2.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_ajax/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_AJAX</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>PHP and JavaScript AJAX library</s>
 <d>Provides PHP and JavaScript libraries for performing AJAX (Communication from JavaScript to your browser without reloading the page)

Offers OO proxies in JavaScript of registered PHP or proxyless operation
Serialization of data sent between PHP and JavaScript is provided by a driver model,
currently JSON and Null encodings are provided</d>
 <r xlink:href="/rest/r/html_ajax"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_ajax/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_AJAX</p>
 <c>pear.php.net</c>
 <r><v>0.1.4</v><s>alpha</s></r>
 <r><v>0.1.3</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_ajax/deps.0.1.4.txt", 'a:2:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.5";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_bbcodeparser/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_BBCodeParser</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>This is a parser to replace UBB style tags with their html equivalents.</s>
 <d>This is a parser to replace UBB style tags with their html equivalents.
 It does not simply do some regex calls, but is complete stack based parse engine. This ensures that all tags are properly nested, if not, extra tags are added to maintain the nesting. This parser should only produce xhtml 1.0 compliant code. All tags are validated and so are all their attributes. It should be easy to extend this parser with your own tags.</d>
 <r xlink:href="/rest/r/html_bbcodeparser"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_bbcodeparser/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_BBCodeParser</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.1b1</v><s>beta</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_bbcodeparser/deps.1.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_common/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Common</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>PEAR::HTML_Common is a base class for other HTML classes.</s>
 <d>The PEAR::HTML_Common package provides methods for html code display and attributes handling.
* Methods to set, remove, update html attributes.
* Handles comments in HTML code.
* Handles layout, tabs, line endings for nicer HTML code.</d>
 <r xlink:href="/rest/r/html_common"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_common/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Common</p>
 <c>pear.php.net</c>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_common/deps.1.2.2.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_common2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Common2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>Abstract base class for HTML classes (PHP5 port of HTML_Common package).</s>
 <d>The HTML_Common2 package provides methods for HTML code display and attributes handling.
* Methods to set, remove, update html attributes.
* Handles comments in HTML code.
* Handles global document options (encoding, linebreak and indentation characters).
* Handles indentation for nicer HTML code.</d>
 <r xlink:href="/rest/r/html_common2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_common2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Common2</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_common2/deps.0.1.0.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_crypt/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Crypt</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>Encrypts text which is later decoded using javascript on the client side</s>
 <d>The PEAR::HTML_Crypt provides methods to encrypt text, which
   can be later be decrypted using JavaScript on the client side

   This is very useful to prevent spam robots collecting email
   addresses from your site, included is a method to add mailto
   links to the text being generated</d>
 <r xlink:href="/rest/r/html_crypt"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_crypt/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Crypt</p>
 <c>pear.php.net</c>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_crypt/deps.1.2.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_css/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_CSS</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License 3.0</l>
 <s>HTML_CSS is a class for generating CSS declarations.</s>
 <d>HTML_CSS provides a simple interface for generating a stylesheet declaration.
It is completely standards compliant, and has some great features:
* Simple OO interface to CSS definitions
* Can parse existing CSS (string or file)
* Output to
    - Inline stylesheet declarations
    - Document internal stylesheet declarations
    - Standalone stylesheet declarations
    - Array of definitions
    - File

In addition, it shares the following with HTML_Common based classes:
* Indent style support
* Line ending style</d>
 <r xlink:href="/rest/r/html_css"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_css/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_CSS</p>
 <c>pear.php.net</c>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.3.4</v><s>beta</s></r>
 <r><v>0.3.3</v><s>beta</s></r>
 <r><v>0.3.2</v><s>beta</s></r>
 <r><v>0.3.1</v><s>beta</s></r>
 <r><v>0.3.0</v><s>beta</s></r>
 <r><v>0.2.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_css/deps.1.0.0RC1.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:8:"optional";s:2:"no";s:4:"name";s:11:"HTML_Common";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_form/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Form</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>Simple HTML form package</s>
 <d>This is a simple HTML form generator.  It supports all the
HTML form element types including file uploads, may return
or print the form, just individual form elements or the full
form in &quot;table mode&quot; with a fixed layout.

This package has been superseded by HTML_QuickForm.</d>
 <r xlink:href="/rest/r/html_form"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_form/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Form</p>
 <c>pear.php.net</c>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.1.0RC4</v><s>beta</s></r>
 <r><v>1.1.0RC3</v><s>beta</s></r>
 <r><v>1.1.0RC2</v><s>beta</s></r>
 <r><v>1.1.0RC1</v><s>beta</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_form/deps.1.2.0.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.0.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_javascript/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Javascript</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP 3.0</l>
 <s>Provides an interface for creating simple JS scripts.</s>
 <d>Provides two classes:
HTML_Javascript for performing basic JS operations.
HTML_Javascript_Convert for converting variables
Allow output data to a file, to the standart output(print), or return</d>
 <r xlink:href="/rest/r/html_javascript"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_javascript/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Javascript</p>
 <c>pear.php.net</c>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.1.0rc1</v><s>beta</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.9</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_javascript/deps.1.1.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_menu/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Menu</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>Generates HTML menus from multidimensional hashes.</s>
 <d>With the HTML_Menu class one can easily create and maintain a
navigation structure for websites, configuring it via a multidimensional
hash structure. Different modes for the HTML output are supported.</d>
 <r xlink:href="/rest/r/html_menu"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_menu/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Menu</p>
 <c>pear.php.net</c>
 <r><v>2.1.1</v><s>stable</s></r>
 <r><v>2.1</v><s>stable</s></r>
 <r><v>2.0pl1</v><s>stable</s></r>
 <r><v>2.0</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_menu/deps.2.1.1.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:19:"HTML_Template_Sigma";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_page/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Page</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License 3.0</l>
 <s>PEAR::HTML_Page is a base class for XHTML page generation.</s>
 <d>*** DEPRECIATED PACKAGE ***
*** use HTML_Page2 instead ***

*****************************
Other than renaming a class, HTML_Page2 is compatible and offers many more features, including frameset support.
*****************************

The PEAR::HTML_Page package provides a simple interface for generating an XHTML compliant page.
* supports virtually all HTML doctypes, from HTML 2.0 through XHTML 1.1 and XHTML Basic 1.0
  plus preliminary support for XHTML 2.0
* namespace support
* global language declaration for the document
* line ending styles
* full META tag support
* support for stylesheet declaration in the head section
* support for linked stylesheets and scripts
* body can be a string, object with toHtml or toString methods or an array (can be combined)</d>
 <r xlink:href="/rest/r/html_page"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_page/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Page</p>
 <c>pear.php.net</c>
 <r><v>2.0.0RC2</v><s>beta</s></r>
 <r><v>2.0.0RC1</v><s>beta</s></r>
 <r><v>2.0.0b7</v><s>beta</s></r>
 <r><v>2.0.0b6</v><s>beta</s></r>
 <r><v>2.0.0b5</v><s>beta</s></r>
 <r><v>2.0.0b4</v><s>beta</s></r>
 <r><v>2.0.0b3</v><s>beta</s></r>
 <r><v>2.0.0b2</v><s>beta</s></r>
 <r><v>2.0.0b1</v><s>beta</s></r>
 <r><v>1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_page/deps.2.0.0RC2.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:11:"HTML_Common";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_page2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Page2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License 3.0</l>
 <s>PEAR::HTML_Page2 is a base class for XHTML page generation.</s>
 <d>The PEAR::HTML_Page2 package provides a simple interface for generating an XHTML compliant page.
* supports virtually all HTML doctypes, from HTML 2.0 through XHTML 1.1 and XHTML Basic 1.0
  plus preliminary support for XHTML 2.0
* namespace support
* global language declaration for the document
* line ending styles
* full META tag support
* support for stylesheet declaration in the head section
* support for script declaration in the head section
* support for linked stylesheets and scripts
* full support for header link tags
* body can be a string, object with toHtml or toString methods or an array (can be combined)

Ideas for use:
* Use to validate the output of a class for XHTML compliance
* Quick prototyping using PEAR packages is now a breeze</d>
 <r xlink:href="/rest/r/html_page2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_page2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Page2</p>
 <c>pear.php.net</c>
 <r><v>0.5.0beta</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_page2/deps.0.5.0beta.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:11:"HTML_Common";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_progress/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Progress</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License 3.0</l>
 <s>How to include a loading bar in your XHTML documents quickly and easily.</s>
 <d>This package provides a way to add a loading bar fully customizable in existing XHTML documents.
Your browser should accept DHTML feature.

Features:
- create horizontal, vertival bar and also circle, ellipse and polygons (square, rectangle).
- allows usage of existing external StyleSheet and/or JavaScript.
- all elements (progress, cells, labels) are customizable by their html properties.
- percent/labels are floating all around the progress meter.
- compliant with all CSS/XHMTL standards.
- integration with all template engines is very easy.
- implements Observer design pattern. It is possible to add Listeners.
- adds a customizable monitor pattern to display a progress bar.
  User-end can abort progress at any time.
- Look and feel can be sets by internal API or external config file
- allows many progress meter on same page without uses of iframe solution.</d>
 <r xlink:href="/rest/r/html_progress"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_progress/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Progress</p>
 <c>pear.php.net</c>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.2.0RC3</v><s>beta</s></r>
 <r><v>1.2.0RC2</v><s>beta</s></r>
 <r><v>1.2.0RC1</v><s>beta</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.6.2</v><s>stable</s></r>
 <r><v>0.6.1</v><s>stable</s></r>
 <r><v>0.6.0</v><s>stable</s></r>
 <r><v>0.5.0</v><s>stable</s></r>
 <r><v>0.4.2</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_progress/deps.1.2.3.txt", 'a:12:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.1";s:8:"optional";s:2:"no";s:4:"name";s:11:"HTML_Common";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.5";s:8:"optional";s:3:"yes";s:4:"name";s:4:"PEAR";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"gt";s:7:"version";s:5:"3.2.4";s:8:"optional";s:3:"yes";s:4:"name";s:14:"HTML_QuickForm";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:8:"optional";s:3:"yes";s:4:"name";s:25:"HTML_QuickForm_Controller";}i:6;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.1";s:8:"optional";s:3:"yes";s:4:"name";s:11:"Image_Color";}i:7;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.5.0";s:8:"optional";s:3:"yes";s:4:"name";s:10:"HTML_Page2";}i:8;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:8:"optional";s:3:"yes";s:4:"name";s:16:"HTML_Template_IT";}i:9;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.2";s:8:"optional";s:3:"yes";s:4:"name";s:19:"HTML_Template_Sigma";}i:10;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.8.7";s:8:"optional";s:3:"yes";s:4:"name";s:3:"Log";}i:11;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"1.10";s:8:"optional";s:3:"yes";s:4:"name";s:6:"Config";}i:12;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:2:"gd";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_progress2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Progress2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License 3.0</l>
 <s>How to include a loading bar in your XHTML documents quickly and easily.</s>
 <d>This package provides a way to add a loading bar fully customizable in existing XHTML documents.
Your browser should accept DHTML feature.

Features:
- create horizontal, vertival bar and also circle, ellipse and polygons (square, rectangle).
- allows usage of existing external StyleSheet and/or JavaScript.
- all elements (progress, cells, labels) are customizable by their html properties.
- percent/labels are floating all around the progress meter.
- compliant with all CSS/XHMTL standards.
- integration with all template engines is very easy.
- implements Observer design pattern. It is possible to add Listeners.
- adds a customizable monitor pattern to display a progress bar.
  User-end can abort progress at any time.
- allows many progress meter on same page without uses of iframe solution.
- error handling system that support native PEAR_Error, but also PEAR_ErrorStack, and
  any other system you might want to plug-in.
- PHP 5 ready.</d>
 <r xlink:href="/rest/r/html_progress2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_progress2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Progress2</p>
 <c>pear.php.net</c>
 <r><v>2.0.0RC2</v><s>beta</s></r>
 <r><v>2.0.0RC1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_progress2/deps.2.0.0RC2.txt", 'a:13:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.4.1";s:8:"optional";s:2:"no";s:4:"name";s:10:"PHP_Compat";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.1";s:8:"optional";s:2:"no";s:4:"name";s:11:"HTML_Common";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.9.1";s:8:"optional";s:2:"no";s:4:"name";s:16:"Event_Dispatcher";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.5";s:8:"optional";s:3:"yes";s:4:"name";s:4:"PEAR";}i:6;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"gt";s:7:"version";s:5:"3.2.4";s:8:"optional";s:3:"yes";s:4:"name";s:14:"HTML_QuickForm";}i:7;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:8:"optional";s:3:"yes";s:4:"name";s:25:"HTML_QuickForm_Controller";}i:8;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.1";s:8:"optional";s:3:"yes";s:4:"name";s:11:"Image_Color";}i:9;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.5.0";s:8:"optional";s:3:"yes";s:4:"name";s:10:"HTML_Page2";}i:10;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:8:"optional";s:3:"yes";s:4:"name";s:16:"HTML_Template_IT";}i:11;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.2";s:8:"optional";s:3:"yes";s:4:"name";s:19:"HTML_Template_Sigma";}i:12;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.8.7";s:8:"optional";s:3:"yes";s:4:"name";s:3:"Log";}i:13;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:2:"gd";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_quickform/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_QuickForm</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>The PEAR::HTML_QuickForm package provides methods for creating, validating, processing HTML forms.</s>
 <d>The HTML_QuickForm package provides methods for dynamically create, validate and render HTML forms.

Features:
* More than 20 ready-to-use form elements.
* XHTML compliant generated code.
* Numerous mixable and extendable validation rules.
* Automatic server-side validation and filtering.
* On request javascript code generation for client-side validation.
* File uploads support.
* Total customization of form rendering.
* Support for external template engines (ITX, Sigma, Flexy, Smarty).
* Pluggable elements, rules and renderers extensions.</d>
 <r xlink:href="/rest/r/html_quickform"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_QuickForm</p>
 <c>pear.php.net</c>
 <r><v>3.2.5</v><s>stable</s></r>
 <r><v>3.2.4pl1</v><s>stable</s></r>
 <r><v>3.2.4</v><s>stable</s></r>
 <r><v>3.2.3</v><s>stable</s></r>
 <r><v>3.2.2</v><s>stable</s></r>
 <r><v>3.2.1</v><s>stable</s></r>
 <r><v>3.2</v><s>stable</s></r>
 <r><v>3.1.1</v><s>stable</s></r>
 <r><v>3.1</v><s>stable</s></r>
 <r><v>3.0</v><s>stable</s></r>
 <r><v>3.0RC1</v><s>beta</s></r>
 <r><v>3.0Beta2</v><s>beta</s></r>
 <r><v>3.0Beta1</v><s>beta</s></r>
 <r><v>2.10</v><s>stable</s></r>
 <r><v>2.9</v><s>stable</s></r>
 <r><v>2.8</v><s>stable</s></r>
 <r><v>2.7</v><s>stable</s></r>
 <r><v>2.6</v><s>stable</s></r>
 <r><v>2.5</v><s>stable</s></r>
 <r><v>2.4</v><s>stable</s></r>
 <r><v>2.3</v><s>stable</s></r>
 <r><v>2.2</v><s>stable</s></r>
 <r><v>2.1</v><s>stable</s></r>
 <r><v>2.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform/deps.3.2.5.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.2";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.1";s:4:"name";s:11:"HTML_Common";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_quickform_advmultiselect/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_QuickForm_advmultiselect</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License 3.0</l>
 <s>Element for HTML_QuickForm that emulate a multi-select.</s>
 <d>The HTML_QuickForm_advmultiselect package adds an element to the
HTML_QuickForm package that is two select boxes next to each other
emulating a multi-select.</d>
 <r xlink:href="/rest/r/html_quickform_advmultiselect"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_advmultiselect/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_QuickForm_advmultiselect</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5.0</v><s>beta</s></r>
 <r><v>0.4.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_advmultiselect/deps.1.0.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.1";s:8:"optional";s:2:"no";s:4:"name";s:11:"HTML_Common";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"gt";s:7:"version";s:5:"3.2.4";s:8:"optional";s:2:"no";s:4:"name";s:14:"HTML_QuickForm";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_quickform_controller/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_QuickForm_Controller</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>The add-on to HTML_QuickForm package that allows building of multipage forms</s>
 <d>The package is essentially an implementation of a PageController pattern.
Architecture:
* Controller class that examines HTTP requests and manages form values persistence across requests.
* Page class (subclass of QuickForm) representing a single page of the form.
* Business logic is contained in subclasses of Action class.
Cool features:
* Includes several default Actions that allow easy building of multipage forms.
* Includes usage examples for common usage cases (single-page form, wizard, tabbed form).</d>
 <r xlink:href="/rest/r/html_quickform_controller"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_controller/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_QuickForm_Controller</p>
 <c>pear.php.net</c>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.9.3</v><s>beta</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.9</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_controller/deps.1.0.4.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"3.2.2";s:4:"name";s:14:"HTML_QuickForm";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_quickform_selectfilter/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_QuickForm_SelectFilter</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>Element for PEAR::HTML_QuickForm that defines dynamic filters on the client side for select elements.</s>
 <d>The PEAR::HTML_QuickForm_SelectFilter package adds an element to the PEAR::HTML_QuickForm package that is used to define dynamic filters on the client side for select elements.</d>
 <r xlink:href="/rest/r/html_quickform_selectfilter"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_selectfilter/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_QuickForm_SelectFilter</p>
 <c>pear.php.net</c>
 <r><v>1.0.0RC1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_quickform_selectfilter/deps.1.0.0RC1.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"3.2.3";s:4:"name";s:14:"HTML_QuickForm";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_safe/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Safe</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>BSD (3 Clause)</l>
 <s>This parser strips down all potentially dangerous content within HTML</s>
 <d>This parser strips down all potentially dangerous content within HTML</d>
 <r xlink:href="/rest/r/html_safe"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_safe/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Safe</p>
 <c>pear.php.net</c>
 <r><v>0.9.0alpha1</v><s>alpha</s></r>
 <r><v>0.3.5</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_safe/deps.0.9.0alpha1.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"4.1.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}s:7:"package";a:3:{s:4:"name";s:12:"XML_HTMLSax3";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"3.0.0RC1";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_select/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Select</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>HTML_Select is a class for generating HTML form select elements.</s>
 <d>HTML_Select provides an OOP way of generating HTML form select elements.</d>
 <r xlink:href="/rest/r/html_select"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_select/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Select</p>
 <c>pear.php.net</c>
 <r><v>1.2.1</v><s>beta</s></r>
 <r><v>1.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_select/deps.1.2.1.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"HTML_Common";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_select_common/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Select_Common</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>BSD</l>
 <s>Some small classes to handle common &amp;lt;select&amp;gt; lists</s>
 <d>Provides &amp;lt;select&amp;gt; lists for:
o Country
o UK counties
o US States
o FR Departements</d>
 <r xlink:href="/rest/r/html_select_common"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_select_common/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Select_Common</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_select_common/deps.1.1.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.8";s:4:"name";s:4:"I18N";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_table/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Table</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>PEAR::HTML_Table makes the design of HTML tables easy, flexible, reusable and efficient.</s>
 <d>The PEAR::HTML_Table package provides methods for easy and efficient design of HTML tables.
* Lots of customization options.
* Tables can be modified at any time.
* The logic is the same as standard HTML editors.
* Handles col and rowspans.
* PHP code is shorter, easier to read and to maintain.
* Tables options can be reused.

For auto filling of data and such then check out http://pear.php.net/package/HTML_Table_Matrix</d>
 <r xlink:href="/rest/r/html_table"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_table/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Table</p>
 <c>pear.php.net</c>
 <r><v>1.5</v><s>stable</s></r>
 <r><v>1.4</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_table/deps.1.5.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:11:"HTML_Common";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_table_matrix/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Table_Matrix</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License v3.0</l>
 <s>Autofill a table with data</s>
 <d>HTML_Table_Matrix is an extension to HTML_Table which allows you to easily fill up a table with data.
Features:
- It uses Filler classes to determine how the data gets filled in the table. With a custom Filler, you can fill data in up, down, forwards, backwards, diagonally, randomly or any other way you like.
- Comes with Fillers to fill left-to-right-top-to-bottom and right-to-left-top-to-bottom.
- Abstract Filler methods keep the code clean &amp; easy to understand.
- Table height or width may be omitted, and it will figure out the correct table size based on the data you provide.
- It integrates handily with Pager to create pleasant pageable table layouts, such as for an image gallery. Just specify a height or width, Filler, and feed it the data returned from Pager.
- Table may be constrained to a specific height or width, and excess data will be ignored.
- Fill offset may be specified, to leave room for a table header, or other elements in the table.
- Fully documented with PHPDoc.
- Includes fully functional example code.</d>
 <r xlink:href="/rest/r/html_table_matrix"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_table_matrix/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Table_Matrix</p>
 <c>pear.php.net</c>
 <r><v>1.0.8</v><s>stable</s></r>
 <r><v>1.0.7</v><s>stable</s></r>
 <r><v>1.0.6</v><s>stable</s></r>
 <r><v>1.0.5</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_table_matrix/deps.1.0.8.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:10:"HTML_Table";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:13:"Numbers_Words";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_template_flexy/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Template_Flexy</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>An extremely powerful Tokenizer driven Template engine</s>
 <d>HTML_Template_Flexy started it\'s life as a simplification of HTML_Template_Xipe,
however in Version 0.2, It became one of the first template engine to use a real Lexer,
rather than regex\'es, making it possible to do things like ASP.net or Cold Fusion tags.
However, it still has a very simple set of goals.
- Very Simple API,
   o easy to learn...
   o prevents to much logic going in templates
- Easy to write document\'able code
   o By using object vars for a template rather than \'assign\', you
     can use phpdoc comments to list what variable you use.
- Editable in WYSIWYG editors
   o you can create full featured templates, that doesnt get broken every time you edit with
     Dreamweaver(tm) or Mozilla editor
   o Uses namespaced attributes to add looping/conditionals
- Extremely Fast,
   o runtime is at least 4 time smaller than most other template engines (eg. Smarty)
   o uses compiled templates, as a result it is many times faster on blocks and loops than
     than Regex templates (eg. IT/phplib)
- Safer (for cross site scripting attacks)
   o All variables default to be output as HTML escaped (overridden with the :h modifier)
- Multilanguage support
   o Parses strings out of template, so you can build translation tools
   o Compiles language specific templates (so translation is only done once, not on every request)
- Full dynamic element support (like ASP.NET), so you can pick elements to replace at runtime

Features:
- {variable} to echo $object-&gt;variable
- {method()} to echo $object-&gt;method();
- {foreach:var,key,value} to PHP foreach loops
- tag attributes FLEXY:FOREACH, FLEXY:IF for looping and conditional HTML inclusion
- {if:variable} to PHP If statement
- {if:method()} to PHP If statement
- {else:} and {end:} to close or alternate If statements
- FORM to HTML_Template_Flexy_Element\'s
- replacement of INPUT, TEXTAREA and SELECT tags with HTML_Template_Flexy_Element code
  use FLEXY:IGNORE (inherited) and FLEXY:IGNOREONLY (single) to prevent replacements
- FLEXY:START/FLEXY:STARTCHILDREN tags to define where template starts/finishes
- support for urlencoded braces {} in HTML attributes.
- documentation in the pear manual

- examples at http://cvs.php.net/cvs.php/pear/HTML_Template_Flexy/tests/

** The long term plan for Flexy is to be integrated as a backend for the
Future Template Package (A BC wrapper will be made available - as I need
to use it too!)</d>
 <r xlink:href="/rest/r/html_template_flexy"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_flexy/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Template_Flexy</p>
 <c>pear.php.net</c>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.8.2</v><s>beta</s></r>
 <r><v>0.8.1</v><s>beta</s></r>
 <r><v>0.8.0</v><s>beta</s></r>
 <r><v>0.7.1</v><s>beta</s></r>
 <r><v>0.7</v><s>beta</s></r>
 <r><v>0.6.3</v><s>beta</s></r>
 <r><v>0.6.2</v><s>beta</s></r>
 <r><v>0.6.1</v><s>beta</s></r>
 <r><v>0.6</v><s>beta</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4.2</v><s>beta</s></r>
 <r><v>0.4.1</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_flexy/deps.1.2.2.txt", 'a:4:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.3";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.0";s:8:"optional";s:3:"yes";s:4:"name";s:15:"HTML_Javascript";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.2.0";s:8:"optional";s:3:"yes";s:4:"name";s:12:"File_Gettext";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.0.1";s:8:"optional";s:3:"yes";s:4:"name";s:12:"Translation2";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_template_it/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Template_IT</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>Integrated Templates</s>
 <d>HTML_Template_IT:
Simple template API.
The Isotemplate API is somewhat tricky for a beginner although it is the best
one you can build. template::parse() [phplib template = Isotemplate] requests
you to name a source and a target where the current block gets parsed into.
Source and target can be block names or even handler names. This API gives you
a maximum of fexibility but you always have to know what you do which is
quite unusual for php skripter like me.

I noticed that I do not any control on which block gets parsed into which one.
If all blocks are within one file, the script knows how they are nested and in
which way you have to parse them. IT knows that inner1 is a child of block2, there\'s
no need to tell him about this.
Features :
  * Nested blocks
  * Include external file
  * Custom tags format (default {mytag})

HTML_Template_ITX :
With this class you get the full power of the phplib template class.
You may have one file with blocks in it but you have as well one main file
and multiple files one for each block. This is quite usefull when you have
user configurable websites. Using blocks not in the main template allows
you to modify some parts of your layout easily.</d>
 <r xlink:href="/rest/r/html_template_it"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_it/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Template_IT</p>
 <c>pear.php.net</c>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_it/deps.1.1.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_template_phplib/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Template_PHPLIB</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>LGPL</l>
 <s>preg_* based template system.</s>
 <d>The popular Template system from PHPLIB ported to PEAR. It has some
features that can\'t be found currently in the original version like
fallback paths. It has minor improvements and cleanup in the code as
well as some speed improvements.</d>
 <r xlink:href="/rest/r/html_template_phplib"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_phplib/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Template_PHPLIB</p>
 <c>pear.php.net</c>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_phplib/deps.1.3.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_template_sigma/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Template_Sigma</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>An implementation of Integrated Templates API with template \'compilation\' added</s>
 <d>HTML_Template_Sigma implements Integrated Templates API designed by Ulf Wendel.

Features:
* Nested blocks. Nesting is controlled by the engine.
* Ability to include files from within template: &amp;lt;!-- INCLUDE --&amp;gt;
* Automatic removal of empty blocks and unknown variables (methods to manually tweak/override this are also available)
* Methods for runtime addition and replacement of blocks in templates
* Ability to insert simple function calls into templates: func_uppercase(\'Hello world!\') and to define callback functions for these
* \'Compiled\' templates: the engine has to parse a template file using regular expressions to find all the blocks and variable placeholders. This is a very &quot;expensive&quot; operation and is an overkill to do on every page request: templates seldom change on production websites. Thus this feature: an internal representation of the template structure is saved into a file and this file gets loaded instead of the source one on subsequent requests (unless the source changes)
* PHPUnit-based tests to define correct behaviour
* Usage examples for most of the features are available, look in the docs/ directory</d>
 <r xlink:href="/rest/r/html_template_sigma"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_sigma/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Template_Sigma</p>
 <c>pear.php.net</c>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.9.2</v><s>stable</s></r>
 <r><v>0.9.1</v><s>stable</s></r>
 <r><v>0.9</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_sigma/deps.1.1.3.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:5:"ctype";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_template_xipe/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_Template_Xipe</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>A simple, fast and powerful template engine.</s>
 <d>The template engine is a compiling engine, all templates are compiled into PHP-files.
This will make the delivery of the files faster on the next request, since the template
doesn\'t need to be compiled again. If the template changes it will be recompiled.

There is no new template language to learn. Beside the default mode, there is a set of constructs
since version 1.6 which allow you to edit your templates with WYSIWYG editors.

By default the template engine uses indention for building blocks (you can turn that off).
This feature was inspired by Python and by the need I felt to force myself
to write proper HTML-code, using proper indentions, to make the code better readable.

Every template is customizable in multiple ways. You can configure each
template or an entire directory to use different delimiters, caching parameters, etc.
via either an XML-file or a XML-chunk which you simply write anywhere inside the tpl-code.

Using the Cache the final file can also be cached (i.e. a resulting HTML-file).
The caching options can be customized as needed. The cache can reduce the server
load by very much, since the entire php-file doesn\'t need to be processed again,
the resulting client-readable data are simply delivered right from the cache
(the data are saved using php\'s output buffering).

The template engine is prepared to be used for multi-language applications too.
If you i.e. use the PEAR::I18N for translating the template,
the compiled templates need to be saved under a different name for each language.
The template engine is prepared for that too, it saves the compiled template including the
language code if required (i.e. a compiled index.tpl which is saved for english gets the filename index.tpl.en.php).</d>
 <r xlink:href="/rest/r/html_template_xipe"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_xipe/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_Template_Xipe</p>
 <c>pear.php.net</c>
 <r><v>1.7.6</v><s>stable</s></r>
 <r><v>1.7.5</v><s>stable</s></r>
 <r><v>1.7.4</v><s>stable</s></r>
 <r><v>1.7.3</v><s>stable</s></r>
 <r><v>1.7.2</v><s>stable</s></r>
 <r><v>1.7.1</v><s>stable</s></r>
 <r><v>1.7</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_template_xipe/deps.1.7.6.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.2";s:4:"name";s:4:"Tree";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.8";s:4:"name";s:3:"Log";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/html_treemenu/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTML_TreeMenu</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>BSD</l>
 <s>Provides an api to create a HTML tree</s>
 <d>PHP Based api creates a tree structure using a couple of
small PHP classes. This can then be converted to javascript
using the printMenu() method. The tree is  dynamic in
IE 4 or higher, NN6/Mozilla and Opera 7, and maintains state
(the collapsed/expanded status of the branches) by using cookies.
Other browsers display the tree fully expanded. Each node can
have an optional link and icon. New API in 1.1 with many changes
(see CVS for changelog) and new features, of which most came
from Chip Chapin (http://www.chipchapin.com).</d>
 <r xlink:href="/rest/r/html_treemenu"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_treemenu/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTML_TreeMenu</p>
 <c>pear.php.net</c>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.9</v><s>stable</s></r>
 <r><v>1.1.8</v><s>stable</s></r>
 <r><v>1.1.7</v><s>stable</s></r>
 <r><v>1.1.6</v><s>stable</s></r>
 <r><v>1.1.5</v><s>stable</s></r>
 <r><v>1.1.4</v><s>stable</s></r>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/html_treemenu/deps.1.2.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP License</l>
 <s>Miscellaneous HTTP utilities</s>
 <d>The HTTP class is a class with static methods for doing
miscellaneous HTTP related stuff like date formatting,
language negotiation or HTTP redirection.</d>
 <r xlink:href="/rest/r/http"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP</p>
 <c>pear.php.net</c>
 <r><v>1.3.6</v><s>stable</s></r>
 <r><v>1.3.5</v><s>stable</s></r>
 <r><v>1.3.4</v><s>stable</s></r>
 <r><v>1.3.3</v><s>stable</s></r>
 <r><v>1.3.2</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.3.0RC2</v><s>beta</s></r>
 <r><v>1.3.0RC1</v><s>beta</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http/deps.1.3.6.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:4:"PEAR";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}i:3;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.0.6";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_client/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_Client</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP License</l>
 <s>Easy way to perform multiple HTTP requests and process their results</s>
 <d>The HTTP_Client class wraps around HTTP_Request and provides a higher level interface
for performing multiple HTTP requests.

Features:
* Manages cookies and referrers between requests
* Handles HTTP redirection
* Has methods to set default headers and request parameters
* Implements the Subject-Observer design pattern: the base class sends
events to listeners that do the response processing.</d>
 <r xlink:href="/rest/r/http_client"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_client/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_Client</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0beta1</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_client/deps.1.0.0.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:12:"HTTP_Request";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_download/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_Download</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP</l>
 <s>Send HTTP Downloads</s>
 <d>Provides an interface to easily send hidden files or any arbitrary data to
HTTP clients.  HTTP_Download can gain its data from variables, files or
stream resources.

It features:
 - Basic caching capabilities
 - Basic throttling mechanism
 - On-the-fly gzip-compression
 - Ranges (partial downloads and resuming)
 - Delivery of on-the-fly generated archives through Archive_Tar and Archive_Zip
 - Sending of PgSQL LOBs without the need to read all data in prior to sending</d>
 <r xlink:href="/rest/r/http_download"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_download/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_Download</p>
 <c>pear.php.net</c>
 <r><v>1.1.0RC3</v><s>beta</s></r>
 <r><v>1.1.0RC2</v><s>beta</s></r>
 <r><v>1.1.0RC1</v><s>beta</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0RC6</v><s>beta</s></r>
 <r><v>1.0.0RC5</v><s>beta</s></r>
 <r><v>1.0.0RC4</v><s>beta</s></r>
 <r><v>1.0.0RC3</v><s>beta</s></r>
 <r><v>1.0.0RC2</v><s>beta</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.10.0</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
 <r><v>0.8.1</v><s>beta</s></r>
 <r><v>0.8.0</v><s>beta</s></r>
 <r><v>0.7.0</v><s>beta</s></r>
 <r><v>0.6.2</v><s>beta</s></r>
 <r><v>0.6.1</v><s>beta</s></r>
 <r><v>0.6.0</v><s>beta</s></r>
 <r><v>0.5.2</v><s>beta</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_download/deps.1.1.0RC3.txt", 'a:10:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:11:"HTTP_Header";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:11:"Archive_Tar";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:11:"Archive_Zip";}i:5;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:9:"MIME_Type";}i:6;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"pcre";}i:7;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:10:"mime_magic";}i:8;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:5:"pgsql";}i:9;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:3:"yes";}i:10;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_header/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_Header</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP License</l>
 <s>OO interface to modify and handle HTTP headers and status codes.</s>
 <d>This class provides methods to set/modify HTTP headers
and status codes including an HTTP caching facility.
It also provides methods for checking Status types.</d>
 <r xlink:href="/rest/r/http_header"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_header/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_Header</p>
 <c>pear.php.net</c>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.2RC1</v><s>beta</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.3.0</v><s>beta</s></r>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1.6</v><s>beta</s></r>
 <r><v>0.1.5</v><s>beta</s></r>
 <r><v>0.1.4</v><s>beta</s></r>
 <r><v>0.1.3</v><s>beta</s></r>
 <r><v>0.1.2</v><s>beta</s></r>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_header/deps.1.1.2.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.1";s:4:"name";s:4:"HTTP";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:4:"PEAR";}i:3;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_request/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_Request</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>BSD</l>
 <s>Provides an easy way to perform HTTP requests</s>
 <d>Supports GET/POST/HEAD/TRACE/PUT/DELETE, Basic authentication, Proxy,
Proxy Authentication, SSL, file uploads etc.</d>
 <r xlink:href="/rest/r/http_request"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_request/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_Request</p>
 <c>pear.php.net</c>
 <r><v>1.2.4</v><s>stable</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_request/deps.1.2.4.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:6:"1.0.12";s:4:"name";s:7:"Net_URL";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.2";s:4:"name";s:10:"Net_Socket";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_server/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_Server</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP License</l>
 <s>HTTP server class.</s>
 <d>HTTP server class that allows you to easily implement HTTP servers by supplying callbacks.
The base class will parse the request, call the appropriate callback and build a repsonse
based on an array that the callbacks have to return.</d>
 <r xlink:href="/rest/r/http_server"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_server/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_Server</p>
 <c>pear.php.net</c>
 <r><v>0.4.0</v><s>alpha</s></r>
 <r><v>0.3</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_server/deps.0.4.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"HTTP";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:6:"0.12.0";s:8:"optional";s:2:"no";s:4:"name";s:10:"Net_Server";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_session/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_Session</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP License</l>
 <s>Object-oriented interface to the session_* family functions</s>
 <d>Object-oriented interface to the session_* family functions.
It provides extra features such as database storage for
session data using the DB, MDB and MDB2 package. It introduces new methods
like isNew(), useCookies(), setExpire(), setIdle(),
isExpired(), isIdled() and others.</d>
 <r xlink:href="/rest/r/http_session"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_session/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_Session</p>
 <c>pear.php.net</c>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5.0</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_session/deps.0.5.1.txt", 'a:5:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.3";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.7.6";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.4";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta4";s:8:"optional";s:3:"yes";s:4:"name";s:4:"MDB2";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_session2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_Session2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP License</l>
 <s>PHP5 Session Handler</s>
 <d>PHP5 Object-oriented interface to the session_* family functions it
                    provides extra features such as database storage for session data using DB
                    package. Supported containers are Creole, PEAR::DB and PEAR::MDB. It introduces
                    new methods like isNew(), useCookies(), setExpire(), setIdle(), isExpired(),
                    isIdled() and others.</d>
 <r xlink:href="/rest/r/http_session2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_session2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_Session2</p>
 <c>pear.php.net</c>
 <r><v>0.2.0</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_session2/deps.0.2.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_sessionserver/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_SessionServer</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP License</l>
 <s>Daemon to store session data that can be accessed via a simple protocol.</s>
 <d>HTTP_SessionServer is a simple PHP based daemon that helps you maintaining state between physically different hosts.
HTTP_SessionServer implements a very simple protocol to store and retrieve data on the server. The storage backend is driver based and supports your local filesystem as well as PEAR::DB as a container.
HTTP_SessionServer comes with a matching client implementation using Net_Socket as well as a session save handler.</d>
 <r xlink:href="/rest/r/http_sessionserver"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_sessionserver/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_SessionServer</p>
 <c>pear.php.net</c>
 <r><v>0.4.0</v><s>alpha</s></r>
 <r><v>0.3.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_sessionserver/deps.0.4.0.txt", 'a:6:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:2:"no";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:6:"0.12.0";s:8:"optional";s:2:"no";s:4:"name";s:10:"Net_Server";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:10:"Net_Socket";}i:5;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:5:"pcntl";}i:6;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_upload/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_Upload</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>LGPL</l>
 <s>Easy and secure managment of files submitted via HTML Forms</s>
 <d>This class provides an advanced file uploader system for file uploads made
from html forms. Features:
 * Can handle from one file to multiple files.
 * Safe file copying from tmp dir.
 * Easy detecting mechanism of valid upload, missing upload or error.
 * Gives extensive information about the uploaded file.
 * Rename uploaded files in different ways: as it is, safe or unique
 * Validate allowed file extensions
 * Multiple languages error messages support (es, en, de, fr, it, nl, pt_BR)</d>
 <r xlink:href="/rest/r/http_upload"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_upload/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_Upload</p>
 <c>pear.php.net</c>
 <r><v>0.9.1</v><s>stable</s></r>
 <r><v>0.9.0</v><s>stable</s></r>
 <r><v>0.8.1</v><s>stable</s></r>
 <r><v>0.8</v><s>stable</s></r>
 <r><v>0.7</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_upload/deps.0.9.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_webdav_client/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_WebDAV_Client</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP</l>
 <s>WebDAV stream wrapper class</s>
 <d>RFC2518 compliant stream wrapper that allows to use WebDAV server
resources like a regular file system from within PHP.</d>
 <r xlink:href="/rest/r/http_webdav_client"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_webdav_client/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_WebDAV_Client</p>
 <c>pear.php.net</c>
 <r><v>0.9.7</v><s>beta</s></r>
 <r><v>0.9.6</v><s>beta</s></r>
 <r><v>0.9.5</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_webdav_client/deps.0.9.7.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.3";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:12:"HTTP_Request";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/http_webdav_server/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>HTTP_WebDAV_Server</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>PHP</l>
 <s>WebDAV Server Baseclass.</s>
 <d>RFC2518 compliant helper class for WebDAV server implementation.</d>
 <r xlink:href="/rest/r/http_webdav_server"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_webdav_server/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>HTTP_WebDAV_Server</p>
 <c>pear.php.net</c>
 <r><v>1.0.0rc1</v><s>beta</s></r>
 <r><v>0.99.1</v><s>beta</s></r>
 <r><v>0.99</v><s>beta</s></r>
 <r><v>0.9</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/http_webdav_server/deps.1.0.0rc1.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.3";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/i18n/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>I18N</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Internationalization">Internationalization</ca>
 <l>PHP</l>
 <s>Internationalization package</s>
 <d>This package supports you to localize your applications.
Multiple ways of supporting translation are implemented
and methods to determine the current users (browser-)language.
Localizing Numbers, DateTime and currency is also implemented.</d>
 <r xlink:href="/rest/r/i18n"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18n/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>I18N</p>
 <c>pear.php.net</c>
 <r><v>0.8.6</v><s>beta</s></r>
 <r><v>0.8.5</v><s>beta</s></r>
 <r><v>0.8.4</v><s>beta</s></r>
 <r><v>0.8.3</v><s>beta</s></r>
 <r><v>0.8.2b1</v><s>beta</s></r>
 <r><v>0.8</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18n/deps.0.8.6.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/i18nv2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>I18Nv2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Internationalization">Internationalization</ca>
 <l>PHP</l>
 <s>Internationalization</s>
 <d>This package provides basic support to localize your application,
like locale based formatting of dates, numbers and currencies.

Beside that it attempts to provide an OS independent way to setlocale()
and aims to provide language, country and currency names translated into
many languages.

Short descriptions of provided classes:
=======================================
 - I18Nv2                    OS independent (Linux/Win32) setlocale(), other utils
 - I18Nv2_Locale             locale based formatter
 - I18Nv2_Negotiator         HTTP negiotiation of preferred language and charset
 - I18Nv2_Country            multilingual list of country names
 - I18Nv2_Language           multilingual list of language names
 - I18Nv2_Currency           multilingual list of currency names
 - I18Nv2_Encoding           list of common and not so common charsets and aliases
 - I18Nv2_AreaCode           list of international area codes (phone)

Decorators for lists like I18Nv2_Country and I18Nv2_Language:
=============================================================
 - HtmlEntities         transparently encode displayed names with HTML entities
 - HtmlSpecialchars     transparently encode special XML chars in displayed names
 - HtmlSelect           ready to use HTML select element facility
 - Filter               exclude or include elements of a list

Translations of language, country and currency names are
more or less completely available in the following languages:
=============================================================
 Afar, Afrikaans, Albanian, Amharic, Arabic, Armenian, Assamese, Azerbaijani,
 Basque, Belarusian, Bengali, Bulgarian, Catalan, Chinese, Cornish, Croatian,
 Czech, Danish, Divehi, Dutch, Dzongkha, English, Esperanto, Estonian, Faroese,
 Finnish, French, Gallegan, Georgian, German, Greek, Gujarati, Hebrew, Hindi,
 Hungarian, Icelandic, Indonesian, Inuktitut, Irish, Italian, Japanese,
 Kalaallisut, Kannada, Kazakh, Khmer, Kirghiz, Korean, Lao, Latvian, Lithuanian,
 Macedonian, Malay, Malayalam, Maltese, Manx, Marathi, Mongolian,
 Norwegian Bokmal, Norwegian Nynorsk, Oriya, Oromo, Pashto (Pushto),
 Persian, Polish, Portuguese, Punjabi, Romanian, Russian, Sanskrit, Serbian,
 Serbo-Croatian, Slovak, Slovenian, Somali, Spanish, Swahili, Swedish, Tamil,
 Tatar, Telugu, Thai, Tigrinya, Turkish, Ukrainian, Urdu, Uzbek, Vietnamese
 and Welsh

FINAL NOTE:
===========
  Contributions are very welcome!</d>
 <r xlink:href="/rest/r/i18nv2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18nv2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>I18Nv2</p>
 <c>pear.php.net</c>
 <r><v>0.11.3</v><s>beta</s></r>
 <r><v>0.11.2</v><s>beta</s></r>
 <r><v>0.11.1</v><s>beta</s></r>
 <r><v>0.11.0</v><s>beta</s></r>
 <r><v>0.10.0</v><s>alpha</s></r>
 <r><v>0.9.3</v><s>alpha</s></r>
 <r><v>0.9.2</v><s>alpha</s></r>
 <r><v>0.9.1</v><s>alpha</s></r>
 <r><v>0.9.0</v><s>alpha</s></r>
 <r><v>0.8.0</v><s>alpha</s></r>
 <r><v>0.7.1</v><s>alpha</s></r>
 <r><v>0.7.0</v><s>alpha</s></r>
 <r><v>0.6.0</v><s>alpha</s></r>
 <r><v>0.5.0</v><s>alpha</s></r>
 <r><v>0.4.0</v><s>alpha</s></r>
 <r><v>0.3.0</v><s>alpha</s></r>
 <r><v>0.2.0</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18nv2/deps.0.11.3.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.0.6";s:8:"optional";s:2:"no";}i:3;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"pcre";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:5:"iconv";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/i18n_unicodestring/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>I18N_UnicodeString</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Internationalization">Internationalization</ca>
 <l>BSD</l>
 <s>Provides a way to work with self contained multibyte strings</s>
 <d>Provides a method of storing and manipulating multibyte strings in PHP without using ext/mbstring. Also allows conversion between various methods of storing Unicode in 8 byte strings like UTF-8 and HTML entities.</d>
 <r xlink:href="/rest/r/i18n_unicodestring"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18n_unicodestring/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>I18N_UnicodeString</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/i18n_unicodestring/deps.0.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_3d/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_3D</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>LGPL</l>
 <s>This class allows the rendering of 3 dimensional images using PHP and ext/GD.</s>
 <d>Image_3D is a highly object oriented PHP5 package
that allows the creation of 3 dimensional images
using PHP and the GD extension, which is bundled
with PHP.

Image_3D currently supports:
* Creation of 3D objects like cubes, spheres, maps, text,...
* Own object definitions possible
* Own material definitions
* Import of 3DSMax files
* Unlimited number of light sources
* Saving all output formats supported by GD</d>
 <r xlink:href="/rest/r/image_3d"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_3d/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_3D</p>
 <c>pear.php.net</c>
 <r><v>0.2.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_3d/deps.0.2.1.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"5.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:9:"extension";a:1:{s:4:"name";s:2:"gd";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_barcode/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_Barcode</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP License</l>
 <s>Barcode generation</s>
 <d>With PEAR::Image_Barcode class you can create a barcode
  representation of a given string.

  This class uses GD function because of this the generated graphic can be any of
  GD supported supported image types.</d>
 <r xlink:href="/rest/r/image_barcode"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_barcode/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_Barcode</p>
 <c>pear.php.net</c>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.5</v><s>stable</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_barcode/deps.1.0.4.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:10:"PHP_Compat";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:2:"gd";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_canvas/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_Canvas</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>LGPL</l>
 <s>A package providing a common interface to image drawing, making image source code independent on the library used.</s>
 <d>A package providing a common interface to image drawing, making image source code independent on the library used.</d>
 <r xlink:href="/rest/r/image_canvas"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_canvas/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_Canvas</p>
 <c>pear.php.net</c>
 <r><v>0.2.1</v><s>alpha</s></r>
 <r><v>0.2.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_canvas/deps.0.2.1.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:2:"gd";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.0";s:8:"optional";s:2:"no";s:4:"name";s:11:"Image_Color";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_color/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_Color</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP License</l>
 <s>Manage and handles color data and conversions.</s>
 <d>Manage and handles color data and conversions.</d>
 <r xlink:href="/rest/r/image_color"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_color/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_Color</p>
 <c>pear.php.net</c>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_color/deps.1.0.2.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:2:"gd";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_gis/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_GIS</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP License</l>
 <s>Visualization of GIS data.</s>
 <d>Generating maps on demand can be a hard job as most often you don\'t
have the maps you need in digital form.
But you can generate your own maps based on raw, digital data files
which are available for free on the net.
This package provides a parser for the most common format for
geographical data, the Arcinfo/E00 format as well as renderers to
produce images using GD or Scalable Vector Graphics (SVG).</d>
 <r xlink:href="/rest/r/image_gis"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_gis/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_GIS</p>
 <c>pear.php.net</c>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_gis/deps.1.1.1.txt", 'a:4:{i:1;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:2:"gd";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:10:"Cache_Lite";}i:3;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:11:"Image_Color";}i:4;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:7:"XML_SVG";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_graph/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_Graph</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>LGPL</l>
 <s>A package for displaying (numerical) data as a graph/chart/plot.</s>
 <d>Image_Graph provides a set of classes that creates graphs/plots/charts based on (numerical) data.

Many different plot types are supported: Bar, line, area, step, impulse, scatter, radar, pie, map, candlestick, band, box &amp; whisker and smoothed line, area and radar plots.

The graph is highly customizable, making it possible to get the exact look and feel that is required.

The output is controlled by a Image_Canvas, which facilitates easy output to many different output formats, amongst others, GD (PNG, JPEG, GIF, WBMP), PDF (using PDFLib), Scalable Vector Graphics (SVG).

Image_Graph is compatible with both PHP4 and PHP5.</d>
 <r xlink:href="/rest/r/image_graph"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_graph/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_Graph</p>
 <c>pear.php.net</c>
 <r><v>0.6.0</v><s>alpha</s></r>
 <r><v>0.5.0</v><s>alpha</s></r>
 <r><v>0.4.0</v><s>alpha</s></r>
 <r><v>0.3.0</v><s>alpha</s></r>
 <r><v>0.3.0dev4</v><s>devel</s></r>
 <r><v>0.3.0dev3</v><s>devel</s></r>
 <r><v>0.3.0dev2</v><s>devel</s></r>
 <r><v>0.3.0dev1</v><s>devel</s></r>
 <r><v>0.2.1</v><s>alpha</s></r>
 <r><v>0.2.0RC1</v><s>alpha</s></r>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_graph/deps.0.6.0.txt", 'a:4:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.2.1";s:8:"optional";s:2:"no";s:4:"name";s:12:"Image_Canvas";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:13:"Numbers_Roman";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:13:"Numbers_Words";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_graphviz/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_GraphViz</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP License</l>
 <s>Interface to AT&amp;T\'s GraphViz tools</s>
 <d>The GraphViz class allows for the creation of and the work with directed and undirected graphs and their visualization with AT&amp;T\'s GraphViz tools.</d>
 <r xlink:href="/rest/r/image_graphviz"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_graphviz/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_GraphViz</p>
 <c>pear.php.net</c>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.1.0beta1</v><s>beta</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
 <r><v>0.2</v><s></s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_graphviz/deps.1.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_iptc/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_IPTC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP License</l>
 <s>Extract, modify, and save IPTC data</s>
 <d>This package provides a mechanism for modifying IPTC header information. The class abstracts the functionality of iptcembed() and iptcparse() in addition to providing methods that properly handle replacing IPTC header fields back into image files.</d>
 <r xlink:href="/rest/r/image_iptc"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_iptc/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_IPTC</p>
 <c>pear.php.net</c>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_iptc/deps.1.0.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_remote/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_Remote</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP</l>
 <s>Retrieve information on remote image files.</s>
 <d>This class can be used for retrieving size information of remote image files via http without downloading the whole image.</d>
 <r xlink:href="/rest/r/image_remote"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_remote/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_Remote</p>
 <c>pear.php.net</c>
 <r><v>1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_remote/deps.1.0.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_text/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_Text</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP License</l>
 <s>Image_Text - Advanced text manipulations in images.</s>
 <d>Image_Text provides a comfortable interface to text manipulations in GD
images. Beside common Freetype2 functionality it offers to handle texts
in a graphic- or office-tool like way. For example it allows alignment of
texts inside a text box, rotation (around the top left corner of a text
box or it\'s center point) and the automatic measurizement of the optimal
font size for a given text box.</d>
 <r xlink:href="/rest/r/image_text"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_text/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_Text</p>
 <c>pear.php.net</c>
 <r><v>0.5.2beta2</v><s>beta</s></r>
 <r><v>0.5.2beta1</v><s>beta</s></r>
 <r><v>0.5.1beta</v><s>beta</s></r>
 <r><v>0.5.0</v><s>beta</s></r>
 <r><v>0.4pl1</v><s>alpha</s></r>
 <r><v>0.4</v><s>alpha</s></r>
 <r><v>0.3pl1</v><s>alpha</s></r>
 <r><v>0.3</v><s>alpha</s></r>
 <r><v>0.2</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_text/deps.0.5.2beta2.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:2:"gd";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_tools/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_Tools</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP License</l>
 <s>Tool collection for images.</s>
 <d>A collection of common image manipulations.</d>
 <r xlink:href="/rest/r/image_tools"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_tools/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_Tools</p>
 <c>pear.php.net</c>
 <r><v>0.2</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_tools/deps.0.2.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:7:"version";s:1:"2";s:4:"name";s:2:"gd";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_transform/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_Transform</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP License</l>
 <s>Provides a standard interface to manipulate images using different libraries</s>
 <d>This package was written to provide a simpler and cross-library interface to doing image transformations and manipulations.
It provides :

* support for GD, ImageMagick, Imagick and NetPBM
* files related functions
* addText
* Scale (by length, percentage, maximum X/Y)
* Resize
* Rotate (custom angle)
* Crop
* Mirror (Most drivers)
* Flip (Most drivers)
* Add border (soon)
* Add shadow (soon)</d>
 <r xlink:href="/rest/r/image_transform"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_transform/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_Transform</p>
 <c>pear.php.net</c>
 <r><v>0.9.0</v><s>alpha</s></r>
 <r><v>0.8</v><s>alpha</s></r>
 <r><v>0.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_transform/deps.0.9.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/image_xbm/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Image_XBM</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Images">Images</ca>
 <l>PHP License</l>
 <s>Manipulate XBM images</s>
 <d>Package for manipulate XBM images</d>
 <r xlink:href="/rest/r/image_xbm"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_xbm/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Image_XBM</p>
 <c>pear.php.net</c>
 <r><v>0.2.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/image_xbm/deps.0.2.0.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.0.4";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.8";s:8:"optional";s:3:"yes";s:4:"name";s:11:"Text_Figlet";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/inline_c/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Inline_C</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>PHP License</l>
 <s>Allows inline inclusion of function definitions in C</s>
 <d>The Inline_C class allows for inline inclusion of C code.  This code
can be compiled and loaded automatically.  Resulting extensions are
cached to speed future loads.</d>
 <r xlink:href="/rest/r/inline_c"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/inline_c/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Inline_C</p>
 <c>pear.php.net</c>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/inline_c/deps.0.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/liveuser/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>LiveUser</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>LGPL</l>
 <s>User authentication and permission management framework</s>
 <d>LiveUser is a set of classes for dealing with user authentication
and permission management. Basically, there are three main elements that
make up this package:
* The LiveUser class
* The Auth containers
* The Perm containers
The LiveUser class takes care of the login process and can be configured
to use a certain permission container and one or more different auth containers.
That means, you can have your users\' data scattered amongst many data containers
and have the LiveUser class try each defined container until the user is found.
For example, you can have all website users who can apply for a new account online
on the webserver\'s local database. Also, you want to enable all your company\'s
employees to login to the site without the need to create new accounts for all of
them. To achieve that, a second container can be defined to be used by the LiveUser class.
You can also define a permission container of your choice that will manage the rights for
each user. Depending on the container, you can implement any kind of permission schemes
for your application while having one consistent API.
Using different permission and auth containers, it\'s easily possible to integrate
newly written applications with older ones that have their own ways of storing permissions
and user data. Just make a new container type and you\'re ready to go!
Currently available are containers using:
PEAR::DB, PEAR::MDB, PEAR::MDB2, PEAR::XML_Tree and PEAR::Auth.</d>
 <r xlink:href="/rest/r/liveuser"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/liveuser/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>LiveUser</p>
 <c>pear.php.net</c>
 <r><v>0.16.6</v><s>beta</s></r>
 <r><v>0.16.5</v><s>beta</s></r>
 <r><v>0.16.4</v><s>beta</s></r>
 <r><v>0.16.3</v><s>beta</s></r>
 <r><v>0.16.2</v><s>beta</s></r>
 <r><v>0.16.1</v><s>beta</s></r>
 <r><v>0.16.0</v><s>beta</s></r>
 <r><v>0.15.1</v><s>beta</s></r>
 <r><v>0.15.0</v><s>beta</s></r>
 <r><v>0.14.0</v><s>beta</s></r>
 <r><v>0.13.3</v><s>beta</s></r>
 <r><v>0.13.2</v><s>beta</s></r>
 <r><v>0.13.1</v><s>beta</s></r>
 <r><v>0.13.0</v><s>beta</s></r>
 <r><v>0.12.0</v><s>beta</s></r>
 <r><v>0.11.1</v><s>beta</s></r>
 <r><v>0.11.0</v><s>beta</s></r>
 <r><v>0.10.0</v><s>beta</s></r>
 <r><v>0.9</v><s>beta</s></r>
 <r><v>0.8.1</v><s>beta</s></r>
 <r><v>0.8</v><s>beta</s></r>
 <r><v>0.7</v><s>alpha</s></r>
 <r><v>0.6.1</v><s>alpha</s></r>
 <r><v>0.6</v><s>alpha</s></r>
 <r><v>0.5.1</v><s>alpha</s></r>
 <r><v>0.5</v><s>alpha</s></r>
 <r><v>0.3</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/liveuser/deps.0.16.6.txt", 'a:10:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.3";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:16:"Event_Dispatcher";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.7.0";s:8:"optional";s:3:"yes";s:4:"name";s:3:"Log";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.6.0";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}i:6;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.4";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:7;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta4";s:8:"optional";s:3:"yes";s:4:"name";s:4:"MDB2";}i:8;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:11:"MDB2_Schema";}i:9;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:8:"XML_Tree";}i:10;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:9:"Crypt_RC4";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/liveuser_admin/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>LiveUser_Admin</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>LGPL</l>
 <s>User authentication and permission management framework</s>
 <d>LiveUser_Admin is meant to be used with the LiveUser package.
It is composed of all the classes necessary to administrate
data used by LiveUser.

You\'ll be able to add/edit/delete/get things like:
* Rights
* Users
* Groups
* Areas
* Applications
* Subgroups
* ImpliedRights

And all other entities within LiveUser.

At the moment we support the following storage containers:
* DB
* MDB
* MDB2

But it takes no time to write up your own storage container,
so if you like to use native mysql functions straight, then it\'s possible
to do so in under a hour!</d>
 <r xlink:href="/rest/r/liveuser_admin"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/liveuser_admin/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>LiveUser_Admin</p>
 <c>pear.php.net</c>
 <r><v>0.3.4</v><s>beta</s></r>
 <r><v>0.3.3</v><s>beta</s></r>
 <r><v>0.3.2</v><s>beta</s></r>
 <r><v>0.3.1</v><s>beta</s></r>
 <r><v>0.3.0</v><s>beta</s></r>
 <r><v>0.2.1</v><s>beta</s></r>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/liveuser_admin/deps.0.3.4.txt", 'a:9:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:6:"0.16.0";s:8:"optional";s:2:"no";s:4:"name";s:8:"LiveUser";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.7.0";s:8:"optional";s:3:"yes";s:4:"name";s:3:"Log";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.6.0";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}i:6;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.4";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:7;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta4";s:8:"optional";s:3:"yes";s:4:"name";s:4:"MDB2";}i:8;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:8:"XML_Tree";}i:9;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:9:"Crypt_RC4";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/log/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Log</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Logging">Logging</ca>
 <l>PHP License</l>
 <s>Logging utilities</s>
 <d>The Log framework provides an abstracted logging system.  It supports logging to console, file, syslog, SQL, Sqlite, mail and mcal targets.  It also provides a subject - observer mechanism.</d>
 <r xlink:href="/rest/r/log"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/log/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Log</p>
 <c>pear.php.net</c>
 <r><v>1.8.7</v><s>stable</s></r>
 <r><v>1.8.6</v><s>stable</s></r>
 <r><v>1.8.5</v><s>stable</s></r>
 <r><v>1.8.4</v><s>stable</s></r>
 <r><v>1.8.3</v><s>stable</s></r>
 <r><v>1.8.2</v><s>stable</s></r>
 <r><v>1.8.1</v><s>stable</s></r>
 <r><v>1.8.0</v><s>stable</s></r>
 <r><v>1.7.1</v><s>stable</s></r>
 <r><v>1.7.0</v><s>stable</s></r>
 <r><v>1.6.7</v><s>stable</s></r>
 <r><v>1.6.6</v><s>stable</s></r>
 <r><v>1.6.5</v><s>stable</s></r>
 <r><v>1.6.4</v><s>stable</s></r>
 <r><v>1.6.3</v><s>stable</s></r>
 <r><v>1.6.2</v><s>stable</s></r>
 <r><v>1.6.1</v><s>stable</s></r>
 <r><v>1.6.0</v><s>stable</s></r>
 <r><v>1.5.3</v><s>stable</s></r>
 <r><v>1.5.2</v><s>stable</s></r>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5</v><s>stable</s></r>
 <r><v>1.4</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s></s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/log/deps.1.8.7.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}i:2;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:6:"sqlite";}i:3;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mail/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Mail</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Mail">Mail</ca>
 <l>PHP/BSD</l>
 <s>Class that provides multiple interfaces for sending emails</s>
 <d>PEAR\'s Mail package defines an interface for implementing mailers under the PEAR hierarchy.  It also provides supporting functions useful to multiple mailer backends.  Currently supported backends include: PHP\'s native mail() function, sendmail, and SMTP.  This package also provides a RFC822 email address list validation utility class.</d>
 <r xlink:href="/rest/r/mail"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Mail</p>
 <c>pear.php.net</c>
 <r><v>1.1.9</v><s>stable</s></r>
 <r><v>1.1.8</v><s>stable</s></r>
 <r><v>1.1.7</v><s>stable</s></r>
 <r><v>1.1.6</v><s>stable</s></r>
 <r><v>1.1.5</v><s>stable</s></r>
 <r><v>1.1.4</v><s>stable</s></r>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail/deps.1.1.9.txt", 'a:1:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.0";s:8:"optional";s:3:"yes";s:4:"name";s:8:"Net_SMTP";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mail_imap/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Mail_IMAP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Mail">Mail</ca>
 <l>PHP</l>
 <s>Provides a c-client backend for webmail.</s>
 <d>Mail_IMAP provides a flexible API for connecting to and retrieving mail from mailboxes using the IMAP, POP3 or NNTP mail protocols. Connection to a mailbox is acheived through the c-client extension to PHP (http://www.php.net/imap). Meaning installation of the c-client extension is required to use Mail_IMAP.

Mail_IMAP can be used to retrieve the contents of a mailbox, whereas it may serve as the backend for a webmail application or mailing list manager.  Since Mail_IMAP is an abstracted object, it allows for complete customization of the UI for any application.

***NOTE***
Mail_IMAPv2 is currently available. Mail_IMAPv2 is far more extensible, has far fewer bugs than Mail_IMAP 1, and is *not* backward compatible with Mail_IMAP 1. Any new developement should use Mail_IMAPv2.</d>
 <r xlink:href="/rest/r/mail_imap"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_imap/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Mail_IMAP</p>
 <c>pear.php.net</c>
 <r><v>1.1.0RC2</v><s>beta</s></r>
 <r><v>1.1.0RC1</v><s>beta</s></r>
 <r><v>1.0.0RC4</v><s>beta</s></r>
 <r><v>1.0.0RC3</v><s>beta</s></r>
 <r><v>1.0.0RC2</v><s>beta</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.3.0A</v><s>alpha</s></r>
 <r><v>0.2.0A</v><s>alpha</s></r>
 <r><v>0.1.7A</v><s>alpha</s></r>
 <r><v>0.1.6A</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_imap/deps.1.1.0RC2.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"imap";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:7:"Net_URL";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mail_imapv2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Mail_IMAPv2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Mail">Mail</ca>
 <l>PHP</l>
 <s>Provides a c-client backend for webmail.</s>
 <d>Mail_IMAPv2 provides a simplified backend for working with the c-client (IMAP) extension. It serves as an OO wrapper for commonly used c-client functions. It provides structure and header parsing as well as body retrieval.
Mail_IMAPv2 provides a simple inbox example that demonstrates its ability to parse and view simple and multipart email messages.
Mail_IMAPv2 also provides a connection wizard to determine the correct protocol and port settings for a remote mail server, all you need to provide is a server, a username and a password.
Mail_IMAPv2 may be used as a webmail backend or as a component in a mailing list manager.
This package requires the c-client extension.  To download the latest version of the c-client extension goto: http://www.php.net/imap.</d>
 <r xlink:href="/rest/r/mail_imapv2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_imapv2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Mail_IMAPv2</p>
 <c>pear.php.net</c>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_imapv2/deps.0.2.0.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"imap";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:7:"Net_URL";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mail_mbox/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Mail_Mbox</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Mail">Mail</ca>
 <l>PHP</l>
 <s>Mbox PHP class to Unix MBOX parsing and using.</s>
 <d>It can split messages inside a Mbox, return the number of messages, return, update or remove an specific message or add a message on the Mbox.</d>
 <r xlink:href="/rest/r/mail_mbox"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_mbox/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Mail_Mbox</p>
 <c>pear.php.net</c>
 <r><v>0.3.0</v><s>beta</s></r>
 <r><v>0.2.0</v><s>alpha</s></r>
 <r><v>0.1.5</v><s>alpha</s></r>
 <r><v>0.1.4</v><s>alpha</s></r>
 <r><v>0.1.3</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_mbox/deps.0.3.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mail_mime/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Mail_Mime</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Mail">Mail</ca>
 <l>PHP</l>
 <s>Provides classes to create and decode mime messages.</s>
 <d>Provides classes to deal with creation and manipulation of mime messages.</d>
 <r xlink:href="/rest/r/mail_mime"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_mime/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Mail_Mime</p>
 <c>pear.php.net</c>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.3.0RC1</v><s>beta</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_mime/deps.1.3.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mail_queue/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Mail_Queue</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Mail">Mail</ca>
 <l>PHP</l>
 <s>Class for put mails in queue and send them later in background.</s>
 <d>Class to handle mail queue managment.
Wrapper for PEAR::Mail and PEAR::DB (or PEAR::MDB/MDB2).
It can load, save and send saved mails in background
and also backup some mails.

The Mail_Queue class puts mails in a temporary container,
waiting to be fed to the MTA (Mail Transport Agent),
and sends them later (e.g. a certain amount of mails
every few minutes) by crontab or in other way.</d>
 <r xlink:href="/rest/r/mail_queue"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_queue/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Mail_Queue</p>
 <c>pear.php.net</c>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.9</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mail_queue/deps.1.1.3.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:4:"Mail";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_basex/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Basex</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Simple class for converting base set of numbers with a customizable character base set.</s>
 <d>Base X conversion class</d>
 <r xlink:href="/rest/r/math_basex"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_basex/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Basex</p>
 <c>pear.php.net</c>
 <r><v>0.3</v><s>stable</s></r>
 <r><v>0.2</v><s>stable</s></r>
 <r><v>0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_basex/deps.0.3.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_binaryutils/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_BinaryUtils</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>LGPL</l>
 <s>Collection of helper methods for easy handling of binary data.</s>
 <d>Collection of helper methods for dealing with binary data (add, subtract, converting functions, endianess functions etc.).</d>
 <r xlink:href="/rest/r/math_binaryutils"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_binaryutils/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_BinaryUtils</p>
 <c>pear.php.net</c>
 <r><v>0.3.0</v><s>alpha</s></r>
 <r><v>0.2.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_binaryutils/deps.0.3.0.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.3";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_complex/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Complex</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Classes that define complex numbers and their operations</s>
 <d>Classes that represent and manipulate complex numbers. Contain definitions
for basic arithmetic functions, as well as trigonometric, inverse
trigonometric, hyperbolic, inverse hyperbolic, exponential and
logarithms of complex numbers.</d>
 <r xlink:href="/rest/r/math_complex"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_complex/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Complex</p>
 <c>pear.php.net</c>
 <r><v>0.8.5</v><s>beta</s></r>
 <r><v>0.8</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_complex/deps.0.8.5.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:11:"Math_TrigOp";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_fibonacci/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Fibonacci</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Package to calculate and manipulate Fibonacci numbers</s>
 <d>The Fibonacci series is constructed using the formula:
      F(n) = F(n - 1) + F (n - 2),
By convention F(0) = 0, and F(1) = 1.
An alternative formula that uses the Golden Ratio can also be used:
      F(n) = (PHI^n - phi^n)/sqrt(5) [Lucas\' formula],
where PHI = (1 + sqrt(5))/2 is the Golden Ratio, and
      phi = (1 - sqrt(5))/2 is its reciprocal
Requires Math_Integer, and can be used with big integers if the GMP or
the BCMATH libraries are present.</d>
 <r xlink:href="/rest/r/math_fibonacci"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_fibonacci/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Fibonacci</p>
 <c>pear.php.net</c>
 <r><v>0.8</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_fibonacci/deps.0.8.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:12:"Math_Integer";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_finance/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Finance</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP License</l>
 <s>Financial functions</s>
 <d>Collection of financial functions for time value of money (annuities), cash flow, interest rate conversions, bonds and depreciation calculations.</d>
 <r xlink:href="/rest/r/math_finance"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_finance/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Finance</p>
 <c>pear.php.net</c>
 <r><v>0.5.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_finance/deps.0.5.0.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.3.0";s:4:"name";s:26:"Math_Numerical_RootFinding";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_fraction/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Fraction</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Classes that represent and manipulate fractions.</s>
 <d>Classes that represent and manipulate fractions (x = a/b).

The Math_FractionOp static class contains definitions for:
- basic arithmetic operations
- comparing fractions
- greatest common divisor (gcd) and least common multiple (lcm) of two integers
- simplifying (reducing) and getting the reciprocal of a fraction
- converting a float to fraction.</d>
 <r xlink:href="/rest/r/math_fraction"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_fraction/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Fraction</p>
 <c>pear.php.net</c>
 <r><v>0.4.0</v><s>beta</s></r>
 <r><v>0.3.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_fraction/deps.0.4.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_histogram/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Histogram</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Classes to calculate histogram distributions</s>
 <d>Classes to calculate histogram distributions and associated
   statistics. Supports simple and cummulative histograms.
   You can generate regular (2D) histograms, 3D, or 4D histograms
   Data must not have nulls.
Requires Math_Stats.</d>
 <r xlink:href="/rest/r/math_histogram"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_histogram/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Histogram</p>
 <c>pear.php.net</c>
 <r><v>0.9.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_histogram/deps.0.9.0.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:10:"Math_Stats";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_integer/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Integer</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Package to represent and manipulate integers</s>
 <d>The class Math_Integer can represent integers bigger than the
signed longs that are the default of PHP, if either the GMP or
the BCMATH (bundled with PHP) are present. Otherwise it will fall
back to the internal integer representation.
The Math_IntegerOp class defines operations on Math_Integer objects.</d>
 <r xlink:href="/rest/r/math_integer"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_integer/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Integer</p>
 <c>pear.php.net</c>
 <r><v>0.8</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_integer/deps.0.8.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_matrix/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Matrix</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Class to represent matrices and matrix operations</s>
 <d>Matrices are represented as 2 dimensional arrays of numbers.
This class defines methods for matrix objects, as well as static methods
to read, write and manipulate matrices, including methods to solve systems
of linear equations (with and without iterative error correction).
Requires the Math_Vector package.
For running the unit tests you will need PHPUnit version 0.6.2 or older.</d>
 <r xlink:href="/rest/r/math_matrix"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_matrix/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Matrix</p>
 <c>pear.php.net</c>
 <r><v>0.8.5</v><s>beta</s></r>
 <r><v>0.8.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_matrix/deps.0.8.5.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:11:"Math_Vector";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"le";s:7:"version";s:5:"0.6.2";s:8:"optional";s:3:"yes";s:4:"name";s:7:"PHPUnit";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_numerical_rootfinding/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Numerical_RootFinding</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>BSD License</l>
 <s>Numerical Methods Root-Finding functions package</s>
 <d>Math_Numerical_RootFinding is the package
provide various Numerical Methods Root-Finding
functions implemented in PHP, e.g Bisection,
Newton-Raphson, Fixed Point, Secant etc.</d>
 <r xlink:href="/rest/r/math_numerical_rootfinding"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_numerical_rootfinding/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Numerical_RootFinding</p>
 <c>pear.php.net</c>
 <r><v>0.3.0</v><s>alpha</s></r>
 <r><v>0.2.0</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_numerical_rootfinding/deps.0.3.0.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_quaternion/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Quaternion</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Classes that define Quaternions and their operations</s>
 <d>Classes that represent and manipulate quaternions. Contain definitions
for basic arithmetic functions in a static class.
Quaternions are an extension of the idea of complex numbers, and
a quaternion is defined as:
    q = a + b*i + c*j + d*k
In 1844 Hamilton described a system in which numbers were composed of
a real part and 3 imaginary and independent parts (i,j,k), such that:
    i^2 = j^2 = k^2 = -1       and
    ij = k, jk = i, ki = j     and
    ji = -k, kj = -i, ik = -j
The above are known as &quot;Hamilton\'s rules&quot;</d>
 <r xlink:href="/rest/r/math_quaternion"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_quaternion/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Quaternion</p>
 <c>pear.php.net</c>
 <r><v>0.7.1</v><s>beta</s></r>
 <r><v>0.7</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_quaternion/deps.0.7.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_rpn/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_RPN</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP License</l>
 <s>Reverse Polish Notation.</s>
 <d>Change Expression To RPN (Reverse Polish Notation) and evaluate it.</d>
 <r xlink:href="/rest/r/math_rpn"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_rpn/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_RPN</p>
 <c>pear.php.net</c>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_rpn/deps.1.1.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_stats/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Stats</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Classes to calculate statistical parameters</s>
 <d>Package to calculate statistical parameters of numerical arrays
of data. The data can be in a simple numerical array, or in a
cummulative numerical array. A cummulative array, has the value
as the index and the number of repeats as the value for the
array item, e.g. $data = array(3=&gt;4, 2.3=&gt;5, 1.25=&gt;6, 0.5=&gt;3).

Nulls can be rejected, ignored or handled as zero values.

Note: You should be using the latest release (0.9.0beta3 currently), as it fixes problems with the calculations of several of the statistics that exist in the stable release.</d>
 <r xlink:href="/rest/r/math_stats"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_stats/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Stats</p>
 <c>pear.php.net</c>
 <r><v>0.9.0beta3</v><s>beta</s></r>
 <r><v>0.9.0beta2</v><s>beta</s></r>
 <r><v>0.9.0beta1</v><s>beta</s></r>
 <r><v>0.8.5</v><s>stable</s></r>
 <r><v>0.8.4</v><s>stable</s></r>
 <r><v>0.8.3</v><s>beta</s></r>
 <r><v>0.8.1</v><s>beta</s></r>
 <r><v>0.8.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_stats/deps.0.9.0beta3.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_trigop/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_TrigOp</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Supplementary trigonometric functions</s>
 <d>Static class with methods that implement supplementary trigonometric,
inverse trigonometric, hyperbolic, and inverse hyperbolic functions.</d>
 <r xlink:href="/rest/r/math_trigop"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_trigop/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_TrigOp</p>
 <c>pear.php.net</c>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_trigop/deps.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/math_vector/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Math_Vector</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Math">Math</ca>
 <l>PHP</l>
 <s>Vector and vector operation classes</s>
 <d>Classes to represent Tuples, general Vectors, and 2D-/3D-vectors,
as well as a static class for vector operations.</d>
 <r xlink:href="/rest/r/math_vector"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_vector/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Math_Vector</p>
 <c>pear.php.net</c>
 <r><v>0.6.2</v><s>beta</s></r>
 <r><v>0.6.0</v><s>beta</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/math_vector/deps.0.6.2.txt", 'a:1:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"le";s:7:"version";s:5:"0.6.2";s:8:"optional";s:3:"yes";s:4:"name";s:7:"PHPUnit";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD style</l>
 <s>database abstraction layer</s>
 <d>PEAR MDB is a merge of the PEAR DB and Metabase php database abstraction layers.
It provides a common API for all support RDBMS. The main difference to most
other DB abstraction packages is that MDB goes much further to ensure
portability. Among other things MDB features:
* An OO-style query API
* A DSN (data source name) or array format for specifying database servers
* Datatype abstraction and on demand datatype conversion
* Portable error codes
* Sequential and non sequential row fetching as well as bulk fetching
* Ordered array and associative array for the fetched rows
* Prepare/execute (bind) emulation
* Sequence emulation
* Replace emulation
* Limited Subselect emulation
* Row limit support
* Transactions support
* Large Object support
* Index/Unique support
* Module Framework to load advanced functionality on demand
* Table information interface
* RDBMS management methods (creating, dropping, altering)
* RDBMS independent xml based schema definition management
* Altering of a DB from a changed xml schema
* Reverse engineering of xml schemas from an existing DB (currently only MySQL)
* Full integration into the PEAR Framework
* Wrappers for the PEAR DB and Metabase APIs
* PHPDoc API documentation
Currently supported RDBMS:
MySQL
PostGreSQL
Oracle
Frontbase
Querysim
Interbase/Firebird
MSSQL</d>
 <r xlink:href="/rest/r/mdb"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB</p>
 <c>pear.php.net</c>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1.4</v><s>stable</s></r>
 <r><v>1.1.4RC6</v><s>devel</s></r>
 <r><v>1.1.4RC5</v><s>devel</s></r>
 <r><v>1.1.4RC4</v><s>devel</s></r>
 <r><v>1.1.4RC3</v><s>devel</s></r>
 <r><v>1.1.4RC2</v><s>devel</s></r>
 <r><v>1.1.4-RC1</v><s>devel</s></r>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.3-RC2</v><s>devel</s></r>
 <r><v>1.1.3-RC1</v><s>devel</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.2-RC2</v><s>devel</s></r>
 <r><v>1.1.2-RC1</v><s>devel</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0pl1</v><s>devel</s></r>
 <r><v>1.1.0</v><s>devel</s></r>
 <r><v>1.0.1RC1</v><s>devel</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>1.0_RC4</v><s>devel</s></r>
 <r><v>1.0_RC3</v><s>devel</s></r>
 <r><v>1.0_RC2</v><s>devel</s></r>
 <r><v>1.0_RC1</v><s>devel</s></r>
 <r><v>0.9.11</v><s>devel</s></r>
 <r><v>0.9.10</v><s>devel</s></r>
 <r><v>0.9.9</v><s>beta</s></r>
 <r><v>0.9.8</v><s>beta</s></r>
 <r><v>0.9.7.1</v><s>beta</s></r>
 <r><v>0.9.7</v><s>beta</s></r>
 <r><v>0.9.6</v><s>beta</s></r>
 <r><v>0.9.5</v><s>beta</s></r>
 <r><v>0.9.4</v><s>beta</s></r>
 <r><v>0.9.3</v><s>beta</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.9</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb/deps.1.3.0.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:4:"name";s:4:"PEAR";}i:3;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:10:"XML_Parser";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>database abstraction layer</s>
 <d>PEAR MDB2 is a merge of the PEAR DB and Metabase php database abstraction layers.

Note that the API will be adapted to better fit with the new php5 only PDO
before the first stable release.

It provides a common API for all support RDBMS. The main difference to most
other DB abstraction packages is that MDB2 goes much further to ensure
portability. Among other things MDB2 features:
* An OO-style query API
* A DSN (data source name) or array format for specifying database servers
* Datatype abstraction and on demand datatype conversion
* Portable error codes
* Sequential and non sequential row fetching as well as bulk fetching
* Ability to make buffered and unbuffered queries
* Ordered array and associative array for the fetched rows
* Prepare/execute (bind) emulation
* Sequence emulation
* Replace emulation
* Limited Subselect emulation
* Row limit support
* Transactions support
* Large Object support
* Index/Unique support
* Module Framework to load advanced functionality on demand
* Table information interface
* RDBMS management methods (creating, dropping, altering)
* RDBMS independent xml based schema definition management
* Reverse engineering schemas from an existing DB
* Full integration into the PEAR Framework
* PHPDoc API documentation</d>
 <r xlink:href="/rest/r/mdb2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2</p>
 <c>pear.php.net</c>
 <r><v>2.0.0beta5</v><s>beta</s></r>
 <r><v>2.0.0beta4</v><s>beta</s></r>
 <r><v>2.0.0beta3</v><s>beta</s></r>
 <r><v>2.0.0beta2</v><s>beta</s></r>
 <r><v>2.0.0beta1</v><s>alpha</s></r>
 <r><v>2.0.0alpha1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2/deps.2.0.0beta5.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_driver_fbsql/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Driver_fbsql</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>fbsql MDB2 driver</s>
 <d>This is the Frontbase SQL MDB2 driver.</d>
 <r xlink:href="/rest/r/mdb2_driver_fbsql"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_fbsql/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Driver_fbsql</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_fbsql/deps.0.1.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta5";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:5:"fbsql";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_driver_ibase/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Driver_ibase</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>ibase MDB2 driver</s>
 <d>This is the Firebird/Interbase MDB2 driver.</d>
 <r xlink:href="/rest/r/mdb2_driver_ibase"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_ibase/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Driver_ibase</p>
 <c>pear.php.net</c>
 <r><v>0.1.1</v><s>beta</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_ibase/deps.0.1.1.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.4";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta5";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:9:"interbase";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_driver_mssql/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Driver_mssql</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>mssql MDB2 driver</s>
 <d>This is the Microsoft SQL Server MDB2 driver.</d>
 <r xlink:href="/rest/r/mdb2_driver_mssql"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_mssql/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Driver_mssql</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_mssql/deps.0.1.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta5";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:5:"mssql";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_driver_mysql/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Driver_mysql</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>mysql MDB2 driver</s>
 <d>This is the MySQL MDB2 driver.</d>
 <r xlink:href="/rest/r/mdb2_driver_mysql"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_mysql/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Driver_mysql</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_mysql/deps.0.1.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta5";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:5:"mysql";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_driver_mysqli/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Driver_mysqli</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>mysqli MDB2 driver</s>
 <d>This is the MySQLi MDB2 driver.</d>
 <r xlink:href="/rest/r/mdb2_driver_mysqli"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_mysqli/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Driver_mysqli</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_mysqli/deps.0.1.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta5";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:6:"mysqli";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_driver_oci8/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Driver_oci8</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>oci8 MDB2 driver</s>
 <d>This is the Oracle OCI8 MDB2 driver.</d>
 <r xlink:href="/rest/r/mdb2_driver_oci8"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_oci8/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Driver_oci8</p>
 <c>pear.php.net</c>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_oci8/deps.0.1.1.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta5";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"oci8";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_driver_pgsql/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Driver_pgsql</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>pgsql MDB2 driver</s>
 <d>This is the PostGreSQL MDB2 driver.</d>
 <r xlink:href="/rest/r/mdb2_driver_pgsql"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_pgsql/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Driver_pgsql</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_pgsql/deps.0.1.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta5";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:5:"pgsql";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_driver_querysim/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Driver_querysim</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>querysim MDB2 driver</s>
 <d>This is the Querysim MDB2 driver.</d>
 <r xlink:href="/rest/r/mdb2_driver_querysim"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_querysim/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Driver_querysim</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_querysim/deps.0.1.0.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta5";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_driver_sqlite/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Driver_sqlite</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>sqlite MDB2 driver</s>
 <d>This is the SQLite MDB2 driver.</d>
 <r xlink:href="/rest/r/mdb2_driver_sqlite"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_sqlite/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Driver_sqlite</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_driver_sqlite/deps.0.1.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta5";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:6:"sqlite";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2_schema/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2_Schema</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>XML based database schema manager</s>
 <d>PEAR::MDB2_Schema enables users to maintain RDBMS independent schema
files in XML that can be used to create, alter and drop database entities
and insert data into a database. Reverse engineering database schemas from
existing databases is also supported. The format is compatible with both
PEAR::MDB and Metabase.</d>
 <r xlink:href="/rest/r/mdb2_schema"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_schema/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2_Schema</p>
 <c>pear.php.net</c>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2_schema/deps.0.2.0.txt", 'a:5:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:10:"XML_Parser";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta4";s:8:"optional";s:2:"no";s:4:"name";s:4:"MDB2";}i:5;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:7:"XML_DTD";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb_querytool/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB_QueryTool</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP</l>
 <s>An OO-interface for easily retrieving and modifying data in a DB.</s>
 <d>This package is an OO-abstraction to the SQL-Query language, it provides methods such
as setWhere, setOrder, setGroup, setJoin, etc. to easily build queries.
It also provides an easy to learn interface that interacts nicely with HTML-forms using
arrays that contain the column data, that shall be updated/added in a DB.
This package bases on an SQL-Builder which lets you easily build
SQL-Statements and execute them.
NB: this is a PEAR::MDB porting from the original DB_QueryTool
written by Wolfram Kriesing and Paolo Panto (vision:produktion, wk@visionp.de).</d>
 <r xlink:href="/rest/r/mdb_querytool"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb_querytool/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB_QueryTool</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.11.1</v><s>stable</s></r>
 <r><v>0.10.1</v><s>stable</s></r>
 <r><v>0.9.7</v><s>stable</s></r>
 <r><v>0.9.6</v><s>stable</s></r>
 <r><v>0.9.5-pl1</v><s>stable</s></r>
 <r><v>0.9.5</v><s>stable</s></r>
 <r><v>0.9.4</v><s>stable</s></r>
 <r><v>0.9.4-RC1</v><s>beta</s></r>
 <r><v>0.9.3</v><s>beta</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb_querytool/deps.1.0.1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:3:"MDB";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.7";s:4:"name";s:3:"Log";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/message/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Message</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Encryption">Encryption</ca>
 <l>PHP</l>
 <s>Message hash and digest (HMAC) generation methods and classes</s>
 <d>Classes for message hashing and HMAC signature generation
using the mhash functions.</d>
 <r xlink:href="/rest/r/message"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/message/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Message</p>
 <c>pear.php.net</c>
 <r><v>0.6</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/message/deps.0.6.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mime_type/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MIME_Type</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Tools+and+Utilities">Tools and Utilities</ca>
 <l>PHP License 3.0</l>
 <s>Utility class for dealing with MIME types</s>
 <d>Provide functionality for dealing with MIME types.
* Parse MIME type.
* Supports full RFC2045 specification.
* Many utility functions for working with and determining info about types.
* Most functions can be called statically.
* Autodetect a file\'s mime-type, either with mime_content_type() or the \'file\' command.</d>
 <r xlink:href="/rest/r/mime_type"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mime_type/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MIME_Type</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0beta3</v><s>beta</s></r>
 <r><v>1.0.0beta2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mime_type/deps.1.0.0.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.1";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:14:"System_Command";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mp3_id/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MP3_ID</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>LGPL</l>
 <s>Read/Write MP3-Tags</s>
 <d>The class offers methods for reading and
writing information tags (version 1) in MP3 files.</d>
 <r xlink:href="/rest/r/mp3_id"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mp3_id/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MP3_ID</p>
 <c>pear.php.net</c>
 <r><v>1.2.0RC2</v><s>beta</s></r>
 <r><v>1.2.0RC1</v><s>beta</s></r>
 <r><v>1.1.4</v><s>stable</s></r>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mp3_id/deps.1.2.0RC2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mp3_playlist/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>MP3_Playlist</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Library to create MP3 playlists on the fly, several formats supported including XML, RSS and XHTML</s>
 <d>MP3_Playlist is a php library to facilitate the creation and to some extend the rendering of MP3 playlists.
It scans a local folder with all the MP3 files and outputs the playlist in several formats including M3U, SMIL, XML, XHTML with the possibility to backup the lists on the fly with an SQLite DB.</d>
 <r xlink:href="/rest/r/mp3_playlist"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mp3_playlist/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MP3_Playlist</p>
 <c>pear.php.net</c>
 <r><v>0.5.0alpha1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mp3_playlist/deps.0.5.0alpha1.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.0";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:6:"1.0.14";s:8:"optional";s:2:"no";s:4:"name";s:7:"Net_URL";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.4";s:8:"optional";s:2:"no";s:4:"name";s:6:"MP3_Id";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_checkip/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_CheckIP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Check the syntax of IPv4 addresses</s>
 <d>This package validates IPv4 addresses.</d>
 <r xlink:href="/rest/r/net_checkip"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_checkip/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_CheckIP</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_checkip/deps.1.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_curl/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Curl</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP</l>
 <s>Net_Curl provides an OO interface to PHP\'s cURL extension</s>
 <d>Provides an OO interface to PHP\'s curl extension</d>
 <r xlink:href="/rest/r/net_curl"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_curl/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Curl</p>
 <c>pear.php.net</c>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.0.1beta</v><s>beta</s></r>
 <r><v>0.2</v><s>stable</s></r>
 <r><v>0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_curl/deps.1.2.2.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"curl";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_cyrus/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Cyrus</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>provides an API for the administration of Cyrus IMAP servers.</s>
 <d>API for the administration of Cyrus IMAP servers. It can be used to create,delete and modify users and it\'s properties (Quota and ACL)</d>
 <r xlink:href="/rest/r/net_cyrus"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_cyrus/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Cyrus</p>
 <c>pear.php.net</c>
 <r><v>0.3.1</v><s>beta</s></r>
 <r><v>0.3.0</v><s>beta</s></r>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_cyrus/deps.0.3.1.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:10:"Net_Socket";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:8:"Net_IMAP";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_dict/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Dict</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP</l>
 <s>Interface to the DICT Protocol</s>
 <d>This class provides a simple API to the DICT Protocol handling all the network related issues
and providing DICT responses in PHP datatypes
to make it easy for a developer to use DICT
servers in their programs.</d>
 <r xlink:href="/rest/r/net_dict"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dict/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Dict</p>
 <c>pear.php.net</c>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dict/deps.1.0.3.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:10:"Net_Socket";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.5.2";s:4:"name";s:5:"Cache";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_dig/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Dig</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP 2.02</l>
 <s>The PEAR::Net_Dig class should be a nice, friendly OO interface to the dig command</s>
 <d>Net_Dig class is no longer being maintained.  Use of Net_DNS is recommended instead.  A brief tutorial on how to migrate to Net_DNS is listed below.</d>
 <r xlink:href="/rest/r/net_dig"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dig/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Dig</p>
 <c>pear.php.net</c>
 <r><v>0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dig/deps.0.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_dime/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_DIME</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>The PEAR::Net_DIME class implements DIME encoding</s>
 <d>This is the initial independent release of the Net_DIME package.
Provides an implementation of DIME as defined at
http://search.ietf.org/internet-drafts/draft-nielsen-dime-02.txt</d>
 <r xlink:href="/rest/r/net_dime"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dime/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_DIME</p>
 <c>pear.php.net</c>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dime/deps.0.3.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_dns/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_DNS</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>LGPL 2.1</l>
 <s>Resolver library used to communicate with a DNS server</s>
 <d>A resolver library used to communicate with a name server to perform DNS queries, zone transfers, dynamic DNS updates, etc.  Creates an object hierarchy from a DNS server\'s response, which allows you to view all of the information given by the DNS server.  It bypasses the system\'s resolver library and communicates directly with the server.</d>
 <r xlink:href="/rest/r/net_dns"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dns/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_DNS</p>
 <c>pear.php.net</c>
 <r><v>1.0.0rc1</v><s>beta</s></r>
 <r><v>1.0.0b3</v><s>beta</s></r>
 <r><v>1.00b2</v><s>beta</s></r>
 <r><v>1.00b1</v><s>beta</s></r>
 <r><v>0.03</v><s>stable</s></r>
 <r><v>0.02</v><s>alpha</s></r>
 <r><v>0.01</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dns/deps.1.0.0rc1.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.2";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:5:"mhash";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_dnsbl/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_DNSBL</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>DNSBL Checker</s>
 <d>Checks if a given Host or URL is listed on an DNSBL or SURBL</d>
 <r xlink:href="/rest/r/net_dnsbl"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dnsbl/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_DNSBL</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.5.4</v><s>beta</s></r>
 <r><v>0.5.3</v><s>beta</s></r>
 <r><v>0.5.2</v><s>beta</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_dnsbl/deps.1.0.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.1";s:4:"name";s:10:"Cache_Lite";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Net_CheckIP";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.3";s:4:"name";s:12:"HTTP_Request";}i:4;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.6";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_finger/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Finger</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>The PEAR::Net_Finger class provides a tool for querying Finger Servers</s>
 <d>Wrapper class for finger calls.</d>
 <r xlink:href="/rest/r/net_finger"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_finger/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Finger</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_finger/deps.1.0.0.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:10:"Net_Socket";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_ftp/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_FTP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Net_FTP provides an OO interface to the PHP FTP functions and some more advanced features in addition.</s>
 <d>Net_FTP allows you to communicate with FTP servers in a more comfortable way
than the native FTP functions of PHP do. The class implements everything nativly
supported by PHP and additionally features like recursive up- and downloading,
dircreation and chmodding. It although implements an observer pattern to allow
for example the view of a progress bar.</d>
 <r xlink:href="/rest/r/net_ftp"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ftp/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_FTP</p>
 <c>pear.php.net</c>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.3.0RC2</v><s>beta</s></r>
 <r><v>1.3.0RC1</v><s>stable</s></r>
 <r><v>1.3.0beta4</v><s>beta</s></r>
 <r><v>1.3.0beta3</v><s>beta</s></r>
 <r><v>1.3.0beta2</v><s>beta</s></r>
 <r><v>1.3.0beta1</v><s>beta</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.9</v><s>beta</s></r>
 <r><v>0.5</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ftp/deps.1.3.1.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.2.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:9:"extension";a:1:{s:4:"name";s:3:"ftp";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_ftp2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_FTP2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP 3.0</l>
 <s>Net_FTP2 provides an OO interface for communication with FTP servers.</s>
 <d>Net_FTP2 is the successor of Net_FTP2. It offers comfortable communication with FTP servers based on a driver based architecture, allowing you to talk FTP even if ext/FTP is not installed. A new, flexible API allows you to only load needed functionality.</d>
 <r xlink:href="/rest/r/net_ftp2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ftp2/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_gameserverquery/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_GameServerQuery</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>An interface to query and return information from a game server</s>
 <d>Net_GameServerQuery provides an interface for querying game servers</d>
 <r xlink:href="/rest/r/net_gameserverquery"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_gameserverquery/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_GameServerQuery</p>
 <c>pear.php.net</c>
 <r><v>0.2.0</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_gameserverquery/deps.0.2.0.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_geo/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Geo</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP</l>
 <s>Geographical locations based on Internet address</s>
 <d>Obtains geogrphical information based on IP number, domain name,
or AS number. Makes use of CAIDA Net_Geo lookup or locaizer extension.</d>
 <r xlink:href="/rest/r/net_geo"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_geo/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Geo</p>
 <c>pear.php.net</c>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_geo/deps.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_geoip/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_GeoIP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>LGPL 2.1</l>
 <s>Library to perform geo-location lookups of IP addresses.</s>
 <d>A library that uses Maxmind\'s GeoIP databases to accurately determine geographic location of an IP address.</d>
 <r xlink:href="/rest/r/net_geoip"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_geoip/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_GeoIP</p>
 <c>pear.php.net</c>
 <r><v>0.9.0alpha1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_geoip/deps.0.9.0alpha1.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_hl7/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_HL7</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>HL7 messaging API.</s>
 <d>This package provides an HL7 API for creating, sending and
manipulating HL7 messages. HL7 is a protocol on the 7th OSI layer
(hence the \'7\' in HL7) for messaging in Health Care
environments. HL7 means \'Health Level 7\'. HL7 is a protocol with a
wealth of semantics that defines hundreds of different messages and
their meaning, but also defines the syntactics of composing and
sending messages.  The API is focused on the syntactic level of
HL7, so as to remain as flexible as possible. The package is a
translation of the Perl HL7 Toolkit and will be kept in sync with
this initiative.</d>
 <r xlink:href="/rest/r/net_hl7"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_hl7/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_HL7</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_hl7/deps.0.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_ident/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Ident</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP</l>
 <s>Identification Protocol implementation</s>
 <d>The PEAR::Net_Ident implements Identification Protocol according
to RFC 1413.
The Identification Protocol (a.k.a., &quot;ident&quot;, a.k.a., &quot;the Ident
Protocol&quot;) provides a means to determine the identity of a user
of a particular TCP connection. Given a TCP port number pair, it
returns a character string which identifies the owner of that
connection on the server\'s system.</d>
 <r xlink:href="/rest/r/net_ident"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ident/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Ident</p>
 <c>pear.php.net</c>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ident/deps.1.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_idna/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_IDNA</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>LGPL</l>
 <s>Punycode encoding and decoding.</s>
 <d>This package helps you to encode and decode punycode strings easily.</d>
 <r xlink:href="/rest/r/net_idna"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_idna/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_IDNA</p>
 <c>pear.php.net</c>
 <r><v>0.7.1</v><s>beta</s></r>
 <r><v>0.7.0</v><s>beta</s></r>
 <r><v>0.6.0</v><s>beta</s></r>
 <r><v>0.5.0</v><s>beta</s></r>
 <r><v>0.4.0</v><s>beta</s></r>
 <r><v>0.3.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_idna/deps.0.7.1.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:2:"no";}i:2;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:8:"5.0.0RC1";s:8:"optional";s:3:"yes";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_imap/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_IMAP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Provides an implementation of the IMAP protocol</s>
 <d>Provides an implementation of the IMAP4Rev1 protocol using PEAR\'s Net_Socket and the optional Auth_SASL class.</d>
 <r xlink:href="/rest/r/net_imap"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_imap/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_IMAP</p>
 <c>pear.php.net</c>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.7.1</v><s>beta</s></r>
 <r><v>0.7</v><s>beta</s></r>
 <r><v>0.6</v><s>beta</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_imap/deps.1.0.3.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:10:"Net_Socket";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_ipv4/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_IPv4</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP 2.0</l>
 <s>IPv4 network calculations and validation</s>
 <d>Class used for calculating IPv4 (AF_INET family) address information
such as network as network address, broadcast address, and IP address
validity.</d>
 <r xlink:href="/rest/r/net_ipv4"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ipv4/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_IPv4</p>
 <c>pear.php.net</c>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ipv4/deps.1.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_ipv6/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_IPv6</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Check and validate IPv6 addresses</s>
 <d>The class allows you to:
* check if an address is an IPv6 address
* compress/uncompress IPv6 addresses
* check for an IPv4 compatible ending in an IPv6 adresse</d>
 <r xlink:href="/rest/r/net_ipv6"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ipv6/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_IPv6</p>
 <c>pear.php.net</c>
 <r><v>1.0.5</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ipv6/deps.1.0.5.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_irc/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_IRC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>IRC Client Class</s>
 <d>IRC Client Class suitable for both client or bots applications.
Features are:
- Supprts Multiple Server connections
- Non-blocking sockets
- Runs on Standard PHP installation without any Extensions
- Server messages handled by a callback system
- Full logging capabilities
- Full statistic collector

Note: Net_IRC is no longer actively maintained. Please see Net_SmartIRC (http://pear.php.net/Net_SmartIRC)</d>
 <r xlink:href="/rest/r/net_irc"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_irc/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_IRC</p>
 <c>pear.php.net</c>
 <r><v>0.0.7</v><s>beta</s></r>
 <r><v>0.0.6</v><s>alpha</s></r>
 <r><v>0.0.3</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_irc/deps.0.0.7.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_ldap/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_LDAP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>LGPL</l>
 <s>OO interface for searching and manipulating LDAP-entries</s>
 <d>Net Ldap is a clone of Perls Net::LDAP object interface to
ldapservers. It does not contain all of Net::LDAPs features,
but has:
* A simple OO-interface to connections, searches and entries.
* Support for tls and ldap v3.
* Simple modification, deletion and creation of ldapentries.
* Support for schema handling.

Net_LDAP layers itself on top of PHP\'s existing ldap extensions.</d>
 <r xlink:href="/rest/r/net_ldap"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ldap/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_LDAP</p>
 <c>pear.php.net</c>
 <r><v>0.6.6</v><s>beta</s></r>
 <r><v>0.6.5</v><s>beta</s></r>
 <r><v>0.6.4</v><s>beta</s></r>
 <r><v>0.6.3</v><s>beta</s></r>
 <r><v>0.6.2</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ldap/deps.0.6.6.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_lmtp/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_LMTP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Provides an implementation of the RFC2033 LMTP protocol</s>
 <d>Provides an implementation of the RFC2033 LMTP using PEAR\'s Net_Socket and Auth_SASL class.</d>
 <r xlink:href="/rest/r/net_lmtp"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_lmtp/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_LMTP</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>0.7.0</v><s>stable</s></r>
 <r><v>0.6</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_lmtp/deps.1.0.1.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:10:"Net_Socket";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_monitor/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Monitor</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Remote Service Monitor</s>
 <d>A unified interface for checking the availability services on external servers and sending meaningful alerts through a variety of media if a service becomes unavailable.</d>
 <r xlink:href="/rest/r/net_monitor"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_monitor/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Monitor</p>
 <c>pear.php.net</c>
 <r><v>0.2.3</v><s>beta</s></r>
 <r><v>0.2.2</v><s>beta</s></r>
 <r><v>0.2.1</v><s>beta</s></r>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
 <r><v>0.0.8</v><s>beta</s></r>
 <r><v>0.0.7</v><s>beta</s></r>
 <r><v>0.0.6</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_monitor/deps.0.2.3.txt", 'a:7:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.0";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.0";s:8:"optional";s:3:"yes";s:4:"name";s:4:"Mail";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.0.1";s:8:"optional";s:3:"yes";s:4:"name";s:7:"Net_SMS";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.2";s:8:"optional";s:3:"yes";s:4:"name";s:8:"Net_SMTP";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.4";s:8:"optional";s:3:"yes";s:4:"name";s:12:"HTTP_Request";}i:6;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:8:"1.3.0RC1";s:8:"optional";s:3:"yes";s:4:"name";s:7:"Net_FTP";}i:7;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.0.3";s:8:"optional";s:3:"yes";s:4:"name";s:7:"Net_DNS";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_nntp/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_NNTP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>W3C</l>
 <s>Implementation of the NNTP protocol</s>
 <d>Package for communicating with NNTP/Usenet servers

-----------------------------

This package uses a rather conservative release cycle: New features won\'t go beta until they are truly ready for use in a production environment.

-----------------------------

The following branches is being actively maintained, each having different states:

STABLE (v1.0)
- Download: http://pear.php.net/get/Net_NNTP
- Backward compatibility with the v0.2 releases.
- Implementation of every standard/base NNTP command.
- The Net_NNTP_Protocol_Client class is considered internal and subject to changes, since it\'s currently incomplete but very functional and well tested.

BETA (v1.1)
- Download: http://pear.php.net/get/Net_NNTP-beta
- Includes a few experimental features from v0.11, which is not included in the v1.0 release, since they are not considered fully mature yet.
- Road-map: Parsing of message data via external classes (stripped down versions of methods from the v1.2 API).

ALPHA (v1.2)
- Download: http://pear.php.net/get/Net_NNTP-alpha
- Includes the classes Net_NNTP_Message and Net_NNTP_Header (which has been stable for quite some, but still considered experimental, since a possible minor BC-break would result in a Net_NNTP2).
- Road-map: Own MIME implementation.

DEVEL (v1.3)
- Road-map: Possibly a NNTP-server, but honestly no current plans.

-----------------------------

NOTE: New minor features, which are not mentioned in the road-map, and which has been tested in the alpha releases, might also become part of the stable or the beta releases.

NOTE: The Protocol implementation (Net_NNTP_Protocol_Client) is identical in current releases (v1.0, v1.1 and v1.2). The difference between these releases is additional fetures in the the pulbic API (Net_NNTP_Client). Modifications to Net_NNTP_Protocol_Client in v1.2 is most likeley to be copied to both v1.1 and v1.0 when they have been well tested...

-----------------------------

Note:
A PHP5-only version of Net_NNTP has been under development and functional since PHP5 beta3 (fall 2003), but due to lack of time etc. this project is currently unpublished...</d>
 <r xlink:href="/rest/r/net_nntp"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_nntp/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_NNTP</p>
 <c>pear.php.net</c>
 <r><v>1.1.2</v><s>beta</s></r>
 <r><v>1.2.3</v><s>alpha</s></r>
 <r><v>1.2.2</v><s>alpha</s></r>
 <r><v>1.1.1</v><s>beta</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.2.1</v><s>alpha</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.2.0</v><s>alpha</s></r>
 <r><v>1.1.0</v><s>beta</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.11.3</v><s>beta</s></r>
 <r><v>0.11.2</v><s>alpha</s></r>
 <r><v>0.11.1</v><s>devel</s></r>
 <r><v>0.11.0</v><s>devel</s></r>
 <r><v>0.2.5</v><s>stable</s></r>
 <r><v>0.10.3</v><s>alpha</s></r>
 <r><v>0.10.2</v><s>alpha</s></r>
 <r><v>0.10.1</v><s>alpha</s></r>
 <r><v>0.10.0</v><s>alpha</s></r>
 <r><v>0.2.3</v><s>stable</s></r>
 <r><v>0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_nntp/deps.1.1.2.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.3";s:4:"name";s:10:"Net_Socket";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_ping/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Ping</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Execute ping</s>
 <d>OS independet wrapper class for executing ping calls</d>
 <r xlink:href="/rest/r/net_ping"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ping/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Ping</p>
 <c>pear.php.net</c>
 <r><v>2.4</v><s>stable</s></r>
 <r><v>2.3</v><s>stable</s></r>
 <r><v>2.2</v><s>stable</s></r>
 <r><v>2.1</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ping/deps.2.4.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_pop3/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_POP3</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>BSD</l>
 <s>Provides a POP3 class to access POP3 server.</s>
 <d>Provides a POP3 class to access POP3 server. Support all POP3 commands
including UIDL listings, APOP authentication,DIGEST-MD5 and CRAM-MD5 using optional Auth_SASL package</d>
 <r xlink:href="/rest/r/net_pop3"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_pop3/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_POP3</p>
 <c>pear.php.net</c>
 <r><v>1.3.6</v><s>stable</s></r>
 <r><v>1.3.5</v><s>stable</s></r>
 <r><v>1.3.4</v><s>beta</s></r>
 <r><v>1.3.3</v><s>stable</s></r>
 <r><v>1.3.2</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_pop3/deps.1.3.6.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:10:"Net_Socket";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_portscan/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Portscan</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP 2.02</l>
 <s>Portscanner utilities.</s>
 <d>The Net_Portscan package allows one to perform basic portscanning
functions with PHP. It supports checking an individual port or
checking a whole range of ports on a machine.</d>
 <r xlink:href="/rest/r/net_portscan"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_portscan/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Portscan</p>
 <c>pear.php.net</c>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_portscan/deps.1.0.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_server/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Server</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Generic server class</s>
 <d>Generic server class based on ext/sockets, used to develop any kind of server.</d>
 <r xlink:href="/rest/r/net_server"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_server/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Server</p>
 <c>pear.php.net</c>
 <r><v>0.12.0</v><s>alpha</s></r>
 <r><v>0.11.5</v><s>alpha</s></r>
 <r><v>0.11.4</v><s>alpha</s></r>
 <r><v>0.11.3</v><s>alpha</s></r>
 <r><v>0.11.2</v><s>alpha</s></r>
 <r><v>0.11</v><s>alpha</s></r>
 <r><v>0.10</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_server/deps.0.12.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:7:"sockets";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:5:"pcntl";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_sieve/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Sieve</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>BSD</l>
 <s>Handles talking to timsieved</s>
 <d>Provides an API to talk to the timsieved server that comes
with Cyrus IMAPd. Can be used to install, remove, mark active etc
sieve scripts.</d>
 <r xlink:href="/rest/r/net_sieve"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_sieve/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Sieve</p>
 <c>pear.php.net</c>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.9.2</v><s>stable</s></r>
 <r><v>0.9.1</v><s>stable</s></r>
 <r><v>0.9.0</v><s>stable</s></r>
 <r><v>0.8.1</v><s>stable</s></r>
 <r><v>0.8</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_sieve/deps.1.1.1.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:10:"Net_Socket";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_smartirc/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_SmartIRC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>LGPL</l>
 <s>Net_SmartIRC is a PHP class for communication with IRC networks</s>
 <d>Net_SmartIRC is a PHP class for communication with IRC networks,
which conforms to the RFC 2812 (IRC protocol).
It\'s an API that handles all IRC protocol messages.
This class is designed for creating IRC bots, chats and show irc related info on webpages.

Full featurelist of Net_SmartIRC
-------------------------------------
- full object oriented programmed
- every received IRC message is parsed into an ircdata object
  (it contains following info: from, nick, ident, host, channel, message, type, rawmessage)
- actionhandler for the API
  on different types of messages (channel/notice/query/kick/join..) callbacks can be registered
- messagehandler for the API
  class based messagehandling, using IRC reply codes
- time events
  callbacks to methods in intervals
- send/receive floodprotection
- detects and changes nickname on nickname collisions
- autoreconnect, if connection is lost
- autoretry for connecting to IRC servers
- debugging/logging system with log levels (destination can be file, stdout, syslog or browserout)
- supports fsocks and PHP socket extension
- supports PHP 4.1.x to 4.3.2 (also PHP 5.0.0b1)
- sendbuffer with a queue that has 3 priority levels (high, medium, low) plus a bypass level (critical)
- channel syncing (tracking of users/modes/topic etc in objects)
- user syncing (tracking the user in channels, nick/ident/host/realname/server/hopcount in objects)
- when channel syncing is acticated the following functions are available:
  isJoined
  isOpped
  isVoiced
  isBanned
- on reconnect all joined channels will be rejoined, also when keys are used
- own CTCP version reply can be set
- IRC commands:
  pass
  op
  deop
  voice
  devoice
  ban
  unban
  join
  part
  action
  message
  notice
  query
  ctcp
  mode
  topic
  nick
  invite
  list
  names
  kick
  who
  whois
  whowas
  quit</d>
 <r xlink:href="/rest/r/net_smartirc"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smartirc/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_SmartIRC</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.5.5p1</v><s>stable</s></r>
 <r><v>0.5.5</v><s>stable</s></r>
 <r><v>0.5.1</v><s>stable</s></r>
 <r><v>0.5.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smartirc/deps.1.0.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_smpp/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_SMPP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License v3.0</l>
 <s>SMPP v3.4 protocol implementation</s>
 <d>Net_SMPP is an implementation of the SMPP (Short Message Peer-to-Peer) v3.4 protocol. SMPP is an open protocol used in the wireless industry to send and receive SMS messages.
Net_SMPP does not provide a SMPP client or server, but they can easily be built with it.</d>
 <r xlink:href="/rest/r/net_smpp"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smpp/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_SMPP</p>
 <c>pear.php.net</c>
 <r><v>0.4.4</v><s>beta</s></r>
 <r><v>0.4.3</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smpp/deps.0.4.4.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.3";s:4:"name";s:4:"PEAR";}i:2;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_smpp_client/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_SMPP_Client</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License v3.0</l>
 <s>SMPP v3.4 client</s>
 <d>Net_SMPP_Client is a package for communicating with SMPP servers, built with
Net_SMPP. It can be used to send SMS messages, among other things.

    Features:
    - PDU stack keeps track of which PDUs have crossed the wire
    - Keeps track of the connection state, and won\'t let you send PDUs if
      the state is incorrect.
    - Supports SMPP vendor extensions.</d>
 <r xlink:href="/rest/r/net_smpp_client"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smpp_client/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_SMPP_Client</p>
 <c>pear.php.net</c>
 <r><v>0.3.2</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smpp_client/deps.0.3.2.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.3";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.0";s:4:"name";s:10:"Net_Socket";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.4.1";s:4:"name";s:8:"Net_SMPP";}i:4;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_sms/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_SMS</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>LGPL</l>
 <s>SMS functionality.</s>
 <d>This package provides SMS functionality and access to SMS gateways.</d>
 <r xlink:href="/rest/r/net_sms"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_sms/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_SMS</p>
 <c>pear.php.net</c>
 <r><v>0.0.2</v><s>beta</s></r>
 <r><v>0.0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_sms/deps.0.0.2.txt", 'a:4:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:7:"gettext";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:12:"HTTP_Request";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:4:"Mail";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_smtp/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_SMTP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Provides an implementation of the SMTP protocol</s>
 <d>Provides an implementation of the SMTP protocol using PEAR\'s Net_Socket class.</d>
 <r xlink:href="/rest/r/net_smtp"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smtp/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_SMTP</p>
 <c>pear.php.net</c>
 <r><v>1.2.7</v><s>stable</s></r>
 <r><v>1.2.6</v><s>stable</s></r>
 <r><v>1.2.5</v><s>stable</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_smtp/deps.1.2.7.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:10:"Net_Socket";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:9:"Auth_SASL";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_socket/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Socket</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Network Socket Interface</s>
 <d>Net_Socket is a class interface to TCP sockets.  It provides blocking
and non-blocking operation, with different reading and writing modes
(byte-wise, block-wise, line-wise and special formats like network
byte-order ip addresses).</d>
 <r xlink:href="/rest/r/net_socket"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_socket/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Socket</p>
 <c>pear.php.net</c>
 <r><v>1.0.5</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_socket/deps.1.0.6.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_traceroute/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Traceroute</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Execute traceroute</s>
 <d>OS independet wrapper class for executing traceroute calls</d>
 <r xlink:href="/rest/r/net_traceroute"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_traceroute/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Traceroute</p>
 <c>pear.php.net</c>
 <r><v>0.21</v><s>alpha</s></r>
 <r><v>0.20</v><s>alpha</s></r>
 <r><v>0.11</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_traceroute/deps.0.21.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_url/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_URL</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>BSD</l>
 <s>Easy parsing of Urls</s>
 <d>Provides easy parsing of URLs and their constituent parts.</d>
 <r xlink:href="/rest/r/net_url"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_url/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_URL</p>
 <c>pear.php.net</c>
 <r><v>1.0.14</v><s>stable</s></r>
 <r><v>1.0.13</v><s>stable</s></r>
 <r><v>1.0.12</v><s>stable</s></r>
 <r><v>1.0.11</v><s>stable</s></r>
 <r><v>1.0.10</v><s>stable</s></r>
 <r><v>1.0.9</v><s>stable</s></r>
 <r><v>1.0.8</v><s>stable</s></r>
 <r><v>1.0.7</v><s>stable</s></r>
 <r><v>1.0.6</v><s>stable</s></r>
 <r><v>1.0.5</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_url/deps.1.0.14.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_useragent_detect/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_UserAgent_Detect</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Net_UserAgent_Detect determines the Web browser, version, and platform from an HTTP user agent string</s>
 <d>The Net_UserAgent object does a number of tests on an HTTP user
agent string.  The results of these tests are available via methods of
the object.

This module is based upon the JavaScript browser detection code
available at http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html.
This module had many influences from the lib/Browser.php code in
version 1.3 of Horde.</d>
 <r xlink:href="/rest/r/net_useragent_detect"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_useragent_detect/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_UserAgent_Detect</p>
 <c>pear.php.net</c>
 <r><v>2.1.0</v><s>stable</s></r>
 <r><v>2.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_useragent_detect/deps.2.1.0.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_useragent_mobile/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_UserAgent_Mobile</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>HTTP mobile user agent string parser</s>
 <d>Net_UserAgent_Mobile parses HTTP_USER_AGENT strings of (mainly Japanese)
mobile HTTP user agents. It\'ll be useful in page dispatching by user agents.
This package was ported from Perl\'s HTTP::MobileAgent.
See http://search.cpan.org/search?mode=module&amp;query=HTTP-MobileAgent
The author of the HTTP::MobileAgent module is Tatsuhiko Miyagawa
&lt;miyagawa@bulknews.net&gt;</d>
 <r xlink:href="/rest/r/net_useragent_mobile"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_useragent_mobile/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_UserAgent_Mobile</p>
 <c>pear.php.net</c>
 <r><v>0.25.0</v><s>beta</s></r>
 <r><v>0.24.0</v><s>beta</s></r>
 <r><v>0.23.0</v><s>beta</s></r>
 <r><v>0.22.0</v><s>beta</s></r>
 <r><v>0.21.0</v><s>beta</s></r>
 <r><v>0.20.0</v><s>beta</s></r>
 <r><v>0.19</v><s>beta</s></r>
 <r><v>0.18</v><s>beta</s></r>
 <r><v>0.17</v><s>beta</s></r>
 <r><v>0.16</v><s>beta</s></r>
 <r><v>0.15</v><s>beta</s></r>
 <r><v>0.14.1</v><s>beta</s></r>
 <r><v>0.14</v><s>beta</s></r>
 <r><v>0.13</v><s>beta</s></r>
 <r><v>0.12</v><s>beta</s></r>
 <r><v>0.11</v><s>beta</s></r>
 <r><v>0.10</v><s>beta</s></r>
 <r><v>0.9</v><s>beta</s></r>
 <r><v>0.8</v><s>beta</s></r>
 <r><v>0.7</v><s>beta</s></r>
 <r><v>0.6</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_useragent_mobile/deps.0.25.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_whois/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Whois</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP</l>
 <s>The PEAR::Net_Whois class provides a tool to query internet domain name and network number directory services</s>
 <d>The PEAR::Net_Whois looks up records in the databases maintained by several Network Information Centers (NICs).</d>
 <r xlink:href="/rest/r/net_whois"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_whois/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Whois</p>
 <c>pear.php.net</c>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_whois/deps.1.0.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:10:"Net_Socket";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_wifi/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_Wifi</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Scans for wireless networks</s>
 <d>Net_Wifi utilizes the command line tools &quot;iwconfig&quot; and &quot;iwlist&quot; to get information
    about wireless lan interfaces on the system and the current configuration.
    The class enables you to scan for wireless networks
    and get a bunch of information about them.</d>
 <r xlink:href="/rest/r/net_wifi"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_wifi/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_Wifi</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_wifi/deps.0.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/numbers_roman/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Numbers_Roman</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Numbers">Numbers</ca>
 <l>PHP</l>
 <s>Provides methods for converting to and from Roman Numerals.</s>
 <d>Numbers_Roman provides static methods for converting to and from Roman
numerals. It supports Roman numerals in both uppercase and lowercase
styles and conversion for and to numbers up to 5 999 999</d>
 <r xlink:href="/rest/r/numbers_roman"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/numbers_roman/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Numbers_Roman</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>beta</s></r>
 <r><v>0.2.0</v><s>stable</s></r>
 <r><v>0.1.1</v><s>stable</s></r>
 <r><v>0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/numbers_roman/deps.1.0.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/numbers_words/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Numbers_Words</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Numbers">Numbers</ca>
 <l>PHP License</l>
 <s>The PEAR Numbers_Words package provides methods for spelling numerals in words.</s>
 <d>With Numbers_Words class you can convert numbers
written in arabic digits to words in several languages.
You can convert an integer between -infinity and infinity.
If your system does not support such long numbers you can
call Numbers_Words::toWords() with just a string.

With the Numbers_Words::toCurrency($num, $locale, \'USD\') method
you can convert a number (decimal and fraction part) to words with currency name.

The following languages are supported:
    * bg (Bulgarian) by Kouber Saparev
    * cs (Czech) by Petr \'PePa\' Pavel
    * de (German) by me
    * dk (Danish) by Jesper Veggerby
    * ee (Estonian) by Erkki Saarniit
    * en_100 (Donald Knuth system, English) by me
    * en_GB (British English) by me
    * en_US (American English) by me
    * es (Spanish Castellano) by Xavier Noguer
    * es_AR (Argentinian Spanish) by Martin Marrese
    * fr (French) by Kouber Saparev
    * fr_BE (French Belgium) by Kouber Saparev and Philippe Bajoit
    * he (Hebrew) by Hadar Porat
    * hu_HU (Hungarian) by Nils Homp
    * id (Indonesian) by Ernas M. Jamil and Arif Rifai Dwiyanto
    * it_IT (Italian) by Filippo Beltramini and Davide Caironi
    * lt (Lithuanian) by Laurynas Butkus
    * pl (Polish) by me
    * pt_BR (Brazilian Portuguese) by Marcelo Subtil Marcal and Mario H.C.T.
    * ru (Russian) by Andrey Demenev
    * sv (Swedish) by Robin Ericsson</d>
 <r xlink:href="/rest/r/numbers_words"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/numbers_words/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Numbers_Words</p>
 <c>pear.php.net</c>
 <r><v>0.14.0</v><s>beta</s></r>
 <r><v>0.13.1</v><s>beta</s></r>
 <r><v>0.13.0</v><s>beta</s></r>
 <r><v>0.12.0</v><s>beta</s></r>
 <r><v>0.11.0</v><s>beta</s></r>
 <r><v>0.10.1</v><s>beta</s></r>
 <r><v>0.10.0</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
 <r><v>0.8.1</v><s>beta</s></r>
 <r><v>0.8</v><s>beta</s></r>
 <r><v>0.7.1</v><s>beta</s></r>
 <r><v>0.7</v><s>beta</s></r>
 <r><v>0.6</v><s>beta</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3.1</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/numbers_words/deps.0.14.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/ole/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>OLE</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Structures">Structures</ca>
 <l>PHP</l>
 <s>Package for reading and writing OLE containers</s>
 <d>This package allows reading and writing of OLE (Object Linking and Embedding) files, the format used as container for Excel, Word and other MS file formats.
 Documentation for the OLE format can be found at: http://user.cs.tu-berlin.de/~schwartz/pmh/guide.html</d>
 <r xlink:href="/rest/r/ole"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/ole/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>OLE</p>
 <c>pear.php.net</c>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2.1</v><s>alpha</s></r>
 <r><v>0.2</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/ole/deps.0.5.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pager/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Pager</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>Data paging class</s>
 <d>It takes an array of data as input and pages it according to various parameters.
It also builds links within a specified range, and allows complete customization of the output (it even works with front controllers and mod_rewrite).
Two operating modes available: &quot;Jumping&quot; and &quot;Sliding&quot; window style.</d>
 <r xlink:href="/rest/r/pager"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pager/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Pager</p>
 <c>pear.php.net</c>
 <r><v>2.3.3</v><s>stable</s></r>
 <r><v>2.3.2</v><s>stable</s></r>
 <r><v>2.3.1</v><s>stable</s></r>
 <r><v>2.3.0</v><s>stable</s></r>
 <r><v>2.3.0RC2</v><s>beta</s></r>
 <r><v>2.3.0RC1</v><s>beta</s></r>
 <r><v>2.2.7</v><s>stable</s></r>
 <r><v>2.2.6</v><s>stable</s></r>
 <r><v>2.2.5</v><s>stable</s></r>
 <r><v>2.2.4</v><s>stable</s></r>
 <r><v>2.2.3</v><s>stable</s></r>
 <r><v>2.2.2</v><s>stable</s></r>
 <r><v>2.2.1</v><s>stable</s></r>
 <r><v>2.2.0</v><s>stable</s></r>
 <r><v>2.1.0</v><s>stable</s></r>
 <r><v>2.0</v><s>stable</s></r>
 <r><v>1.0.8</v><s>stable</s></r>
 <r><v>1.0.7</v><s>stable</s></r>
 <r><v>1.0.6</v><s>stable</s></r>
 <r><v>1.0.5</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pager/deps.2.3.3.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pager_sliding/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Pager_Sliding</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/HTML">HTML</ca>
 <l>PHP License</l>
 <s>Sliding Window Pager.</s>
 <d>It takes an array of data as input and page it according to various parameters. It also builds links within a specified range, and allows complete customization of the output (it even works with mod_rewrite). It is compatible with PEAR::Pager\'s API.

[Deprecated]Use PEAR::Pager v2.x with $mode = \'Sliding\' instead</d>
 <r xlink:href="/rest/r/pager_sliding"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pager_sliding/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Pager_Sliding</p>
 <c>pear.php.net</c>
 <r><v>1.6</v><s>stable</s></r>
 <r><v>1.5</v><s>stable</s></r>
 <r><v>1.4</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.1.6</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pager_sliding/deps.1.6.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/payment_clieop/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Payment_Clieop</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Payment">Payment</ca>
 <l>PHP</l>
 <s>These classes can create a clieop03 file for you which you can send to a Dutch Bank. Ofcourse you need also a Dutch bank account.</s>
 <d>Clieop03 generation classes</d>
 <r xlink:href="/rest/r/payment_clieop"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_clieop/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Payment_Clieop</p>
 <c>pear.php.net</c>
 <r><v>0.1.1</v><s>stable</s></r>
 <r><v>0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_clieop/deps.0.1.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/payment_dta/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Payment_DTA</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Payment">Payment</ca>
 <l>BSD style</l>
 <s>Creates DTA files containing money transaction data (Germany).</s>
 <d>Payment_DTA provides functions to create DTA files used in Germany to exchange informations about money transactions with banks or online banking programs.</d>
 <r xlink:href="/rest/r/payment_dta"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_dta/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Payment_DTA</p>
 <c>pear.php.net</c>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.00</v><s>stable</s></r>
 <r><v>0.81</v><s>beta</s></r>
 <r><v>0.8</v><s>beta</s></r>
 <r><v>0.71</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_dta/deps.1.2.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/payment_process/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Payment_Process</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Payment">Payment</ca>
 <l>PHP License, v3.0</l>
 <s>Unified payment processor</s>
 <d>Payment_Process is a gateway-independent framework for processing credit cards, e-checks and eventually other forms of payments as well.</d>
 <r xlink:href="/rest/r/payment_process"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_process/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Payment_Process</p>
 <c>pear.php.net</c>
 <r><v>0.6.2</v><s>beta</s></r>
 <r><v>0.6.1</v><s>beta</s></r>
 <r><v>0.6.0</v><s>beta</s></r>
 <r><v>0.5.8</v><s>beta</s></r>
 <r><v>0.5.7</v><s>beta</s></r>
 <r><v>0.5.6</v><s>beta</s></r>
 <r><v>0.5.5</v><s>beta</s></r>
 <r><v>0.5.2</v><s>beta</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/payment_process/deps.0.6.2.txt", 'a:5:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.6";s:8:"optional";s:3:"yes";s:4:"name";s:10:"XML_Parser";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.0";s:8:"optional";s:3:"yes";s:4:"name";s:8:"Net_Curl";}i:3;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.5.0";s:8:"optional";s:2:"no";s:4:"name";s:8:"Validate";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.5.0";s:8:"optional";s:2:"no";s:4:"name";s:27:"Validate_Finance_CreditCard";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the beta-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling class
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class</d>
 <r xlink:href="/rest/r/pear"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
 <r><v>1.3.4</v><s>stable</s></r>
 <r><v>1.3.3.1</v><s>stable</s></r>
 <r><v>1.3.3</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.3b6</v><s>beta</s></r>
 <r><v>1.3b5</v><s>beta</s></r>
 <r><v>1.3b3</v><s>beta</s></r>
 <r><v>1.3b2</v><s>beta</s></r>
 <r><v>1.3b1</v><s>beta</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.2b5</v><s>beta</s></r>
 <r><v>1.2b4</v><s>beta</s></r>
 <r><v>1.2b3</v><s>beta</s></r>
 <r><v>1.2b2</v><s>beta</s></r>
 <r><v>1.2b1</v><s>beta</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>1.0b3</v><s>stable</s></r>
 <r><v>1.0b2</v><s>stable</s></r>
 <r><v>1.0b1</v><s>stable</s></r>
 <r><v>0.90</v><s>beta</s></r>
 <r><v>0.11</v><s>beta</s></r>
 <r><v>0.10</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0.txt", 'a:2:{s:8:"required";a:4:{s:3:"php";a:1:{s:3:"min";s:3:"4.2";}s:13:"pearinstaller";a:1:{s:3:"min";s:8:"1.4.0a12";}s:7:"package";a:5:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.4.0";s:11:"recommended";s:5:"1.4.1";}i:3;a:5:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"max";s:5:"0.5.0";s:7:"exclude";s:5:"0.5.0";s:9:"conflicts";s:0:"";}i:4;a:5:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"max";s:5:"0.4.0";s:7:"exclude";s:5:"0.4.0";s:9:"conflicts";s:0:"";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:5:"group";a:2:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_delegator/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_Delegator</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>Delegation for PHP</s>
 <d>This package implements traditional and unorthodox delegation in PHP. This allows for pseudo multiple inheritance and other interesting design paradigms.</d>
 <r xlink:href="/rest/r/pear_delegator"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_delegator/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_Delegator</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_delegator/deps.0.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_errorstack/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_ErrorStack</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>Advanced successor to PEAR_Error</s>
 <d>PEAR_ErrorStack provides a stack-based approach to error raising and also seeks to unify diverse error raising solutions into one location in order to link unrelated projects into a single application</d>
 <r xlink:href="/rest/r/pear_errorstack"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_errorstack/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_frontend_gtk/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_Frontend_Gtk</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>Gtk (Desktop) PEAR Package Manager</s>
 <d>Desktop Interface to the PEAR Package Manager, Requires PHP-GTK</d>
 <r xlink:href="/rest/r/pear_frontend_gtk"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_Frontend_Gtk</p>
 <c>pear.php.net</c>
 <r><v>0.4.0</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>snapshot</s></r>
 <r><v>0.1</v><s>snapshot</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/deps.0.4.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_frontend_web/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_Frontend_Web</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>HTML (Web) PEAR Package Manager</s>
 <d>Web Interface to the PEAR Package Manager</d>
 <r xlink:href="/rest/r/pear_frontend_web"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_web/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_Frontend_Web</p>
 <c>pear.php.net</c>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2.2</v><s>beta</s></r>
 <r><v>0.2.1</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_web/deps.0.4.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:20:"Net_UserAgent_Detect";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:16:"HTML_Template_IT";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_info/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_Info</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>Show Information about your PEAR install and its packages</s>
 <d>This package generates a comprehensive information page for your current PEAR install.
* The format for the page is similar to that for phpinfo() except using PEAR colors.
* Has complete PEAR Credits (based on the packages you have installed).
* Will show if there is a newer version than the one presently installed (and what its state is)
* Each package has an anchor in the form pkg_PackageName - where PackageName is a case-sensitive PEAR package name</d>
 <r xlink:href="/rest/r/pear_info"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_info/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_Info</p>
 <c>pear.php.net</c>
 <r><v>1.6.0</v><s>stable</s></r>
 <r><v>1.5.2</v><s>stable</s></r>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5</v><s>stable</s></r>
 <r><v>1.0.6</v><s>stable</s></r>
 <r><v>1.0.5</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_info/deps.1.6.0.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.2";s:4:"name";s:4:"PEAR";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_packagefilemanager/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_PackageFileManager</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>PEAR_PackageFileManager takes an existing package.xml file and updates it with a new filelist and changelog</s>
 <d>This package revolutionizes the maintenance of PEAR packages.  With a few parameters,
the entire package.xml is automatically updated with a listing of all files in a package.
Features include
 - manages the new package.xml 2.0 format in PEAR 1.4.0
 - can detect PHP and extension dependencies using PHP_CompatInfo
 - reads in an existing package.xml file, and only changes the release/changelog
 - a plugin system for retrieving files in a directory.  Currently two plugins
   exist, one for standard recursive directory content listing, and one that
   reads the CVS/Entries files and generates a file listing based on the contents
   of a checked out CVS repository
 - incredibly flexible options for assigning install roles to files/directories
 - ability to ignore any file based on a * ? wildcard-enabled string(s)
 - ability to include only files that match a * ? wildcard-enabled string(s)
 - ability to manage dependencies
 - can output the package.xml in any directory, and read in the package.xml
   file from any directory.
 - can specify a different name for the package.xml file

PEAR_PackageFileManager is fully unit tested.
The new PEAR_PackageFileManager2 class is not.</d>
 <r xlink:href="/rest/r/pear_packagefilemanager"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_PackageFileManager</p>
 <c>pear.php.net</c>
 <r><v>1.6.0a3</v><s>alpha</s></r>
 <r><v>1.6.0a2</v><s>alpha</s></r>
 <r><v>1.6.0a1</v><s>alpha</s></r>
 <r><v>1.5.2</v><s>stable</s></r>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5.0</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.15</v><s>beta</s></r>
 <r><v>0.14</v><s>beta</s></r>
 <r><v>0.13</v><s>beta</s></r>
 <r><v>0.12</v><s>beta</s></r>
 <r><v>0.11</v><s>beta</s></r>
 <r><v>0.10</v><s>beta</s></r>
 <r><v>0.9</v><s>alpha</s></r>
 <r><v>0.8</v><s>alpha</s></r>
 <r><v>0.7</v><s>alpha</s></r>
 <r><v>0.6</v><s>alpha</s></r>
 <r><v>0.5</v><s>alpha</s></r>
 <r><v>0.4</v><s>alpha</s></r>
 <r><v>0.3</v><s>alpha</s></r>
 <r><v>0.2</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager/deps.1.6.0a3.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_packagefilemanager_gui_gtk/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_PackageFileManager_GUI_Gtk</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>A PHP-GTK frontend for the PEAR_PackageFileManager class.</s>
 <d>A PHP-GTK 1 frontend for the PEAR_PackageFileManager class. It makes it easier for developers to create and maintain PEAR package.xml files.

Features:
* Update existing package files or create new ones
* Import values from an existing package file
* Drag-n-Drop package directory into the application for easy loading
* Set package level information (package name, description, etc.)
* Set release level information (version, release notes, etc.)
* Easily add maintainers
* Browse package files as a tree and click to add a dependency
* Add install time global and file replacements
* Package file preview window
* Package the package using the new package file</d>
 <r xlink:href="/rest/r/pear_packagefilemanager_gui_gtk"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager_gui_gtk/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_PackageFileManager_GUI_Gtk</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0rc1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_packagefilemanager_gui_gtk/deps.1.0.1.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.5.1";s:4:"name";s:23:"PEAR_PackageFileManager";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.5";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.1.0";s:4:"name";s:12:"Gtk_FileDrop";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_remoteinstaller/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_RemoteInstaller</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>PEAR Remote installation plugin through FTP</s>
 <d>Originally part of the 1.4.0 new features,
remote installation through FTP is now its own package.
This package adds the commands &quot;remote-install&quot; &quot;remote-upgrade&quot;
&quot;remote-uninstall&quot; and &quot;remote-upgrade-all&quot; to the PEAR core.

To take advantage, you must have a config file on the remote
ftp server and full access to the server to create and remove
files.  The config-create command can be used to get started,
and the remote_config configuration variable is set to the
full URL as in &quot;ftp://ftp.example.com/path/to/pear.ini&quot;

After this is done, install/upgrade as normal using the
remote* commands as if they were local.</d>
 <r xlink:href="/rest/r/pear_remoteinstaller"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_remoteinstaller/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_RemoteInstaller</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_remoteinstaller/deps.0.1.0.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:3:"4.2";}s:13:"pearinstaller";a:1:{s:3:"min";s:8:"1.4.0a12";}s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:4:"PEAR";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:7:"1.4.0b1";}i:1;a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.1";}}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/phpdoc/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHPDoc</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>PHP</l>
 <s>Tool to generate documentation from the source</s>
 <d>PHPDoc is an attemt to adopt Javadoc to the PHP world.</d>
 <r xlink:href="/rest/r/phpdoc"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpdoc/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHPDoc</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpdoc/deps.0.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/phpdocumentor/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PhpDocumentor</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Tools+and+Utilities">Tools and Utilities</ca>
 <l>PHP License</l>
 <s>The phpDocumentor package provides automatic documenting of php api directly from the source.</s>
 <d>The phpDocumentor tool is a standalone auto-documentor similar to JavaDoc
written in PHP.  It differs from PHPDoc in that it is MUCH faster, parses a much
wider range of php files, and comes with many customizations including 11 HTML
templates, windows help file CHM output, PDF output, and XML DocBook peardoc2
output for use with documenting PEAR.  In addition, it can do PHPXref source
code highlighting and linking.

Features (short list):
-output in HTML, PDF (directly), CHM (with windows help compiler), XML DocBook
-very fast
-web and command-line interface
-fully customizable output with Smarty-based templates
-recognizes JavaDoc-style documentation with special tags customized for PHP 4
-automatic linking, class inheritance diagrams and intelligent override
-customizable source code highlighting, with phpxref-style cross-referencing
-parses standard README/CHANGELOG/INSTALL/FAQ files and includes them
 directly in documentation
-generates a todo list from @todo tags in source
-generates multiple documentation sets based on @access private, @internal and
 {@internal} tags
-example php files can be placed directly in documentation with highlighting
 and phpxref linking using the @example tag
-linking between external manual and API documentation is possible at the
 sub-section level in all output formats
-easily extended for specific documentation needs with Converter
-full documentation of every feature, manual can be generated directly from
 the source code with &quot;phpdoc -c makedocs&quot; in any format desired.
-current manual always available at http://www.phpdoc.org/manual.php
-user .ini files can be used to control output, multiple outputs can be
 generated at once

**WARNING**:
To use the web interface, you must set PEAR\'s data_dir to a subdirectory of
document root.

If browsing to http://localhost/index.php displays /path/to/htdocs/index.php,
set data_dir to a subdirectory of /path/to/htdocs:

$ pear config-set data_dir /path/to/htdocs/pear
$ pear install PhpDocumentor

http://localhost/pear/PhpDocumentor is the web interface</d>
 <r xlink:href="/rest/r/phpdocumentor"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpdocumentor/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PhpDocumentor</p>
 <c>pear.php.net</c>
 <r><v>1.3.0RC3</v><s>beta</s></r>
 <r><v>1.3.0RC2</v><s>beta</s></r>
 <r><v>1.3.0RC1</v><s>beta</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2.1</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0a</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.2.0beta3</v><s>beta</s></r>
 <r><v>1.2.0beta2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpdocumentor/deps.1.3.0RC3.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:8:"optional";s:2:"no";s:4:"name";s:11:"Archive_Tar";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:8:"optional";s:3:"yes";s:4:"name";s:14:"XML_Beautifier";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/phpunit/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHPUnit</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Testing">Testing</ca>
 <l>PHP License</l>
 <s>Regression testing framework for unit tests.</s>
 <d>PHPUnit is a regression testing framework used by the developer who implements unit tests in PHP. This is the version to be used with PHP 4.</d>
 <r xlink:href="/rest/r/phpunit"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpunit/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHPUnit</p>
 <c>pear.php.net</c>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.2.0beta1</v><s>beta</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.6.2</v><s>stable</s></r>
 <r><v>0.6.1</v><s>stable</s></r>
 <r><v>0.6</v><s>stable</s></r>
 <r><v>0.5</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
 <r><v>0.2</v><s>stable</s></r>
 <r><v>0.1</v><s></s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpunit/deps.1.3.1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";}i:2;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:3:"yes";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:10:"PHP_Compat";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/phpunit2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHPUnit2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Testing">Testing</ca>
 <l>PHP License</l>
 <s>Regression testing framework for unit tests.</s>
 <d>PHPUnit is a regression testing framework used by the developer who implements unit tests in PHP. This is the version to be used with PHP 5.</d>
 <r xlink:href="/rest/r/phpunit2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpunit2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHPUnit2</p>
 <c>pear.php.net</c>
 <r><v>2.3.0beta5</v><s>beta</s></r>
 <r><v>2.3.0beta4</v><s>beta</s></r>
 <r><v>2.3.0beta3</v><s>beta</s></r>
 <r><v>2.3.0beta2</v><s>beta</s></r>
 <r><v>2.3.0beta1</v><s>beta</s></r>
 <r><v>2.2.1</v><s>stable</s></r>
 <r><v>2.2.0</v><s>stable</s></r>
 <r><v>2.2.0beta7</v><s>beta</s></r>
 <r><v>2.2.0beta6</v><s>beta</s></r>
 <r><v>2.1.6</v><s>stable</s></r>
 <r><v>2.2.0beta5</v><s>beta</s></r>
 <r><v>2.1.5</v><s>stable</s></r>
 <r><v>2.2.0beta4</v><s>beta</s></r>
 <r><v>2.2.0beta3</v><s>beta</s></r>
 <r><v>2.2.0beta2</v><s>beta</s></r>
 <r><v>2.2.0beta1</v><s>beta</s></r>
 <r><v>2.1.4</v><s>stable</s></r>
 <r><v>2.1.3</v><s>stable</s></r>
 <r><v>2.1.2</v><s>stable</s></r>
 <r><v>2.1.1</v><s>stable</s></r>
 <r><v>2.1.0</v><s>stable</s></r>
 <r><v>2.0.3</v><s>stable</s></r>
 <r><v>2.0.2</v><s>stable</s></r>
 <r><v>2.0.1</v><s>stable</s></r>
 <r><v>2.0.0</v><s>stable</s></r>
 <r><v>2.0.0beta2</v><s>beta</s></r>
 <r><v>2.0.0beta1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/phpunit2/deps.2.3.0beta5.txt", 'a:7:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:7:"5.1.0b1";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"dom";}i:3;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}i:4;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"spl";}i:5;a:5:{s:4:"type";s:3:"ext";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta2";s:8:"optional";s:3:"yes";s:4:"name";s:6:"xdebug";}i:6;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:9:"Benchmark";}i:7;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"Log";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/php_archive/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHP_Archive</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>PHP License</l>
 <s>Create and Use PHP Archive files</s>
 <d>PHP_Archive allows you to create a single .phar file containing an entire application.</d>
 <r xlink:href="/rest/r/php_archive"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_archive/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHP_Archive</p>
 <c>pear.php.net</c>
 <r><v>0.6.1</v><s>alpha</s></r>
 <r><v>0.6.0</v><s>alpha</s></r>
 <r><v>0.5.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_archive/deps.0.6.1.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:7:"5.1.0b1";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.3.1";}i:1;a:3:{s:4:"name";s:4:"PEAR";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.3.5";}}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/php_beautifier/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHP_Beautifier</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>PHP License</l>
 <s>Beautifier for Php</s>
 <d>This program reformat and beautify PHP 4 and PHP 5 source code files automatically. The program is Open Source and distributed under the terms of PHP Licence. It is written in PHP 5 and has a command line tool.</d>
 <r xlink:href="/rest/r/php_beautifier"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_beautifier/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHP_Beautifier</p>
 <c>pear.php.net</c>
 <r><v>0.1.7</v><s>beta</s></r>
 <r><v>0.1.6</v><s>beta</s></r>
 <r><v>0.1.5</v><s>beta</s></r>
 <r><v>0.1.4</v><s>beta</s></r>
 <r><v>0.1.3</v><s>beta</s></r>
 <r><v>0.1.2</v><s>beta</s></r>
 <r><v>0.1.1</v><s>beta</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
 <r><v>0.0.9</v><s>devel</s></r>
 <r><v>0.0.8</v><s>devel</s></r>
 <r><v>0.0.7</v><s>devel</s></r>
 <r><v>0.0.6.1</v><s>devel</s></r>
 <r><v>0.0.6</v><s>devel</s></r>
 <r><v>0.0.5</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_beautifier/deps.0.1.7.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:1:"5";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.8";s:8:"optional";s:2:"no";s:4:"name";s:3:"Log";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"gt";s:7:"version";s:1:"1";s:8:"optional";s:3:"yes";s:4:"name";s:14:"Console_Getopt";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:8:"optional";s:3:"yes";s:4:"name";s:11:"Archive_Tar";}i:5;a:5:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:7:"version";s:1:"0";s:8:"optional";s:2:"no";s:4:"name";s:9:"tokenizer";}i:6;a:5:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:7:"version";s:1:"0";s:8:"optional";s:3:"yes";s:4:"name";s:3:"bz2";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/php_compat/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHP_Compat</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>PHP License</l>
 <s>Provides missing functionality for older versions of PHP</s>
 <d>PHP_Compat provides missing functionality in the form of constants and functions for older versions of PHP.

Note: PHP_Compat can be used without installing PEAR.
1) Download the package by clicking the &quot;Download&quot; link.
2) Find the file you need. Each function is in its own file, e.g. array_walk_recursive.php.
3) Place this file somewhere in your include_path.
4) Include it, e.g. &lt;?php require_once \'array_walk_recursive.php\';?&gt;
The function is now ready to be used.</d>
 <r xlink:href="/rest/r/php_compat"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_compat/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHP_Compat</p>
 <c>pear.php.net</c>
 <r><v>1.4.1</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0RC2</v><s>beta</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_compat/deps.1.4.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/php_compatinfo/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHP_CompatInfo</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>PHP License</l>
 <s>Find out the minimum version and the extensions required for a piece of code to run</s>
 <d>PHP_CompatInfo will parse a file/folder/script/array to find out the minimum
version and extensions required for it to run. Features advanced debug output
which shows which functions require which version and CLI output script</d>
 <r xlink:href="/rest/r/php_compatinfo"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_compatinfo/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHP_CompatInfo</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0RC5</v><s>beta</s></r>
 <r><v>1.0.0RC4</v><s>beta</s></r>
 <r><v>1.0.0RC3</v><s>beta</s></r>
 <r><v>1.0.0RC2</v><s>beta</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.8.4</v><s>alpha</s></r>
 <r><v>0.8.3</v><s>alpha</s></r>
 <r><v>0.8.2</v><s>alpha</s></r>
 <r><v>0.8.1</v><s>alpha</s></r>
 <r><v>0.8.0</v><s>alpha</s></r>
 <r><v>0.7.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_compatinfo/deps.1.0.0.txt", 'a:2:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.3.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:9:"extension";a:1:{s:4:"name";s:9:"tokenizer";}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:13:"Console_Table";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.0.1";}i:1;a:3:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";}}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/php_fork/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHP_Fork</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>PHP License</l>
 <s>PHP_Fork class. Wrapper around the pcntl_fork() stuff with a API set like Java language</s>
 <d>PHP_Fork class. Wrapper around the pcntl_fork() stuff
with a API set like Java language.
Practical usage is done by extending this class, and re-defining
the run() method.
[see basic example]
This way PHP developers can enclose logic into a class that extends
PHP_Fork, then execute the start() method that forks a child process.
Communications with the forked process is ensured by using a Shared Memory
Segment; by using a user-defined signal and this shared memory developers
can access to child process methods that returns a serializable variable.
The shared variable space can be accessed with the tho methods:
o void setVariable($name, $value)
o mixed getVariable($name)
$name must be a valid PHP variable name;
$value must be a variable or a serializable object.
Resources (db connections, streams, etc.) cannot be serialized and so they\'re not correctly handled.
Requires PHP build with --enable-cli --with-pcntl --enable-shmop.
Only runs on *NIX systems, because Windows lacks of the pcntl ext.
@example browser_pool.php an interactive tool to perform multiple cuncurrent request over an URL.
@example simple_controller.php shows how to attach a controller to started pseudo-threads.
@example exec_methods.php shows a workaround to execute methods into the child process.
@example passing_vars.php shows variable exchange between the parent process and started pseudo-threads.
@example basic.php a basic example, only two pseudo-threads that increment a counter simultaneously.</d>
 <r xlink:href="/rest/r/php_fork"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_fork/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHP_Fork</p>
 <c>pear.php.net</c>
 <r><v>0.3.0</v><s>beta</s></r>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_fork/deps.0.3.0.txt", 'a:4:{i:1;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:5:"pcntl";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:5:"shmop";}i:3;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:5:"posix";}i:4;a:3:{s:4:"type";s:4:"sapi";s:3:"rel";s:3:"has";s:4:"name";s:3:"cli";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/php_parser/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHP_Parser</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>PHP License</l>
 <s>A PHP Grammar Parser</s>
 <d>PHP_Parser is a source code analysis tool based around a real Parser
generated by phpJay.  The parser uses the same EBNF source that PHP
uses to parse itself, and it therefore as robust as PHP itself.
This version has full support for parsing out every re-usable element
in PHP 5 as of beta 1:
- classes
 - abstract classes
 - inheritance, implements
 - interfaces
 - methods
  - exception parsing directly from source
  - static variables declared
  - global and superglobal ($_GET) variables used
    and declared
 - variables
 - constants
- functions (same information as methods)
- defines
- global variables (with help of the Tokenizer Lexer)
- superglobal variables used in global code
- include statements

The output can be customized to return an array, return
objects of user-specified classes, and can also be
customized to publish each element as it is parsed, allowing
hooks into parsing to catch information.</d>
 <r xlink:href="/rest/r/php_parser"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_parser/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHP_Parser</p>
 <c>pear.php.net</c>
 <r><v>0.1</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_parser/deps.0.1.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/rdf/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>RDF</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Semantic+Web">Semantic Web</ca>
 <l>LGPL</l>
 <s>Port of the core RAP API</s>
 <d>This package is a port of the core components of the RDF API for PHP (aka RAP):
http://www.wiwiss.fu-berlin.de/suhl/bizer/rdfapi/.</d>
 <r xlink:href="/rest/r/rdf"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>RDF</p>
 <c>pear.php.net</c>
 <r><v>0.1.0alpha1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf/deps.0.1.0alpha1.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/rdf_n3/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>RDF_N3</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Semantic+Web">Semantic Web</ca>
 <l>LGPL</l>
 <s>Port of the RAP N3 parser/serializer</s>
 <d>This package is a port of the N3 parser and serializer of the RDF API for PHP (aka RAP):
http://www.wiwiss.fu-berlin.de/suhl/bizer/rdfapi/.</d>
 <r xlink:href="/rest/r/rdf_n3"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf_n3/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>RDF_N3</p>
 <c>pear.php.net</c>
 <r><v>0.1.0alpha1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf_n3/deps.0.1.0alpha1.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:3:"RDF";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/rdf_ntriple/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>RDF_NTriple</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Semantic+Web">Semantic Web</ca>
 <l>LGPL</l>
 <s>Port of the RAP NTriple serializer</s>
 <d>This package is a port of the NTriple serializer of the RDF API for PHP (aka RAP):
http://www.wiwiss.fu-berlin.de/suhl/bizer/rdfapi/.</d>
 <r xlink:href="/rest/r/rdf_ntriple"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf_ntriple/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>RDF_NTriple</p>
 <c>pear.php.net</c>
 <r><v>0.1.0alpha1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf_ntriple/deps.0.1.0alpha1.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:3:"RDF";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/rdf_rdql/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>RDF_RDQL</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Semantic+Web">Semantic Web</ca>
 <l>LGPL</l>
 <s>Port of the RAP RDQL API</s>
 <d>This package is a port of the RDQL part of the RDF API for PHP (aka RAP):
http://www.wiwiss.fu-berlin.de/suhl/bizer/rdfapi/.</d>
 <r xlink:href="/rest/r/rdf_rdql"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf_rdql/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>RDF_RDQL</p>
 <c>pear.php.net</c>
 <r><v>0.1.0alpha1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/rdf_rdql/deps.0.1.0alpha1.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:3:"RDF";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/science_chemistry/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Science_Chemistry</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Science">Science</ca>
 <l>PHP License</l>
 <s>Classes to manipulate chemical objects: atoms, molecules, etc.</s>
 <d>General classes to represent Atoms, Molecules and Macromolecules.  Also
parsing code for PDB, CML and XYZ file formats.  Examples of parsing and
conversion to/from chemical structure formats. Includes a utility class with
information on the Elements in the Periodic Table.</d>
 <r xlink:href="/rest/r/science_chemistry"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/science_chemistry/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Science_Chemistry</p>
 <c>pear.php.net</c>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/science_chemistry/deps.1.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/scriptreorganizer/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>ScriptReorganizer</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Tools+and+Utilities">Tools and Utilities</ca>
 <l>LGPL</l>
 <s>Library/Tool focusing exclusively on the file size aspect of PHP script optimization.</s>
 <d>ScriptReorganizer has the ability to reorganize source code in different (incremental) ways:
- File: one-to-one (Script) or many-to-one (Library) optimization.
- Source: EOL (Route), comment (Quiet) and whitespace (Pack) optimization.

Plugin functionality is available by means of the Decorator Pattern.

It is highly recommended to follow the best practice detailed out below, when using this package:
1. Running of all tests before building releases to deploy.
2. Reorganization of the source code file(s) with ScriptReorganizer.
3. Running of all tests - not only unit tests!
4. Final building of the release to deploy.

If the advanced pack mode strategy is used for packaging, a non-ScriptReorganized source code tree should be shipped together with the optimized one, to enable third parties to track down undiscovered bugs.

Same applies for (complex) applications that are pharized, i.e. optimized and packaged with PHP_Archive, as well as for bcompiled scripts.

ANN: Currently only &quot;pure&quot; PHP code can be reorganized.</d>
 <r xlink:href="/rest/r/scriptreorganizer"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/scriptreorganizer/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/search_mnogosearch/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Search_Mnogosearch</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Tools+and+Utilities">Tools and Utilities</ca>
 <l>PHP License 2.02</l>
 <s>Wrapper classes for the mnoGoSearch extension</s>
 <d>This package provides wrapper classes for the mnoGoSearch search engine. The package has two central classes &quot;Search_Mnogosearch&quot; and &quot;Search_Mnogosearch_Result&quot;. The class &quot;Search_Mnogosearch&quot; gives an object that represents the search and the &quot;Search_Mnogosearch_Result&quot; the result. The usage is just like the usage in the &quot;DB&quot; and &quot;DB_result&quot; classes.</d>
 <r xlink:href="/rest/r/search_mnogosearch"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/search_mnogosearch/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Search_Mnogosearch</p>
 <c>pear.php.net</c>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/search_mnogosearch/deps.0.1.1.txt", 'a:5:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:11:"mnogosearch";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"3.2.3";s:8:"optional";s:3:"yes";s:4:"name";s:14:"HTML_QuickForm";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.1";s:8:"optional";s:3:"yes";s:4:"name";s:19:"HTML_Template_Sigma";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"2.2.3";s:8:"optional";s:3:"yes";s:4:"name";s:5:"Pager";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_amazon/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Amazon</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>BSD</l>
 <s>Provides access to Amazon.com\'s retail and associate web services</s>
 <d>Services_Amazon uses Amazon.com?s web services to allow developers to search and provide associate links for specific ISBN numbers, authors, artist, directors, and publishers among other things.</d>
 <r xlink:href="/rest/r/services_amazon"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_amazon/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Amazon</p>
 <c>pear.php.net</c>
 <r><v>0.2.0</v><s>beta</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_amazon/deps.0.2.0.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:11:"HTTP_Client";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:14:"XML_Serializer";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_delicious/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Delicious</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>Client for the del.icio.us web service.</s>
 <d>Services_Delicious is a client for the REST-based web service of del.icio.us.
del.icio.us is a social bookmarks manager. It allows you to easily add sites you like to your personal collection of links, to categorize those sites with keywords, and to share your collection not only between your own browsers and machines, but also with others.
Services_Delicious allows you to select, add and delete your bookmarks from any PHP script.</d>
 <r xlink:href="/rest/r/services_delicious"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_delicious/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Delicious</p>
 <c>pear.php.net</c>
 <r><v>0.2.0beta</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_delicious/deps.0.2.0beta.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:12:"HTTP_Request";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:6:"0.12.0";s:8:"optional";s:2:"no";s:4:"name";s:14:"XML_Serializer";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_dyndns/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_DynDNS</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>Provides access to the DynDNS web service</s>
 <d>Services_DynDNS provides object-oriented interfaces to the DynDNS REST API.</d>
 <r xlink:href="/rest/r/services_dyndns"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_dyndns/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_DynDNS</p>
 <c>pear.php.net</c>
 <r><v>0.3.1</v><s>alpha</s></r>
 <r><v>0.3.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_dyndns/deps.0.3.1.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.1.0";s:8:"optional";s:2:"no";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:12:"HTTP_Request";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_ebay/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Ebay</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>Interface to eBay\'s XML-API.</s>
 <d>Interface to eBay\'s XML-API. It provides objects that are able to communicate with eBay as well as models that help you working with the return values like User or Item models.
The Services_Ebay class provides a unified method to use all objects.</d>
 <r xlink:href="/rest/r/services_ebay"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_ebay/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Ebay</p>
 <c>pear.php.net</c>
 <r><v>0.12.0</v><s>alpha</s></r>
 <r><v>0.11.0</v><s>alpha</s></r>
 <r><v>0.10.1alpha</v><s>alpha</s></r>
 <r><v>0.10.0alpha</v><s>alpha</s></r>
 <r><v>0.8.0alpha</v><s>alpha</s></r>
 <r><v>0.7.0</v><s>devel</s></r>
 <r><v>0.6.1</v><s>devel</s></r>
 <r><v>0.6.0</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_ebay/deps.0.12.0.txt", 'a:4:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.2";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:6:"0.16.0";s:8:"optional";s:2:"no";s:4:"name";s:14:"XML_Serializer";}i:3;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";s:8:"optional";s:2:"no";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"curl";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_exchangerates/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_ExchangeRates</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>Performs currency conversion</s>
 <d>Extendable to work with any source that provides exchange rate data, this class downloads
exchange rates and the name of each currency (US Dollar, Euro, Maltese Lira, etc.) and
converts between any two of the available currencies (the actual number of currencies
supported depends on the exchange rate feed used).</d>
 <r xlink:href="/rest/r/services_exchangerates"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_exchangerates/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_ExchangeRates</p>
 <c>pear.php.net</c>
 <r><v>0.5.2</v><s>beta</s></r>
 <r><v>0.5.0</v><s>beta</s></r>
 <r><v>0.4.1</v><s>alpha</s></r>
 <r><v>0.4</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_exchangerates/deps.0.5.2.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:12:"HTTP_Request";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:10:"Cache_Lite";}i:3;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:8:"XML_Tree";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_google/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Google</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>Provides access to the Google Web APIs</s>
 <d>Allows easy access to the Google Web APIs for the search engine, spelling suggestions, and cache.
                To use the package you\'ll need an API key from http://www.google.com/apis/.</d>
 <r xlink:href="/rest/r/services_google"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_google/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Google</p>
 <c>pear.php.net</c>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_google/deps.0.1.1.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";s:8:"optional";s:2:"no";}i:2;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"soap";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_pingback/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Pingback</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>BSD License</l>
 <s>A Pingback User-Agent class.</s>
 <d>A Pingback package implemented in PHP, able to send and receive a pingback.</d>
 <r xlink:href="/rest/r/services_pingback"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_pingback/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Pingback</p>
 <c>pear.php.net</c>
 <r><v>0.2.0dev2</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_pingback/deps.0.2.0dev2.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"4.3.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:3:{i:0;a:3:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.0.3RC1";}i:1;a:3:{s:4:"name";s:7:"Net_URL";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:6:"1.0.14";}i:2;a:3:{s:4:"name";s:12:"HTTP_Request";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.2.4";}}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_technorati/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Technorati</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>A class for interacting with the Technorati API</s>
 <d>Services_Technorati is a wrapper for the REST-based Technorati webservices API. Technorati is a blog search engine that provides a number of interfaces for interacting with recent blog entries, such as searching for entries that link to a certain URL, are linked from a certain URL, or have been given certain tags.

Services_Technorati provides an interface to all of the query types in Technorati API version 1.0, and supports filesystem caching of query data using Cache_Lite compatible cache objects.</d>
 <r xlink:href="/rest/r/services_technorati"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_technorati/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Technorati</p>
 <c>pear.php.net</c>
 <r><v>0.6.5beta</v><s>beta</s></r>
 <r><v>0.6.4beta</v><s>beta</s></r>
 <r><v>0.6.3alpha</v><s>alpha</s></r>
 <r><v>0.6.2alpha</v><s>alpha</s></r>
 <r><v>0.6.1alpha</v><s>alpha</s></r>
 <r><v>0.6.0alpha</v><s>alpha</s></r>
 <r><v>0.5.6</v><s>devel</s></r>
 <r><v>0.5.5</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_technorati/deps.0.6.5beta.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:10:"Cache_Lite";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:14:"XML_Serializer";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:12:"HTTP_Request";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_trackback/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Trackback</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>Trackback - A generic class for sending and receiving trackbacks.</s>
 <d>A generic class for sending and receiving trackbacks.</d>
 <r xlink:href="/rest/r/services_trackback"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_trackback/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Trackback</p>
 <c>pear.php.net</c>
 <r><v>0.5.0</v><s>alpha</s></r>
 <r><v>0.4.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_trackback/deps.0.5.0.txt", 'a:2:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.3.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:5:"1.3.0";}}s:5:"group";a:2:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:43:"Usage of Services_Trackback::autodiscover()";s:4:"name";s:12:"autodiscover";}s:7:"package";a:2:{s:4:"name";s:12:"HTTP_Request";s:7:"channel";s:12:"pear.php.net";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:23:"DNSBL/SURBL spam checks";s:4:"name";s:5:"dnsbl";}s:7:"package";a:2:{s:4:"name";s:9:"Net_DNSBL";s:7:"channel";s:12:"pear.php.net";}}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_weather/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Weather</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>This class acts as an interface to various online weather-services.</s>
 <d>Services_Weather searches for given locations and retrieves current
weather data and, dependent on the used service, also forecasts. Up to
now, GlobalWeather from CapeScience, Weather XML from EJSE (US only),
a XOAP service from Weather.com and METAR/TAF from NOAA are supported.
Further services will get included, if they become available, have a
usable API and are properly documented.</d>
 <r xlink:href="/rest/r/services_weather"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_weather/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Weather</p>
 <c>pear.php.net</c>
 <r><v>1.3.2</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0RC2</v><s>beta</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_weather/deps.1.3.2.txt", 'a:5:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.5.3";s:8:"optional";s:3:"yes";s:4:"name";s:5:"Cache";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.4";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:8:"optional";s:3:"yes";s:4:"name";s:12:"HTTP_Request";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.7.5";s:8:"optional";s:3:"yes";s:4:"name";s:4:"SOAP";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.8";s:8:"optional";s:3:"yes";s:4:"name";s:14:"XML_Serializer";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_webservice/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Webservice</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>Create webservices</s>
 <d>Easy Webservice creation</d>
 <r xlink:href="/rest/r/services_webservice"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_webservice/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Webservice</p>
 <c>pear.php.net</c>
 <r><v>0.4.0</v><s>alpha</s></r>
 <r><v>0.3.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_webservice/deps.0.4.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/services_yahoo/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Services_Yahoo</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>Provides access to the Yahoo! Web Services</s>
 <d>Services_Yahoo provides object-oriented interfaces to the web service capabilities of Yahoo.</d>
 <r xlink:href="/rest/r/services_yahoo"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_yahoo/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Services_Yahoo</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/services_yahoo/deps.0.1.0.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:12:"HTTP_Request";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.3";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"5.0.0";s:8:"optional";s:2:"no";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:9:"simplexml";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/soap/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>SOAP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>SOAP Client/Server for PHP</s>
 <d>Implementation of SOAP protocol and services</d>
 <r xlink:href="/rest/r/soap"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/soap/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>SOAP</p>
 <c>pear.php.net</c>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
 <r><v>0.8.1</v><s>beta</s></r>
 <r><v>0.8RC3</v><s>beta</s></r>
 <r><v>0.8RC2</v><s>beta</s></r>
 <r><v>0.8RC1</v><s>beta</s></r>
 <r><v>0.7.5</v><s>beta</s></r>
 <r><v>0.7.4</v><s>beta</s></r>
 <r><v>0.7.3</v><s>beta</s></r>
 <r><v>0.7.2</v><s>beta</s></r>
 <r><v>0.7.1</v><s>beta</s></r>
 <r><v>0.7.0</v><s>beta</s></r>
 <r><v>0.6.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/soap/deps.0.9.1.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/soap_interop/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>SOAP_Interop</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>SOAP Interop Test Application</s>
 <d>Test harness for SOAP Builders tests.
Supports Round 2 and Round 3 tests.</d>
 <r xlink:href="/rest/r/soap_interop"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/soap_interop/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>SOAP_Interop</p>
 <c>pear.php.net</c>
 <r><v>0.8</v><s>beta</s></r>
 <r><v>0.7.2</v><s>beta</s></r>
 <r><v>0.7.1</v><s>beta</s></r>
 <r><v>0.7.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/soap_interop/deps.0.8.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:4:"SOAP";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/spreadsheet_excel_writer/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Spreadsheet_Excel_Writer</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>LGPL</l>
 <s>Package for generating Excel spreadsheets</s>
 <d>Spreadsheet_Excel_Writer was born as a porting of the Spreadsheet::WriteExcel Perl module to PHP.
It allows writing of Excel spreadsheets without the need for COM objects.
It supports formulas, images (BMP) and all kinds of formatting for text and cells.
It currently supports the BIFF5 format (Excel 5.0), so functionality appeared in the latest Excel versions is not yet available.</d>
 <r xlink:href="/rest/r/spreadsheet_excel_writer"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/spreadsheet_excel_writer/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Spreadsheet_Excel_Writer</p>
 <c>pear.php.net</c>
 <r><v>0.8</v><s>beta</s></r>
 <r><v>0.7</v><s>beta</s></r>
 <r><v>0.6</v><s>beta</s></r>
 <r><v>0.5</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/spreadsheet_excel_writer/deps.0.8.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.5";s:4:"name";s:3:"OLE";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/sql_parser/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>SQL_Parser</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>LGPL</l>
 <s>An SQL parser</s>
 <d>This class is primarily an SQL parser, written with influences from a variety of sources (mSQL, CPAN\'s SQL-Statement, mySQL). It also includes a tokenizer (lexer) class and a reimplementation of the ctype extension in PHP.</d>
 <r xlink:href="/rest/r/sql_parser"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/sql_parser/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>SQL_Parser</p>
 <c>pear.php.net</c>
 <r><v>0.5</v><s>devel</s></r>
 <r><v>0.4</v><s>devel</s></r>
 <r><v>0.3</v><s>devel</s></r>
 <r><v>0.2</v><s>devel</s></r>
 <r><v>0.1</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/sql_parser/deps.0.5.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/stream_shm/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Stream_SHM</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Streams">Streams</ca>
 <l>PHP</l>
 <s>Shared Memory Stream</s>
 <d>The Stream_SHM package provides a class that can be registered with stream_register_wrapper() in order to have stream-based shared-memory access.</d>
 <r xlink:href="/rest/r/stream_shm"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/stream_shm/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Stream_SHM</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/stream_shm/deps.1.0.0.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/stream_var/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Stream_Var</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Streams">Streams</ca>
 <l>PHP License</l>
 <s>Allows stream based access to any variable.</s>
 <d>Stream_Var can be registered as a stream with stream_register_wrapper() and allows stream based acces to variables in any scope. Arrays are treated as directories, so it\'s possible to replace temporary directories and files in your application with variables.</d>
 <r xlink:href="/rest/r/stream_var"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/stream_var/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Stream_Var</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.2.1</v><s>stable</s></r>
 <r><v>0.2</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/stream_var/deps.1.0.0.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.2";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/structures_datagrid/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Structures_DataGrid</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Structures">Structures</ca>
 <l>PHP License</l>
 <s>A tabular structure that contains a record set of data for paging and sorting purposes.</s>
 <d>This package offers a toolkit to render out a datagrid in HTML format as well as
many other formats such as an XML Document, an Excel Spreadsheet, an XUL Document and more.
It also offers paging and sorting functionallity to limit the data that is presented and processed.
This concept is based on the .NET Framework DataGrid control and works very well with database and XML result sets.</d>
 <r xlink:href="/rest/r/structures_datagrid"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Structures_DataGrid</p>
 <c>pear.php.net</c>
 <r><v>0.6.2</v><s>beta</s></r>
 <r><v>0.6.0</v><s>beta</s></r>
 <r><v>0.5.3</v><s>alpha</s></r>
 <r><v>0.5.2</v><s>alpha</s></r>
 <r><v>0.5.1</v><s>alpha</s></r>
 <r><v>0.5</v><s>alpha</s></r>
 <r><v>0.4.1</v><s>alpha</s></r>
 <r><v>0.4</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_datagrid/deps.0.6.2.txt", 'a:8:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.5";s:8:"optional";s:3:"yes";s:4:"name";s:10:"HTML_Table";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"2.2";s:8:"optional";s:3:"yes";s:4:"name";s:5:"Pager";}i:4;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.6";s:8:"optional";s:3:"yes";s:4:"name";s:24:"Spreadsheet_Excel_Writer";}i:5;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.5.2";s:8:"optional";s:3:"yes";s:4:"name";s:8:"XML_Util";}i:6;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.9.2";s:8:"optional";s:3:"yes";s:4:"name";s:7:"XML_RSS";}i:7;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:6:"0.11.1";s:8:"optional";s:3:"yes";s:4:"name";s:14:"XML_Serializer";}i:8;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.1";s:8:"optional";s:3:"yes";s:4:"name";s:13:"Console_Table";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/structures_graph/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Structures_Graph</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Structures">Structures</ca>
 <l>LGPL</l>
 <s>Graph datastructure manipulation library</s>
 <d>Structures_Graph is a package for creating and manipulating graph datastructures. It allows building of directed
and undirected graphs, with data and metadata stored in nodes. The library provides functions for graph traversing
as well as for characteristic extraction from the graph topology.</d>
 <r xlink:href="/rest/r/structures_graph"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_graph/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Structures_Graph</p>
 <c>pear.php.net</c>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/structures_graph/deps.1.0.1.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:4:"Pear";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/system_command/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>System_Command</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Console">Console</ca>
 <l>PHP License</l>
 <s>PEAR::System_Command is a commandline execution interface.</s>
 <d>System_Command is a commandline execution interface.
Running functions from the commandline can be risky if the proper precautions are
not taken to escape the shell arguments and reaping the exit status properly.  This class
provides a formal interface to both, so that you can run a system command as comfortably as
you would run a php function, with full pear error handling as results on failure.
It is important to note that this class, unlike other implementations, distinguishes between
output to stderr and output to stdout.  It also reports the exit status of the command.
So in every sense of the word, it gives php shell capabilities.</d>
 <r xlink:href="/rest/r/system_command"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_command/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>System_Command</p>
 <c>pear.php.net</c>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_command/deps.1.0.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/system_mount/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>System_Mount</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/System">System</ca>
 <l>PHP License v3.0</l>
 <s>Mount and unmount devices in fstab</s>
 <d>System_Mount provides a simple interface to deal with mounting and unmounting devices listed in the system\'s fstab.

Features:
* Very compact, easy-to-read code, based on File_Fstab.
* Examines mount options to determine if a device can be mounted or not.
* Extremely easy to use.
* Fully documented with PHPDoc.</d>
 <r xlink:href="/rest/r/system_mount"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_mount/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>System_Mount</p>
 <c>pear.php.net</c>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0beta2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_mount/deps.1.0.0.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:10:"2.0.0beta1";s:4:"name";s:10:"File_Fstab";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:14:"System_Command";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:4:"File";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/system_procwatch/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>System_ProcWatch</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/System">System</ca>
 <l>PHP</l>
 <s>Monitor Processes</s>
 <d>With this package you can monitor running processes based upon an XML configuration file, XML string, INI file or an array where you define patterns, conditions and actions.

XML::Parser must be installed to configure System::ProcWatch by XML, additionally Console::Getopt and XML::DTD must be installed if you want to use the shipped shell scripts \'procwatch\' and \'procwatch-lint\'.

A simple \'ps\' fake for WinNT can be found at http://dev.iworks.at/ps/ps.zip</d>
 <r xlink:href="/rest/r/system_procwatch"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_procwatch/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>System_ProcWatch</p>
 <c>pear.php.net</c>
 <r><v>0.4.2</v><s>beta</s></r>
 <r><v>0.4.1</v><s>beta</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3.1</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_procwatch/deps.0.4.2.txt", 'a:7:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.1.0";s:8:"optional";s:3:"yes";s:4:"name";s:10:"XML_Parser";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:7:"XML_DTD";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:14:"Console_Getopt";}i:5;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"pcre";}i:6;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.0.2";s:8:"optional";s:2:"no";}i:7;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:3:"yes";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/system_sharedmemory/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>System_SharedMemory</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/System">System</ca>
 <l>PHP License</l>
 <s>common OO-style shared memory API</s>
 <d>OO-style shared memory API for next shared memory engines:
Apache Note
APC
Eaccelerator
Plain files
Memcached
Turck MMCache
Sharedance
Shmop
SQLite
System V</d>
 <r xlink:href="/rest/r/system_sharedmemory"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_sharedmemory/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>System_SharedMemory</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_sharedmemory/deps.0.1.0.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/system_socket/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>System_Socket</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/System">System</ca>
 <l>PHP</l>
 <s>OO socket API</s>
 <d>Aims to provide a thight and robust OO API to PHPs socket extension (ext/sockets).</d>
 <r xlink:href="/rest/r/system_socket"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_socket/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>System_Socket</p>
 <c>pear.php.net</c>
 <r><v>0.4.1</v><s>alpha</s></r>
 <r><v>0.4.0</v><s>devel</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_socket/deps.0.4.1.txt", 'a:5:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:8:"optional";s:3:"yes";s:4:"name";s:8:"Net_IPv4";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"Log";}i:4;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:7:"sockets";}i:5;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/system_windrives/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>System_WinDrives</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/System">System</ca>
 <l>PHP License</l>
 <s>List files drives on windows systems</s>
 <d>Provides functions to enumerate root directories
  (&quot;Drives&quot;) on Windows systems by using win32 api
  calls.</d>
 <r xlink:href="/rest/r/system_windrives"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_windrives/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>System_WinDrives</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/system_windrives/deps.0.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_captcha/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_CAPTCHA</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>PHP License</l>
 <s>Generation of CAPTCHA imgaes</s>
 <d>Implementation of CAPTCHA (completely automated public Turing test to tell computers and humans apart) images</d>
 <r xlink:href="/rest/r/text_captcha"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_captcha/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_CAPTCHA</p>
 <c>pear.php.net</c>
 <r><v>0.1.4</v><s>alpha</s></r>
 <r><v>0.1.3</v><s>alpha</s></r>
 <r><v>0.1.2</v><s>alpha</s></r>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_captcha/deps.0.1.4.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:13:"Text_Password";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:10:"Image_Text";}i:3;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:2:"gd";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_diff/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Diff</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>LGPL</l>
 <s>Engine for performing and rendering text diffs</s>
 <d>This package provides a text-based diff engine and renderers for multiple diff output formats.</d>
 <r xlink:href="/rest/r/text_diff"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_diff/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_Diff</p>
 <c>pear.php.net</c>
 <r><v>0.1.1</v><s>beta</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
 <r><v>0.0.5</v><s>beta</s></r>
 <r><v>0.0.4</v><s>beta</s></r>
 <r><v>0.0.3</v><s>beta</s></r>
 <r><v>0.0.2</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_diff/deps.0.1.1.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}i:2;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:5:"xdiff";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_figlet/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Figlet</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>PHP License</l>
 <s>Render text using FIGlet fonts</s>
 <d>Engine for use FIGlet fonts to rendering text</d>
 <r xlink:href="/rest/r/text_figlet"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_figlet/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_Figlet</p>
 <c>pear.php.net</c>
 <r><v>0.8.1</v><s>beta</s></r>
 <r><v>0.8.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_figlet/deps.0.8.1.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:6:"4.0.4+";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_highlighter/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Highlighter</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>PHP License</l>
 <s>Syntax highlighting</s>
 <d>Text_Highlighter is a package for syntax highlighting.

It provides a base class provining all the functionality,
and a descendant classes geneator class.

The main idea is to simplify creation of subclasses
implementing syntax highlighting for particular language.
Subclasses do not implement any new functioanality,
they just provide syntax highlighting rules.
The rules sources are in XML format.

To create a highlighter for a language, there is no need
to code a new class manually. Simply describe the rules
in XML file and use Text_Highlighter_Generator to create
a new class.</d>
 <r xlink:href="/rest/r/text_highlighter"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_highlighter/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_Highlighter</p>
 <c>pear.php.net</c>
 <r><v>0.6.2</v><s>beta</s></r>
 <r><v>0.6.1</v><s>beta</s></r>
 <r><v>0.6.0</v><s>beta</s></r>
 <r><v>0.5.1</v><s>beta</s></r>
 <r><v>0.5.0</v><s>beta</s></r>
 <r><v>0.4.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_highlighter/deps.0.6.6.txt", 'a:3:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.1";s:8:"optional";s:2:"no";s:4:"name";s:10:"XML_Parser";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:8:"optional";s:2:"no";s:4:"name";s:14:"Console_Getopt";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_huffman/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Huffman</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>LGPL</l>
 <s>Huffman compression</s>
 <d>Huffman compression is a lossless compression algorithm that is ideal for compressing textual data.</d>
 <r xlink:href="/rest/r/text_huffman"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_huffman/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_Huffman</p>
 <c>pear.php.net</c>
 <r><v>0.2.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_huffman/deps.0.2.0.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:8:"5.0.0RC1";s:8:"optional";s:2:"no";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_lexer/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Lexer</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>PHP</l>
 <s>A base class for all types of Lexers and their implementation.</s>
 <d>Text_Lexer includes a base class for all types of Lexers and their implementation in PHP. Currently, only a regex lexer is implemented; in the future, FSM and String lexers will be added.</d>
 <r xlink:href="/rest/r/text_lexer"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_lexer/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_password/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Password</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>PHP License</l>
 <s>Creating passwords with PHP.</s>
 <d>Text_Password allows one to create pronounceable and unpronounceable
passwords. The full functional range is explained in the manual at
http://pear.php.net/manual/.</d>
 <r xlink:href="/rest/r/text_password"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_password/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_Password</p>
 <c>pear.php.net</c>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_password/deps.1.1.0.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_statistics/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Statistics</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Structures">Structures</ca>
 <l>PHP License</l>
 <s>Compute readability indexes for documents.</s>
 <d>Text_Statistics allows for computation of readability indexes for
text documents.</d>
 <r xlink:href="/rest/r/text_statistics"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_statistics/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_Statistics</p>
 <c>pear.php.net</c>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_statistics/deps.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_texhyphen/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_TeXHyphen</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>PHP</l>
 <s>Automated word hyphenation with the TeX algorithm.</s>
 <d>This package implements the TeX hyphenation algorithm based on pattern.

The package can support various backends for pattern retrieval. At this stage only flat files with TeX pattern were implemented. The advantage of the TeX pattern is the available multi-language support. Currently german, oxford and american english are supported.

For speed purposes an interface for a cache of hyphenated words was implemented.</d>
 <r xlink:href="/rest/r/text_texhyphen"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_texhyphen/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_TeXHyphen</p>
 <c>pear.php.net</c>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_texhyphen/deps.0.1.0.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_wiki/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Wiki</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>LGPL</l>
 <s>Abstracts parsing and rendering rules for any markup as Wiki or BBCode in structured plain text.</s>
 <d>The text transformation is done in 2 steps, the parsers use rules to tokenize the content, renderers output the tokens and left text in the requested format.
Used for versatile transformers as well as converters.</d>
 <r xlink:href="/rest/r/text_wiki"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_Wiki</p>
 <c>pear.php.net</c>
 <r><v>0.25.0</v><s>beta</s></r>
 <r><v>0.24.0</v><s>beta</s></r>
 <r><v>0.23.1</v><s>alpha</s></r>
 <r><v>0.23.0</v><s>alpha</s></r>
 <r><v>0.22.0</v><s>alpha</s></r>
 <r><v>0.21.0</v><s>alpha</s></r>
 <r><v>0.20.1</v><s>alpha</s></r>
 <r><v>0.19.7</v><s>alpha</s></r>
 <r><v>0.19.6</v><s>alpha</s></r>
 <r><v>0.19.5</v><s>alpha</s></r>
 <r><v>0.19.4</v><s>alpha</s></r>
 <r><v>0.19.3</v><s>alpha</s></r>
 <r><v>0.19.2</v><s>alpha</s></r>
 <r><v>0.19.1</v><s>alpha</s></r>
 <r><v>0.19</v><s>alpha</s></r>
 <r><v>0.17</v><s>alpha</s></r>
 <r><v>0.16</v><s>alpha</s></r>
 <r><v>0.15</v><s>alpha</s></r>
 <r><v>0.14</v><s>alpha</s></r>
 <r><v>0.12.1</v><s>alpha</s></r>
 <r><v>0.12</v><s>alpha</s></r>
 <r><v>0.11</v><s>alpha</s></r>
 <r><v>0.10.4</v><s>alpha</s></r>
 <r><v>0.10.3</v><s>alpha</s></r>
 <r><v>0.10.2</v><s>alpha</s></r>
 <r><v>0.10.1</v><s>alpha</s></r>
 <r><v>0.10</v><s>alpha</s></r>
 <r><v>0.8.3</v><s>alpha</s></r>
 <r><v>0.8.2</v><s>alpha</s></r>
 <r><v>0.8.1</v><s>alpha</s></r>
 <r><v>0.8</v><s>alpha</s></r>
 <r><v>0.7</v><s>alpha</s></r>
 <r><v>0.6</v><s>alpha</s></r>
 <r><v>0.5</v><s>alpha</s></r>
 <r><v>0.4</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki/deps.1.0.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_wiki_bbcode/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Wiki_BBCode</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>LGPL</l>
 <s>BBCode parser for Text_Wiki</s>
 <d>Parses BBCode mark-up to tokenize the text for Text_Wiki rendering (Xhtml, plain, Latex) or for conversions using the existing renderers (wiki).</d>
 <r xlink:href="/rest/r/text_wiki_bbcode"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_bbcode/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Text_Wiki_BBCode</p>
 <c>pear.php.net</c>
 <r><v>0.0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_bbcode/deps.0.0.1.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.1";s:4:"name";s:9:"Text_Wiki";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_wiki_cowiki/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Wiki_Cowiki</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>LGPL</l>
 <s>CoWiki parser and renderer for Text_Wiki</s>
 <d>A subpackage for Text_Wiki which allows parsing from and rendering to CoWiki syntax.</d>
 <r xlink:href="/rest/r/text_wiki_cowiki"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_cowiki/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_wiki_doku/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Wiki_Doku</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>LGPL</l>
 <s>DokuWiki parser and renderer for Text_Wiki</s>
 <d>A subpackage for Text_Wiki which allows parsing from and rendering to DokuWiki syntax.</d>
 <r xlink:href="/rest/r/text_wiki_doku"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_doku/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/text_wiki_tiki/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Text_Wiki_Tiki</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Text">Text</ca>
 <l>LGPL</l>
 <s>TikiWiki parser and renderer for Text_Wiki</s>
 <d>A subpackage for Text_Wiki which allows parsing from and rendering to TikiWiki syntax.</d>
 <r xlink:href="/rest/r/text_wiki_tiki"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki_tiki/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/translation/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Translation</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Internationalization">Internationalization</ca>
 <l>PHP License</l>
 <s>Class for creating multilingual websites.</s>
 <d>Class allows storing and retrieving all the strings on multilingual site in a database. The class connects to any database using PEAR::DB extension. The object should be created for every page. While creation all the strings connected with specific page and the strings connected with all the pages on the site are loaded into variable, so access to them is quite fast and does not overload database server connection.</d>
 <r xlink:href="/rest/r/translation"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/translation/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Translation</p>
 <c>pear.php.net</c>
 <r><v>1.2.6pl1</v><s>stable</s></r>
 <r><v>1.2.6</v><s>stable</s></r>
 <r><v>1.2.5</v><s>stable</s></r>
 <r><v>1.2.4</v><s>stable</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>beta</s></r>
 <r><v>1.2</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/translation/deps.1.2.6pl1.txt", 'a:1:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:2:"DB";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/translation2/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Translation2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Internationalization">Internationalization</ca>
 <l>PHP License</l>
 <s>Class for multilingual applications management.</s>
 <d>This class provides an easy way to retrieve all the strings for a multilingual site from a data source (i.e. db).
The following containers are provided, more will follow:
- PEAR::DB
- PEAR::MDB
- PEAR::MDB2
- gettext
- XML
- PEAR::DB_DataObject (experimental)
It is designed to reduce the number of queries to the db, caching the results when possible.
An Admin class is provided to easily manage translations (add/remove a language, add/remove a string).
Currently, the following decorators are provided:
- CacheLiteFunction (for file-based caching)
- CacheMemory (for memory-based caching)
- DefaultText (to replace empty strings with their keys)
- ErrorText (to replace empty strings with a custom error text)
- Iconv (to switch from/to different encodings)
- Lang (resort to fallback languages for empty strings)
- SpecialChars (replace html entities with their hex codes)
- UTF-8 (to convert UTF-8 strings to ISO-8859-1)</d>
 <r xlink:href="/rest/r/translation2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/translation2/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Translation2</p>
 <c>pear.php.net</c>
 <r><v>2.0.0beta7</v><s>beta</s></r>
 <r><v>2.0.0beta6</v><s>beta</s></r>
 <r><v>2.0.0beta5</v><s>beta</s></r>
 <r><v>2.0.0beta4</v><s>beta</s></r>
 <r><v>2.0.0beta3</v><s>beta</s></r>
 <r><v>2.0.0beta2</v><s>beta</s></r>
 <r><v>2.0.0beta1</v><s>beta</s></r>
 <r><v>2.0.0alpha2</v><s>alpha</s></r>
 <r><v>0.0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/translation2/deps.2.0.0beta7.txt", 'a:9:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:10:"Cache_Lite";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:13:"DB_DataObject";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:5;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:4:"MDB2";}i:6;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:7:"gettext";}i:7;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:12:"File_Gettext";}i:8;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.9.1";s:8:"optional";s:3:"yes";s:4:"name";s:6:"I18Nv2";}i:9;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:6:"0.13.0";s:8:"optional";s:3:"yes";s:4:"name";s:14:"XML_Serializer";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/tree/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Tree</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Structures">Structures</ca>
 <l>PHP License</l>
 <s>Generic tree management, currently supports DB and XML as data sources</s>
 <d>Provides methods to read and manipulate trees, which are stored in the DB
or an XML file. The trees can be stored in the DB either as nested trees.
Or as simple trees (\'brain dead method\'), which use parentId-like structure.
Currently XML data can only be read from a file and accessed.
The package offers a big number of methods to access and manipulate trees.
For example methods like: getRoot, getChild[ren[Ids]], getParent[s[Ids]], getPath[ById] and many
more.
There are two ways of retreiving the data from the place where they are stored,
one is by reading the entire tree into the memory - the Memory way. The other
is reading the tree nodes as needed (very useful in combination with huge trees
and the nested set model).
The package is designed that way that it is possible to convert/copy tree data
from either structure to another (from XML into DB).</d>
 <r xlink:href="/rest/r/tree"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/tree/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Tree</p>
 <c>pear.php.net</c>
 <r><v>0.2.4</v><s>beta</s></r>
 <r><v>0.2.3</v><s>beta</s></r>
 <r><v>0.2.2</v><s>beta</s></r>
 <r><v>0.2.1</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1.2</v><s>beta</s></r>
 <r><v>0.1.1</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/tree/deps.0.2.4.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.3";s:4:"name";s:2:"DB";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:10:"XML_Parser";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/uddi/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>UDDI</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>LGPL</l>
 <s>UDDI for PHP</s>
 <d>Implementation of the Universal Description, Discovery and Integration API for locating and publishing Web Services listings in a UBR (UDDI Business Registry)</d>
 <r xlink:href="/rest/r/uddi"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/uddi/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>UDDI</p>
 <c>pear.php.net</c>
 <r><v>0.2.0alpha4</v><s>alpha</s></r>
 <r><v>0.2.0alpha3</v><s>alpha</s></r>
 <r><v>0.2.0alpha2</v><s>alpha</s></r>
 <r><v>0.2.0alpha1</v><s>alpha</s></r>
 <r><v>0.1.3</v><s>alpha</s></r>
 <r><v>0.1.2</v><s>alpha</s></r>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/uddi/deps.0.2.0alpha4.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/validate/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Validate</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Validate">Validate</ca>
 <l>PHP</l>
 <s>Validation class</s>
 <d>Package to validate various datas. It includes :
 - numbers (min/max, decimal or not)
 - email (syntax, domain check)
 - string (predifined type alpha upper and/or lowercase, numeric,...)
 - date (min, max)
 - uri (RFC2396)
 - possibility valid multiple data with a single method call (::multiple)</d>
 <r xlink:href="/rest/r/validate"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Validate</p>
 <c>pear.php.net</c>
 <r><v>0.6.1</v><s>beta</s></r>
 <r><v>0.6.0</v><s>beta</s></r>
 <r><v>0.5.0</v><s>alpha</s></r>
 <r><v>0.4.1</v><s>alpha</s></r>
 <r><v>0.4.0</v><s>alpha</s></r>
 <r><v>0.3.0</v><s>alpha</s></r>
 <r><v>0.2.0</v><s>alpha</s></r>
 <r><v>0.1.2</v><s>alpha</s></r>
 <r><v>0.1.1</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
 <r><v>0.0.4</v><s>alpha</s></r>
 <r><v>0.0.3</v><s>alpha</s></r>
 <r><v>0.0.2</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate/deps.0.6.1.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:4:"Date";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/validate_at/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Validate_AT</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Validate">Validate</ca>
 <l>PHP</l>
 <s>Validation class for AT</s>
 <d>Package containes locale validation for AT such as:
 * SSN
 *  Postal Code</d>
 <r xlink:href="/rest/r/validate_at"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_at/allreleases.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Validate_AT</p>
 <c>pear.php.net</c>
 <r><v>0.5.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_at/deps.0.5.0.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.5.0";s:4:"name";s:8:"Validate";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/validate_au/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Validate_AU</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Validate">Validate</ca>
 <l>PHP License</l>
 <s>Validation Class for AU</s>
 <d>Packages contains Australia specific validators</d>
 <r xlink:href="/rest/r/validate_au"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/validate_au/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/validate_be/info.xml", '<?xml version="1.0" encoding="iso-8859-1" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Validate_BE</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Validate">Validate</ca>
 <l>PHP</l>
 <s>Validation class for Belgium</s>
 <d>Package containes locale validation for Belgium such as:
 * Postal Code
 * Bank Account Number
 * Transfer message (transfer from an bank account to another)
 * VAT
 * National ID
 * Identity Card Number
 * SIS CARD ID (belgian &quot;s&eacute;curit&eacute; sociale&quot; ID)</d>
 <r xlink:href="/rest/r/validate_be"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/1.3.1.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/archive_tar">Archive_Tar</p>
 <c>pear.php.net</c>
 <v>1.3.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>vblavet</m>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.
</d>
 <da>2005-03-17 16:09:16</da>
 <n>Correct Bug #3855
</n>
 <f>15102</f>
 <g>http://pear.php.net/get/Archive_Tar-1.3.1</g>
 <x xlink:href="package.1.3.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/file/1.2.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/file">File</p>
 <c>pear.php.net</c>
 <v>1.2.2</v>
 <st>stable</st>
 <l>PHP</l>
 <m>dufuz</m>
 <s>Common file and directory routines</s>
 <d>Provides easy access to read/write to files along with
some common routines to deal with paths. Also provides
interface for handling CSV files.
</d>
 <da>2005-09-10 08:20:34</da>
 <n>* Fixed bug #5071 install File throws XML error (helgi)
</n>
 <f>15796</f>
 <g>http://pear.php.net/get/File-1.2.2</g>
 <x xlink:href="package.1.2.2.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_sieve/1.1.1.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/net_sieve">Net_Sieve</p>
 <c>pear.php.net</c>
 <v>1.1.1</v>
 <st>stable</st>
 <l>BSD</l>
 <m>damian</m>
 <s>Handles talking to timsieved</s>
 <d>Provides an API to talk to the timsieved server that comes
with Cyrus IMAPd. Can be used to install, remove, mark active etc
sieve scripts.
</d>
 <da>2005-02-02 09:54:38</da>
 <n>* Fixed Bug #3242 cyrus murder referrals not followed
</n>
 <f>9750</f>
 <g>http://pear.php.net/get/Net_Sieve-1.1.1</g>
 <x xlink:href="package.1.1.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_socket/1.0.6.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/net_socket">Net_Socket</p>
 <c>pear.php.net</c>
 <v>1.0.6</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>chagenbu</m>
 <s>Network Socket Interface</s>
 <d>Net_Socket is a class interface to TCP sockets.  It provides blocking
and non-blocking operation, with different reading and writing modes
(byte-wise, block-wise, line-wise and special formats like network
byte-order ip addresses).
</d>
 <da>2005-02-26 09:53:48</da>
 <n>- Make package.xml safe for PEAR 1.4.0.
- Chunk socket writes on Windows by default, or if explicitly specified (Bug #980)
- Don\'t run any $addr with a \'/\' in it through gethostbyname() (Bug #3372)
</n>
 <f>4623</f>
 <g>http://pear.php.net/get/Net_Socket-1.0.6</g>
 <x xlink:href="package.1.0.6.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>pajoye</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the beta-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling class
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class</d>
 <da>2005-09-18 15:24:29</da>
 <n>This is a major milestone release for PEAR.  In addition to several killer features,
  every single element of PEAR has a regression test, and so stability is much higher
  than any previous PEAR release.

  New features in a nutshell:
  * full support for channels
  * pre-download dependency validation
  * new package.xml 2.0 format allows tremendous flexibility while maintaining BC
  * support for optional dependency groups and limited support for sub-packaging
  * robust dependency support
  * full dependency validation on uninstall
  * remote install for hosts with only ftp access - no more problems with
    restricted host installation [through PEAR_RemoteInstaller package]
  * full support for mirroring
  * support for bundling several packages into a single tarball
  * support for static dependencies on a uri-based package
  * support for custom file roles and installation tasks

  NOTE: users of PEAR_Frontend_Web/PEAR_Frontend_Gtk must upgrade their installations
  to the latest version, or PEAR will not upgrade properly</n>
 <f>266597</f>
 <g>http://pear.php.net/get/PEAR-1.4.0</g>
 <x xlink:href="package.1.4.0.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_highlighter/0.6.6.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/text_highlighter">Text_Highlighter</p>
 <c>pear.php.net</c>
 <v>0.6.6</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>blindman</m>
 <s>Syntax highlighting</s>
 <d>Text_Highlighter is a package for syntax highlighting.

It provides a base class provining all the functionality,
and a descendant classes geneator class.

The main idea is to simplify creation of subclasses
implementing syntax highlighting for particular language.
Subclasses do not implement any new functioanality,
they just provide syntax highlighting rules.
The rules sources are in XML format.

To create a highlighter for a language, there is no need
to code a new class manually. Simply describe the rules
in XML file and use Text_Highlighter_Generator to create
a new class.
</d>
 <da>2005-06-24 09:04:44</da>
 <n>+ fixed bug #4606 -- span end tag at beginning of a list
+ fixed bug #4607 -- The span class=&quot;hl-brackets&quot; shall not include the spaces before
+ fixed bug #4608 -- Tabs not expanded in list mode
</n>
 <f>118450</f>
 <g>http://pear.php.net/get/Text_Highlighter-0.6.6</g>
 <x xlink:href="package.0.6.6.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki/1.0.1.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/text_wiki">Text_Wiki</p>
 <c>pear.php.net</c>
 <v>1.0.1</v>
 <st>stable</st>
 <l>LGPL</l>
 <m>toggg</m>
 <s>Abstracts parsing and rendering rules for any markup as Wiki or BBCode in structured plain text.</s>
 <d>The text transformation is done in 2 steps, the parsers use rules to tokenize the content, renderers output the tokens and left text in the requested format.
Used for versatile transformers as well as converters.

</d>
 <da>2005-09-12 10:48:56</da>
 <n>This is a bug fix and security release, also preparing the introduction of new parsers/renderers
* Fixed bug 3881 and 4442, UTF-8 encoding problems.  There are new config options for Render_Xhtml, \'charset\' and \'quotes\', that allow you to specify the charset for translation.
* Fixed bug 3959, &quot;XHTML lists not rendered according W3C Standards&quot; (where a line of text before a list leaves an open paragraph tag and closes it after the list)
* In Parse_Xhtml_Toc, returns an extra newline before the parsed replacement text to comply with the matching regex.
* In Render_Xhtml_Toc, added a \'collapse\' config key to collapse the div horizontally within a table; this is for aesthetics, nothing else.  The \'collapse\' option is true by default.
* Added general rules Smiley, Subscript &quot;,,text,,&quot; and Underline &quot;__text__&quot;
* Added rendering rules Box, Font, Page, Plugin, Preformatted, Specialchar and Titelbar
              for the optional extra parsers (BBCode, Cowiki, Doku, Mediawiki and Tiki)
* Fixed bug 4175 &quot;Wrong transform method&quot; by generating PEAR_Error objects when a parse, format, or render rule cannot be found.
* applied feature request 4436 &quot;Add option to getTokens to get original token indices&quot; -- now the return array from getTokens() is keyed to the original token index number.
* Fixed Bug #4473 Undefined variables in error()
* Fixed bug 4474 to silence calls to htmlentities and htmlspecialchars so that errors about charsets don\'t pop up, per counsel from Jan at Horde.
* Fixed Code potential nesting
* Fixed bug #4719, &quot;In Latex, newline rule does not produce a new line&quot;
* Request #4520  	Additional space confuses image tag, adapted regexp
* Request #4634  	Code block title/filename, uses conf css_filename
* Code Xhtml: add php tags only if not there
* Heading: collapsing in headers
* Colortext Xhtml: don\'t add # if allready present
* Anchor Xhtml htlmentify the link
* Cleaned Xhtml renderers documentation headers
* Added an example in doc
* Rowspan and space before free format in Table renderer
* Secured url linked on images

</n>
 <f>62189</f>
 <g>http://pear.php.net/get/Text_Wiki-1.0.1</g>
 <x xlink:href="package.1.0.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_parser/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>XML_Parser</p>
 <c>pear.php.net</c>
 <r><v>1.2.4</v><s>stable</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.2.0beta3</v><s>beta</s></r>
 <r><v>1.2.0beta2</v><s>beta</s></r>
 <r><v>1.2.0beta1</v><s>beta</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.1.0beta2</v><s>beta</s></r>
 <r><v>1.1.0beta1</v><s>beta</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_parser/1.3.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xml_parser">XML_Parser</p>
 <c>pear.php.net</c>
 <v>1.3.2</v>
 <st>stable</st>
 <l>BSD License</l>
 <m>ashnazg</m>
 <s>XML parsing class based on PHP\'s bundled expat</s>
 <d>This is an XML parser based on PHPs built-in xml extension.
It supports two basic modes of operation: &quot;func&quot; and &quot;event&quot;.  In &quot;func&quot; mode, it will look for a function named after each element (xmltag_ELEMENT for start tags and xmltag_ELEMENT_ for end tags), and in &quot;event&quot; mode it uses a set of generic callbacks.

Since version 1.2.0 there\'s a new XML_Parser_Simple class that makes parsing of most XML documents easier, by automatically providing a stack for the elements.
Furthermore its now possible to split the parser from the handler object, so you do not have to extend XML_Parser anymore in order to parse a document with it.</d>
 <da>2009-01-21 19:59:04</da>
 <n>- Fix Bug #9328: assigned by reference error in XML_RSS parse
- add an AllTests.php for PHPUnit usage</n>
 <f>16260</f>
 <g>http://pear.php.net/get/XML_Parser-1.3.2</g>
 <x xlink:href="package.1.3.2.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>XML_RPC</p>
 <c>pear.php.net</c>
 <r><v>1.2.0RC6</v><s>beta</s></r>
 <r><v>1.2.0RC5</v><s>beta</s></r>
 <r><v>1.2.0RC4</v><s>beta</s></r>
 <r><v>1.2.0RC3</v><s>beta</s></r>
 <r><v>1.2.0RC2</v><s>beta</s></r>
 <r><v>1.2.0RC1</v><s>beta</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/1.5.1.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xml_rpc">XML_RPC</p>
 <c>pear.php.net</c>
 <v>1.5.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>danielc</m>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.

It has support for HTTP/HTTPS transport, proxies and authentication.</d>
 <da>2006-10-28 13:06:21</da>
 <n>* Turn passing payload through mb_convert_encoding() off by default.  Use new XML_RPC_Message::setConvertPayloadEncoding() and XML_RPC_Server::setConvertPayloadEncoding() to turn it on.  Bug 8632.
* Have XML_RPC_Value::scalarval() return FALSE if value is not a scalar.  Bug 8251.</n>
 <f>32215</f>
 <g>http://pear.php.net/get/XML_RPC-1.5.1</g>
 <x xlink:href="package.1.5.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/xml_parser/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>XML_Parser</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/XML">XML</ca>
 <l>BSD License</l>
 <s>XML parsing class based on PHP\'s bundled expat</s>
 <d>This is an XML parser based on PHPs built-in xml extension.
It supports two basic modes of operation: &quot;func&quot; and &quot;event&quot;.  In &quot;func&quot; mode, it will look for a function named after each element (xmltag_ELEMENT for start tags and xmltag_ELEMENT_ for end tags), and in &quot;event&quot; mode it uses a set of generic callbacks.

Since version 1.2.0 there\'s a new XML_Parser_Simple class that makes parsing of most XML documents easier, by automatically providing a stack for the elements.
Furthermore its now possible to split the parser from the handler object, so you do not have to extend XML_Parser anymore in order to parse a document with it.</d>
 <r xlink:href="/rest/r/xml_parser"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_parser/deps.1.3.2.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}s:7:"package";a:2:{s:4:"name";s:4:"PEAR";s:7:"channel";s:12:"pear.php.net";}}}', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/xml_rpc/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>XML_RPC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.

It has support for HTTP/HTTPS transport, proxies and authentication.</d>
 <r xlink:href="/rest/r/xml_rpc"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/deps.1.5.1.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:9:"extension";a:1:{s:4:"name";s:3:"xml";}}}', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/1.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/console_getopt">Console_Getopt</p>
 <c>pear.php.net</c>
 <v>1.2</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>andrei</m>
 <s>Command-line option parser</s>
 <d>This is a PHP implementation of &quot;getopt&quot; supporting both
short and long options.
</d>
 <da>2003-12-11 14:26:46</da>
 <n>Fix to preserve BC with 1.0 and allow correct behaviour for new users
</n>
 <f>3370</f>
 <g>http://pear.php.net/get/Console_Getopt-1.2</g>
 <x xlink:href="package.1.2.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki/0.25.0.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/text_wiki">Text_Wiki</p>
 <c>pear.php.net</c>
 <v>0.25.0</v>
 <st>beta</st>
 <l>LGPL</l>
 <m>pmjones</m>
 <s>Abstracts parsing and rendering rules for Wiki markup in structured plain text.</s>
 <d>Abstracts parsing and rendering rules for Wiki markup in structured plain text.
</d>
 <da>2005-02-01 11:59:45</da>
 <n>* moved all parsing rules from Text/Wiki/Parse to Text/Wiki/Parse/Default (this will help separate entire parsing rule sets, e.g. BBCode)
* changed Wiki.php to use the new Parse/Default directory as the default directory
* fixed interwiki regex so that page names starting with : are not honored (it was messing up wikilinks with 2 colons in the text)
</n>
 <f>46425</f>
 <g>http://pear.php.net/get/Text_Wiki-0.25.0</g>
 <x xlink:href="package.0.25.0.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/1.2.0RC6.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xml_rpc">XML_RPC</p>
 <c>pear.php.net</c>
 <v>1.2.0RC6</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>danielc</m>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.
It has support for HTTP transport, proxies and authentication.
</d>
 <da>2005-01-24 16:15:35</da>
 <n>- Don\'t put the protocol in the Host field of the POST data.  (danielc)
</n>
 <f>18691</f>
 <g>http://pear.php.net/get/XML_RPC-1.2.0RC6</g>
 <x xlink:href="package.1.2.0RC6.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/deps.1.2.0RC6.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_wiki/deps.0.25.0.txt", 'b:0;', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/1.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/archive_tar">Archive_Tar</p>
 <c>pear.php.net</c>
 <v>1.2</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>vblavet</m>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.
</d>
 <da>2004-05-08 10:03:17</da>
 <n>Add support for other separator than the space char and bug
	correction
</n>
 <f>14792</f>
 <g>http://pear.php.net/get/Archive_Tar-1.2</g>
 <x xlink:href="package.1.2.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/deps.1.2.txt", 'b:0;', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_socket/1.0.5.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/net_socket">Net_Socket</p>
 <c>pear.php.net</c>
 <v>1.0.5</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>chagenbu</m>
 <s>Network Socket Interface</s>
 <d>Net_Socket is a class interface to TCP sockets.  It provides blocking
and non-blocking operation, with different reading and writing modes
(byte-wise, block-wise, line-wise and special formats like network
byte-order ip addresses).
</d>
 <da>2005-01-11 17:04:44</da>
 <n>Don\'t rely on gethostbyname() for error checking (Bug #3100).
</n>
 <f>4208</f>
 <g>http://pear.php.net/get/Net_Socket-1.0.5</g>
 <x xlink:href="package.1.0.5.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_socket/deps.1.0.5.txt", 'b:0;', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/file/1.1.0RC5.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/file">File</p>
 <c>pear.php.net</c>
 <v>1.1.0RC5</v>
 <st>beta</st>
 <l>PHP</l>
 <m>pajoye</m>
 <s>Common file and directory routines</s>
 <d>Provides easy access to read/write to files along with
some common routines to deal with paths. Also provides
interface for handling CSV files.
</d>
 <da>2005-02-02 17:28:28</da>
 <n>* Bug #3364 fixed, typo
</n>
 <f>14739</f>
 <g>http://pear.php.net/get/File-1.1.0RC5</g>
 <x xlink:href="package.1.1.0RC5.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_highlighter/0.6.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/text_highlighter">Text_Highlighter</p>
 <c>pear.php.net</c>
 <v>0.6.2</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>blindman</m>
 <s>Syntax highlighting</s>
 <d>Text_Highlighter is a package for syntax highlighting.

It provides a base class provining all the functionality,
and a descendant classes geneator class.

The main idea is to simplify creation of subclasses
implementing syntax highlighting for particular language.
Subclasses do not implement any new functioanality,
they just provide syntax highlighting rules.
The rules sources are in XML format.

To create a highlighter for a language, there is no need
to code a new class manually. Simply describe the rules
in XML file and use Text_Highlighter_Generator to create
a new class.
</d>
 <da>2005-02-04 02:13:54</da>
 <n>- fixed Bug #3060 : Wrong render with HL_NUMBERS_TABLE option
- fixed Bug #3063 : Output buffer is not cleared before rendering in HTML renderer
</n>
 <f>55103</f>
 <g>http://pear.php.net/get/Text_Highlighter-0.6.2</g>
 <x xlink:href="package.0.6.2.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/file/deps.1.1.0RC5.txt", 'a:4:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:3:"yes";}i:3;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"pcre";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/text_highlighter/deps.0.6.2.txt", 'a:3:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.1";s:8:"optional";s:2:"no";s:4:"name";s:10:"XML_Parser";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:8:"optional";s:2:"no";s:4:"name";s:14:"Console_Getopt";}}', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3.4.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3.4</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception php5-only exception class
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
 * the PEAR base class
</d>
 <da>2005-01-01 20:26:39</da>
 <n>* fix a serious problem caused by a bug in all versions of PHP that caused multiple registration
  of the shutdown function of PEAR.php
* fix Bug #2861: package.dtd does not define NUMBER
* fix Bug #2946: ini_set warning errors
* fix Bug #3026: Dependency type &quot;ne&quot; is needed, &quot;not&quot; is not handled
  properly
* fix Bug #3061: potential warnings in PEAR_Exception
* implement Request #2848: PEAR_ErrorStack logger extends, PEAR_ERRORSTACK_DIE
* implement Request #2914: Dynamic Include Path for run-tests command
* make pear help listing more useful (put how-to-use info at the bottom of the listing)
</n>
 <f>107207</f>
 <g>http://pear.php.net/get/PEAR-1.3.4</g>
 <x xlink:href="package.1.3.4.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3.4.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.2";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"xml";}i:6;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}}', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_parser/1.2.4.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xml_parser">XML_Parser</p>
 <c>pear.php.net</c>
 <v>1.2.4</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>schst</m>
 <s>XML parsing class based on PHP\'s bundled expat</s>
 <d>This is an XML parser based on PHPs built-in xml extension.
It supports two basic modes of operation: &quot;func&quot; and &quot;event&quot;.  In &quot;func&quot; mode, it will look for a function named after each element (xmltag_ELEMENT for start tags and xmltag_ELEMENT_ for end tags), and in &quot;event&quot; mode it uses a set of generic callbacks.

Since version 1.2.0 there\'s a new XML_Parser_Simple class that makes parsing of most XML documents easier, by automatically providing a stack for the elements.
Furthermore its now possible to split the parser from the handler object, so you do not have to extend XML_Parser anymore in order to parse a document with it.
</d>
 <da>2005-01-18 15:12:51</da>
 <n>- fixed a bug in XML_Parser_Simple when trying to register more than the default handlers and a separate callback object (schst)
</n>
 <f>10858</f>
 <g>http://pear.php.net/get/XML_Parser-1.2.4</g>
 <x xlink:href="package.1.2.4.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_parser/deps.1.2.4.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";s:8:"optional";s:2:"no";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}}', 'text/xml');

$p1  = $packageDir . 'File-1.1.0RC3.tgz';
$p2  = $packageDir . 'Net_Sieve-1.1.0.tgz';
$p3  = $packageDir . 'Text_Highlighter-0.6.1.tgz';
$p4  = $packageDir . 'Text_Wiki-0.23.1.tgz';
$p5  = $packageDir . 'XML_RPC-1.2.0RC3.tgz';
$p6  = $packageDir . 'Net_Socket-1.0.5.tgz';
$p7  = $packageDir . 'PEAR-1.3.4.tgz';
$p8  = $packageDir . 'Console_Getopt-1.2.tgz';
$p9  = $packageDir . 'XML_Parser-1.2.4.tgz';
$p10 = $packageDir . 'Archive_Tar-1.2.tgz';
for ($i = 1; $i <= 10; $i++) {
    $packages[] = ${"p$i"};
}

$config->set('preferred_state', 'alpha');
$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setExtensions(array('xml' => '1.0', 'pcre' => '1.0'));
$command->run('install', array(), $packages);
$phpunit->assertNoErrors('after install');

$fakelog->getLog();
$command->_reset_downloader();
$command->run('upgrade-all', array(), array());

$phpunit->assertNoErrors('after upgrade');

$log = array_slice($fakelog->getLog(), 0, 5);
function poop($a, $b) {return strnatcasecmp($a['info']['data'], $b['info']['data']);}
usort($log, 'poop');

$phpunit->assertEquals(
array (
  array (
    'info' =>
    array (
      'data' => 'Will upgrade channel://pear.php.net/file',
    ),
    'cmd' => 'upgrade-all',
  ),
  array (
    'info' =>
    array (
      'data' => 'Will upgrade channel://pear.php.net/net_sieve',
    ),
    'cmd' => 'upgrade-all',
  ),
  array (
    'info' =>
    array (
      'data' => 'Will upgrade channel://pear.php.net/text_highlighter',
    ),
    'cmd' => 'upgrade-all',
  ),
  array (
    'info' =>
    array (
      'data' => 'Will upgrade channel://pear.php.net/text_wiki',
    ),
    'cmd' => 'upgrade-all',
  ),
  array (
    'info' =>
    array (
      'data' => 'Will upgrade channel://pear.php.net/xml_rpc',
    ),
    'cmd' => 'upgrade-all',
  ),
  ), $log, 'same log entries');
$reg = &$config->getRegistry();
$phpunit->assertEquals('1.1.0RC5', $reg->packageInfo('File', 'version'),             'File');
$phpunit->assertEquals('1.1.1',    $reg->packageInfo('Net_Sieve', 'version'),        'Net_Sieve');
$phpunit->assertEquals('0.6.2',    $reg->packageInfo('Text_Highlighter', 'version'), 'Text_Highlighter');
$phpunit->assertEquals('0.25.0',   $reg->packageInfo('Text_Wiki', 'version'),        'Text_Wiki');
$phpunit->assertEquals('1.2.0RC6', $reg->packageInfo('XML_RPC', 'version'),          'XML_RPC');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
