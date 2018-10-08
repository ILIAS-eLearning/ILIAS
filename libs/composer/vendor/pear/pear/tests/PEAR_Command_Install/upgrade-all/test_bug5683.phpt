--TEST--
upgrade-all command - real-world example from Bug #5683
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/allreleases.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>XML_RPC</p>
 <c>pear.php.net</c>
 <r><v>1.4.3</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.1</max></co>
</r>
 <r><v>1.4.2</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a14</max></co>
</r>
 <r><v>1.4.1</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a14</max></co>
</r>
 <r><v>1.4.0</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.3</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.2</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.1</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.0</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.0RC3</v><s>beta</s></r>
 <r><v>1.3.0RC2</v><s>beta</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a10</max></co>
</r>
 <r><v>1.3.0RC1</v><s>beta</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a10</max></co>
</r>
 <r><v>1.2.2</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a4</max></co>
</r>
 <r><v>1.2.1</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a2</max></co>
</r>
 <r><v>1.2.0</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a1</max></co>
</r>
 <r><v>1.2.0RC7</v><s>beta</s></r>
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
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/1.4.3.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xml_rpc">XML_RPC</p>
 <c>pear.php.net</c>
 <v>1.4.3</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>danielc</m>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.

It has support for HTTP/HTTPS transport, proxies and authentication.</d>
 <da>2005-09-24 14:22:55</da>
 <n>* Make XML_RPC_encode() properly handle dateTime.iso8601.  Request 5117.</n>
 <f>27198</f>
 <g>http://pear.php.net/get/XML_RPC-1.4.3</g>
 <x xlink:href="package.1.4.3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/deps.1.4.3.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}}}', 'text/xml');
$dir = dirname(__FILE__). DIRECTORY_SEPARATOR . 'bug5683' . DIRECTORY_SEPARATOR;
$pf1 = $dir . 'PEAR-1.4.0.tgz';
$pf2 = $dir . 'Console_Getopt-1.2.tgz';
$pf3 = $dir . 'Archive_Tar-1.3.1.tgz';
$pf4 = $dir . 'XML_RPC-1.4.1.tgz';
$_test_dep->setPHPVersion('4.3.0');
$_test_dep->setPEARVersion('1.4.0');
$_test_dep->setExtensions(array('xml' => 0, 'pcre' => 1));
$command->run('install', array(), array($pf1, $pf2, $pf3, $pf4));
$phpunit->assertNoErrors('setup');
$fakelog->getLog();
$phpunit->assertEquals(4, count($reg->listPackages()), 'installed package list');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/packages.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pear.php.net</c>
 <p>Archive_Tar</p>
 <p>Archive_Zip</p>
 <p>Auth</p>
 <p>Auth_Enterprise</p>
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
 <p>CodeGen_MySQL_UDF</p>
 <p>CodeGen_PECL</p>
 <p>Config</p>
 <p>Console_Color</p>
 <p>Console_Getargs</p>
 <p>Console_Getopt</p>
 <p>Console_ProgressBar</p>
 <p>Console_Table</p>
 <p>Contact_AddressBook</p>
 <p>Contact_Vcard_Build</p>
 <p>Contact_Vcard_Parse</p>
 <p>Crypt_Blowfish</p>
 <p>Crypt_CBC</p>
 <p>Crypt_CHAP</p>
 <p>Crypt_HMAC</p>
 <p>Crypt_RC4</p>
 <p>Crypt_RSA</p>
 <p>Crypt_Xtea</p>
 <p>Date</p>
 <p>Date_Holidays</p>
 <p>DB</p>
 <p>DBA</p>
 <p>DBA_Relational</p>
 <p>DB_ado</p>
 <p>DB_DataObject</p>
 <p>DB_DataObject_FormBuilder</p>
 <p>DB_ldap</p>
 <p>DB_ldap2</p>
 <p>DB_NestedSet</p>
 <p>DB_odbtp</p>
 <p>DB_Pager</p>
 <p>DB_QueryTool</p>
 <p>DB_Sqlite_Tools</p>
 <p>DB_Table</p>
 <p>Event_Dispatcher</p>
 <p>File</p>
 <p>File_Archive</p>
 <p>File_Bittorrent</p>
 <p>File_DICOM</p>
 <p>File_DNS</p>
 <p>File_Find</p>
 <p>File_Fortune</p>
 <p>File_Fstab</p>
 <p>File_Gettext</p>
 <p>File_HtAccess</p>
 <p>File_IMC</p>
 <p>File_Ogg</p>
 <p>File_Passwd</p>
 <p>File_PDF</p>
 <p>File_SearchReplace</p>
 <p>File_SMBPasswd</p>
 <p>FSM</p>
 <p>Games_Chess</p>
 <p>Genealogy_Gedcom</p>
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
 <p>HTML_Form</p>
 <p>HTML_Javascript</p>
 <p>HTML_Menu</p>
 <p>HTML_Page</p>
 <p>HTML_Page2</p>
 <p>HTML_Progress</p>
 <p>HTML_Progress2</p>
 <p>HTML_QuickForm</p>
 <p>HTML_QuickForm_advmultiselect</p>
 <p>HTML_QuickForm_Controller</p>
 <p>HTML_QuickForm_SelectFilter</p>
 <p>HTML_Safe</p>
 <p>HTML_Select</p>
 <p>HTML_Select_Common</p>
 <p>HTML_Table</p>
 <p>HTML_Table_Matrix</p>
 <p>HTML_Template_Flexy</p>
 <p>HTML_Template_IT</p>
 <p>HTML_Template_PHPLIB</p>
 <p>HTML_Template_Sigma</p>
 <p>HTML_Template_Xipe</p>
 <p>HTML_TreeMenu</p>
 <p>HTTP</p>
 <p>HTTP_Client</p>
 <p>HTTP_Download</p>
 <p>HTTP_Header</p>
 <p>HTTP_Request</p>
 <p>HTTP_Server</p>
 <p>HTTP_Session</p>
 <p>HTTP_Session2</p>
 <p>HTTP_SessionServer</p>
 <p>HTTP_Upload</p>
 <p>HTTP_WebDAV_Client</p>
 <p>HTTP_WebDAV_Server</p>
 <p>I18N</p>
 <p>I18Nv2</p>
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
 <p>Image_MonoBMP</p>
 <p>Image_Remote</p>
 <p>Image_Text</p>
 <p>Image_Tools</p>
 <p>Image_Transform</p>
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
 <p>Mail_Queue</p>
 <p>Math_Basex</p>
 <p>Math_BinaryUtils</p>
 <p>Math_Complex</p>
 <p>Math_Fibonacci</p>
 <p>Math_Finance</p>
 <p>Math_Fraction</p>
 <p>Math_Histogram</p>
 <p>Math_Integer</p>
 <p>Math_Matrix</p>
 <p>Math_Numerical_RootFinding</p>
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
 <p>MDB_QueryTool</p>
 <p>Message</p>
 <p>MIME_Type</p>
 <p>MP3_ID</p>
 <p>MP3_Playlist</p>
 <p>Net_CheckIP</p>
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
 <p>Net_Geo</p>
 <p>Net_GeoIP</p>
 <p>Net_HL7</p>
 <p>Net_Ident</p>
 <p>Net_IDNA</p>
 <p>Net_IMAP</p>
 <p>Net_IPv4</p>
 <p>Net_IPv6</p>
 <p>Net_IRC</p>
 <p>Net_LDAP</p>
 <p>Net_LMTP</p>
 <p>Net_Monitor</p>
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
 <p>Net_UserAgent_Detect</p>
 <p>Net_UserAgent_Mobile</p>
 <p>Net_Whois</p>
 <p>Net_Wifi</p>
 <p>Numbers_Roman</p>
 <p>Numbers_Words</p>
 <p>OLE</p>
 <p>Pager</p>
 <p>Pager_Sliding</p>
 <p>Payment_Clieop</p>
 <p>Payment_DTA</p>
 <p>Payment_Process</p>
 <p>PEAR</p>
 <p>PEAR_Delegator</p>
 <p>PEAR_ErrorStack</p>
 <p>PEAR_Frontend_Gtk</p>
 <p>PEAR_Frontend_Web</p>
 <p>PEAR_Info</p>
 <p>PEAR_PackageFileManager</p>
 <p>PEAR_PackageFileManager_GUI_Gtk</p>
 <p>PEAR_RemoteInstaller</p>
 <p>PHPDoc</p>
 <p>PhpDocumentor</p>
 <p>PHPUnit</p>
 <p>PHPUnit2</p>
 <p>PHP_Archive</p>
 <p>PHP_Beautifier</p>
 <p>PHP_Compat</p>
 <p>PHP_CompatInfo</p>
 <p>PHP_Fork</p>
 <p>PHP_Parser</p>
 <p>RDF</p>
 <p>RDF_N3</p>
 <p>RDF_NTriple</p>
 <p>RDF_RDQL</p>
 <p>Science_Chemistry</p>
 <p>ScriptReorganizer</p>
 <p>Search_Mnogosearch</p>
 <p>Services_Amazon</p>
 <p>Services_Delicious</p>
 <p>Services_DynDNS</p>
 <p>Services_Ebay</p>
 <p>Services_ExchangeRates</p>
 <p>Services_Google</p>
 <p>Services_Pingback</p>
 <p>Services_Technorati</p>
 <p>Services_Trackback</p>
 <p>Services_Weather</p>
 <p>Services_Webservice</p>
 <p>Services_Yahoo</p>
 <p>SOAP</p>
 <p>SOAP_Interop</p>
 <p>Spreadsheet_Excel_Writer</p>
 <p>SQL_Parser</p>
 <p>Stream_SHM</p>
 <p>Stream_Var</p>
 <p>Structures_DataGrid</p>
 <p>Structures_Graph</p>
 <p>System_Command</p>
 <p>System_Mount</p>
 <p>System_ProcWatch</p>
 <p>System_SharedMemory</p>
 <p>System_Socket</p>
 <p>System_WinDrives</p>
 <p>Text_CAPTCHA</p>
 <p>Text_Diff</p>
 <p>Text_Figlet</p>
 <p>Text_Highlighter</p>
 <p>Text_Huffman</p>
 <p>Text_Lexer</p>
 <p>Text_Password</p>
 <p>Text_Statistics</p>
 <p>Text_TeXHyphen</p>
 <p>Text_Wiki</p>
 <p>Text_Wiki_BBCode</p>
 <p>Text_Wiki_Cowiki</p>
 <p>Text_Wiki_Doku</p>
 <p>Text_Wiki_Tiki</p>
 <p>Translation</p>
 <p>Translation2</p>
 <p>Tree</p>
 <p>UDDI</p>
 <p>Validate</p>
 <p>Validate_AT</p>
 <p>Validate_AU</p>
 <p>Validate_BE</p>
 <p>Validate_CA</p>
 <p>Validate_CH</p>
 <p>Validate_DE</p>
 <p>Validate_DK</p>
 <p>Validate_ES</p>
 <p>Validate_Finance</p>
 <p>Validate_Finance_CreditCard</p>
 <p>Validate_FR</p>
 <p>Validate_IS</p>
 <p>Validate_ISPN</p>
 <p>Validate_NL</p>
 <p>Validate_PL</p>
 <p>Validate_ptBR</p>
 <p>Validate_UK</p>
 <p>Validate_US</p>
 <p>Validate_ZA</p>
 <p>Var_Dump</p>
 <p>VersionControl_SVN</p>
 <p>VFS</p>
 <p>XML_Beautifier</p>
 <p>XML_CSSML</p>
 <p>XML_DTD</p>
 <p>XML_FastCreate</p>
 <p>XML_Feed_Parser</p>
 <p>XML_fo2pdf</p>
 <p>XML_FOAF</p>
 <p>XML_HTMLSax</p>
 <p>XML_HTMLSax3</p>
 <p>XML_image2svg</p>
 <p>XML_Indexing</p>
 <p>XML_MXML</p>
 <p>XML_NITF</p>
 <p>XML_Parser</p>
 <p>XML_RDDL</p>
 <p>XML_RPC</p>
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
 <p>XML_XSLT_Wrapper</p>
 <p>XML_XUL</p>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Tar</p>
 <c>pear.php.net</c>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.10-b1</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_Getopt</p>
 <c>pear.php.net</c>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.11</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pecl.php.net/rest/p/packages.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pecl.php.net</c>
 <p>APC</p>
 <p>apd</p>
 <p>archive</p>
 <p>bcompiler</p>
 <p>big_int</p>
 <p>Bitset</p>
 <p>BLENC</p>
 <p>bz2</p>
 <p>bz2_filter</p>
 <p>classkit</p>
 <p>clips</p>
 <p>coin_acceptor</p>
 <p>colorer</p>
 <p>crack</p>
 <p>crack_dll</p>
 <p>cvsclient</p>
 <p>cybercash</p>
 <p>cybermut</p>
 <p>cyrus</p>
 <p>daffodildb</p>
 <p>date_time</p>
 <p>dazuko</p>
 <p>DBDO</p>
 <p>dbplus</p>
 <p>dbx</p>
 <p>dio</p>
 <p>domxml</p>
 <p>DTrace</p>
 <p>ecasound</p>
 <p>enchant</p>
 <p>esmtp</p>
 <p>event</p>
 <p>expect</p>
 <p>fann</p>
 <p>ffi</p>
 <p>Fileinfo</p>
 <p>filter</p>
 <p>FreeImage</p>
 <p>fribidi</p>
 <p>gnupg</p>
 <p>html_parse</p>
 <p>huffman</p>
 <p>ibm_db2</p>
 <p>id3</p>
 <p>idn</p>
 <p>imagick</p>
 <p>imlib2</p>
 <p>ingres</p>
 <p>intercept</p>
 <p>isis</p>
 <p>kadm5</p>
 <p>lchash</p>
 <p>lzf</p>
 <p>mailparse</p>
 <p>maxdb</p>
 <p>mcrypt_filter</p>
 <p>mcve</p>
 <p>mdbtools</p>
 <p>memcache</p>
 <p>mono</p>
 <p>mqseries</p>
 <p>mysql</p>
 <p>namazu</p>
 <p>netools</p>
 <p>Net_Gopher</p>
 <p>newt</p>
 <p>notes</p>
 <p>oci8</p>
 <p>odbtp</p>
 <p>oggvorbis</p>
 <p>openal</p>
 <p>opendirectory</p>
 <p>Ovrimis</p>
 <p>panda</p>
 <p>Paradox</p>
 <p>parsekit</p>
 <p>pdflib</p>
 <p>PDO</p>
 <p>PDO_DBLIB</p>
 <p>PDO_FIREBIRD</p>
 <p>PDO_IDS</p>
 <p>PDO_MYSQL</p>
 <p>PDO_OCI</p>
 <p>PDO_ODBC</p>
 <p>PDO_PGSQL</p>
 <p>PDO_SQLITE</p>
 <p>PECL_Gen</p>
 <p>pecl_http</p>
 <p>perforce</p>
 <p>perl</p>
 <p>PHPScript</p>
 <p>POP3</p>
 <p>postparser</p>
 <p>ps</p>
 <p>python</p>
 <p>radius</p>
 <p>rar</p>
 <p>rpmreader</p>
 <p>runkit</p>
 <p>sasl</p>
 <p>sdo</p>
 <p>shape</p>
 <p>SPL</p>
 <p>spplus</p>
 <p>spread</p>
 <p>SQLite</p>
 <p>ssh2</p>
 <p>statgrab</p>
 <p>StreamsXml</p>
 <p>svn</p>
 <p>TCLink</p>
 <p>tcpwrap</p>
 <p>tidy</p>
 <p>timezonedb</p>
 <p>tk</p>
 <p>translit</p>
 <p>tvision</p>
 <p>uuid</p>
 <p>Valkyrie</p>
 <p>vld</p>
 <p>vpopmail</p>
 <p>win32ps</p>
 <p>win32ps_dll</p>
 <p>win32std</p>
 <p>WinBinder</p>
 <p>xattr</p>
 <p>Xdebug</p>
 <p>xdiff</p>
 <p>xmlReader</p>
 <p>XMLRPCi</p>
 <p>xmlwriter</p>
 <p>xmms</p>
 <p>yaz</p>
 <p>zeroconf</p>
 <p>zip</p>
 <p>zlib_filter</p>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
 <r><v>1.4.2</v><s>stable</s></r>
 <r><v>1.4.1</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.4.0RC2</v><s>beta</s></r>
 <r><v>1.4.0RC1</v><s>beta</s></r>
 <r><v>1.4.0b2</v><s>beta</s></r>
 <r><v>1.4.0b1</v><s>beta</s></r>
 <r><v>1.3.6</v><s>stable</s></r>
 <r><v>1.4.0a12</v><s>alpha</s></r>
 <r><v>1.4.0a11</v><s>alpha</s></r>
 <r><v>1.4.0a10</v><s>alpha</s></r>
 <r><v>1.4.0a9</v><s>alpha</s></r>
 <r><v>1.4.0a8</v><s>alpha</s></r>
 <r><v>1.4.0a7</v><s>alpha</s></r>
 <r><v>1.4.0a6</v><s>alpha</s></r>
 <r><v>1.4.0a5</v><s>alpha</s></r>
 <r><v>1.4.0a4</v><s>alpha</s></r>
 <r><v>1.4.0a3</v><s>alpha</s></r>
 <r><v>1.4.0a2</v><s>alpha</s></r>
 <r><v>1.4.0a1</v><s>alpha</s></r>
 <r><v>1.3.5</v><s>stable</s></r>
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
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.2</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the beta-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class

  New features in a nutshell:
  * full support for channels
  * pre-download dependency validation
  * new package.xml 2.0 format allows tremendous flexibility while maintaining BC
  * support for optional dependency groups and limited support for sub-packaging
  * robust dependency support
  * full dependency validation on uninstall
  * remote install for hosts with only ftp access - no more problems with
    restricted host installation
  * full support for mirroring
  * support for bundling several packages into a single tarball
  * support for static dependencies on a url-based package
  * support for custom file roles and installation tasks

  NOTE: users of PEAR_Frontend_Web/PEAR_Frontend_Gtk must upgrade their installations
  to the latest version, or PEAR will not upgrade properly</d>
 <da>2005-10-08 19:53:23</da>
 <n>Minor bugfix release
* fix issues with API for adding tasks to package2.xml
* fix Bug #5520: pecl pickle fails on pecl pickle fails on extension/package deps
* fix Bug #5523: pecl pickle misses to put configureoptions into package.xml v1
* fix Bug #5527: No need for cpp
* fix Bug #5529: configure options in package.xml 2.0 will be ignored
* fix Bug #5530: PEAR_PackageFile_v2-&gt;getConfigureOptions() API incompatible with v1
* fix Bug #5531: adding of installconditions/binarypackage/configure options
                 to extsrc may fail
* fix Bug #5550: PHP notices/warnings/errors are 1 file off in trace
* fix Bug #5580: pear makerpm XML_sql2xml-0.3.2.tgz error
* fix Bug #5619: pear makerpm produces invalid .spec dependancy code
* fix Bug #5629: pear install http_download dies with bad error message</n>
 <f>270370</f>
 <g>http://pear.php.net/get/PEAR-1.4.2</g>
 <x xlink:href="package.1.4.2.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.2.txt", 'a:2:{s:8:"required";a:4:{s:3:"php";a:1:{s:3:"min";s:3:"4.2";}s:13:"pearinstaller";a:1:{s:3:"min";s:8:"1.4.0a12";}s:7:"package";a:5:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.4.0";s:11:"recommended";s:5:"1.4.3";}i:3;a:5:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"max";s:5:"0.5.0";s:7:"exclude";s:5:"0.5.0";s:9:"conflicts";s:0:"";}i:4;a:5:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"max";s:5:"0.4.0";s:7:"exclude";s:5:"0.4.0";s:9:"conflicts";s:0:"";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:5:"group";a:2:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/deps.1.4.3.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/1.3.1.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
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
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/deps.1.3.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/1.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
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
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the beta-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class

  New features in a nutshell:
  * full support for channels
  * pre-download dependency validation
  * new package.xml 2.0 format allows tremendous flexibility while maintaining BC
  * support for optional dependency groups and limited support for sub-packaging
  * robust dependency support
  * full dependency validation on uninstall
  * remote install for hosts with only ftp access - no more problems with
    restricted host installation
  * full support for mirroring
  * support for bundling several packages into a single tarball
  * support for static dependencies on a url-based package
  * support for custom file roles and installation tasks

  NOTE: users of PEAR_Frontend_Web/PEAR_Frontend_Gtk must upgrade their installations
  to the latest version, or PEAR will not upgrade properly</d>
 <r xlink:href="/rest/r/pear"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_tar/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
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
$pearweb->addRESTConfig("http://pear.php.net/rest/p/console_getopt/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Console_Getopt</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Console">Console</ca>
 <l>PHP License</l>
 <s>Command-line option parser</s>
 <d>This is a PHP implementation of &quot;getopt&quot; supporting both
short and long options.</d>
 <r xlink:href="/rest/r/console_getopt"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/xml_rpc/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>XML_RPC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.

It has support for HTTP/HTTPS transport, proxies and authentication.</d>
 <r xlink:href="/rest/r/xml_rpc"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/deps.1.2.txt", 'b:0;', 'text/xml');
$pearweb->addHTMLConfig('http://pear.php.net/get/PEAR-1.4.2.tgz', $dir . 'PEAR-1.4.2.tgz');
$pearweb->addHTMLConfig('http://pear.php.net/get/XML_RPC-1.4.3.tgz', $dir . 'XML_RPC-1.4.3.tgz');
unset($GLOBALS['__Stupid_php4_a']); // reset downloader
$command->run('upgrade-all', array(), array());
$phpunit->assertNoErrors('afterwards');
$phpunit->assertEquals('1.4.2', $reg->packageInfo('PEAR', 'version'), 'PEAR version');
$phpunit->assertEquals('1.4.3', $reg->packageInfo('XML_RPC', 'version'), 'XML_RPC version');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
