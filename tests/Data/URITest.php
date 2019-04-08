<?php
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class URITest extends TestCase {

	const URI_COMPLETE = 'g+it://github.com:8080/someaccount/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';

	const URI_COMPLETE_IPV4 = 'g+it://10.0.0.86:8080/someaccount/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';

	const URI_COMPLETE_LOCALHOST = 'g+it://localhost:8080/someaccount/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';


	const URI_NO_PATH_1 = 'g-it://ilias%2Da.de:8080?query_par_1=val_1&query_par_2=val_2#fragment';
	const URI_NO_PATH_2 = 'g.it://amaz;on.co.uk:8080/?query_par_1=val_1&query_par_2=val_2#fragment';

	const URI_NO_QUERY_1 = 'git://one-letter-top-level.a:8080/someaccount/somerepo/somerepo.git/#fragment';
	const URI_NO_QUERY_2 = 'git://github.com:8080/someaccount/somerepo/somerepo.git#fragment';

	const URI_AUTHORITY_AND_QUERY_1 = 'git://github.com?query_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2';
	const URI_AUTHORITY_AND_QUERY_2 = 'git://github.com/?qu/ery_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2';

	const URI_AUTHORITY_AND_FRAGMENT = 'git://github.com:8080/#fragment$,;:A!\'*+()ar_1=val_1&';

	const URI_AUTHORITY_PATH_FRAGMENT = 'git://git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git#frag:A!\'*+()arment';

	const URI_PATH = 'git://git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git/';

	const URI_AUTHORITY_ONLY = 'git://git$,;hub.com';

	const URI_NO_SCHEMA = 'git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git/';

	const URI_NO_AUTHORITY = 'git://:8080/someaccount/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';

	const URI_WRONG_SCHEMA = 'gi$t://git$,;hub.com';

	const URI_WRONG_AUTHORITY_1 = 'git://git$,;hu<b.com:8080/someacc$,;ount/somerepo/somerepo.git/';
	const URI_WRONG_AUTHORITY_2 = 'git://git$,;hu=b.com/someacc$,;ount/somerepo/somerepo.git/';


	const URI_INVALID =  'https://host.de/ilias.php/"><script>alert(1)</script>?baseClass=ilObjChatroomGUI&cmd=getOSDNotifications&cmdMode=asynch&max_age=15192913';

	const URI_FAKEPCENC = 'g+it://github.com:8080/someaccoun%t/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';

	const URI_HOST_ALPHADIG_START_1 = 'g+it://-github.com:8080/someaccount';
	const URI_HOST_ALPHADIG_START_2 = 'g+it://github-.com:8080/someaccount';
	const URI_HOST_ALPHADIG_START_3 = 'http://.';
	const URI_HOST_ALPHADIG_START_4 = 'http://../';
	const URI_HOST_ALPHADIG_START_5 = 'http://-error-.invalid/';


	/**
	 * @doesNotPerformAssertions
	 */
	public function test_init()
	{
		return new ILIAS\Data\URI(self::URI_COMPLETE);
	}

	/**
	 * @depends test_init
	 */
	public function test_ipv4()
	{
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE_IPV4);
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'10.0.0.86:8080');
		$this->assertEquals($uri->getHost(),'10.0.0.86');
		$this->assertEquals($uri->getPort(),8080);
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
	}


	/**
	 * @depends test_init
	 */
	public function test_localhost()
	{
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE_LOCALHOST);
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'localhost:8080');
		$this->assertEquals($uri->getHost(),'localhost');
		$this->assertEquals($uri->getPort(),8080);
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
	}


	/**
	 * @depends test_init
	 */
	public function test_components($uri)
	{
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
	}

	/**
	 * @depends test_init
	 */
	public function test_base_uri($uri)
	{
		$this->assertEquals($uri->getBaseURI(),'g+it://github.com:8080/someaccount/somerepo/somerepo.git');
	}

	/**
	 * @depends test_init
	 */
	public function test_base_uri_idempotent($uri)
	{
		$base_uri = $uri->getBaseURI();
		$this->assertEquals($base_uri,'g+it://github.com:8080/someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');

		$uri = new ILIAS\Data\URI($base_uri);
		$this->assertEquals($base_uri,$uri->getBaseURI());
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertNull($uri->getQuery());
		$this->assertNull($uri->getFragment());
	}


	/**
	 * @depends test_init
	 */
	public function test_no_path()
	{
		$uri = new ILIAS\Data\URI(self::URI_NO_PATH_1);
		$this->assertEquals($uri->getSchema(),'g-it');
		$this->assertEquals($uri->getAuthority(),'ilias%2Da.de:8080');
		$this->assertEquals($uri->getHost(),'ilias%2Da.de');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertNull($uri->getPath());
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');

		$uri = new ILIAS\Data\URI(self::URI_NO_PATH_2);
		$this->assertEquals($uri->getSchema(),'g.it');
		$this->assertEquals($uri->getAuthority(),'amaz;on.co.uk:8080');
		$this->assertEquals($uri->getHost(),'amaz;on.co.uk');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertNull($uri->getPath());
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
	}

	/**
	 * @depends test_init
	 */
	public function test_no_query()
	{
		$uri = new ILIAS\Data\URI(self::URI_NO_QUERY_1);
		$this->assertEquals($uri->getSchema(),'git');
		$this->assertEquals($uri->getAuthority(),'one-letter-top-level.a:8080');
		$this->assertEquals($uri->getHost(),'one-letter-top-level.a');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertNull($uri->getQuery());
		$this->assertEquals($uri->getFragment(),'fragment');

		$uri = new ILIAS\Data\URI(self::URI_NO_QUERY_2);
		$this->assertEquals($uri->getSchema(),'git');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertNull($uri->getQuery());
		$this->assertEquals($uri->getFragment(),'fragment');
	}

	/**
	 * @depends test_init
	 */
	public function test_authority_and_query()
	{
		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_QUERY_1);
		$this->assertEquals($uri->getSchema(),'git');
		$this->assertEquals($uri->getAuthority(),'github.com');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertNull($uri->getPort());
		$this->assertNull($uri->getPath());
		$this->assertEquals($uri->getQuery(),'query_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2');
		$this->assertNull($uri->getFragment());

		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_QUERY_2);
		$this->assertEquals($uri->getSchema(),'git');
		$this->assertEquals($uri->getAuthority(),'github.com');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertNull($uri->getPort());
		$this->assertNull($uri->getPath());
		$this->assertEquals($uri->getQuery(),'qu/ery_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2');
		$this->assertNull($uri->getFragment());
	}

	/**
	 * @depends test_init
	 */
	public function test_authority_and_fragment()
	{
		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_FRAGMENT);
		$this->assertEquals($uri->getSchema(),'git');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertNull($uri->getPath());
		$this->assertNull($uri->getQuery());
		$this->assertEquals($uri->getFragment(),'fragment$,;:A!\'*+()ar_1=val_1&');
	}
	/**
	 * @depends test_init
	 */
	public function test_authority_path_fragment()
	{
		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_PATH_FRAGMENT);
		$this->assertEquals($uri->getSchema(),'git');
		$this->assertEquals($uri->getAuthority(),'git$,;hub.com:8080');
		$this->assertEquals($uri->getHost(),'git$,;hub.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someacc$,;ount/somerepo/somerepo.git');
		$this->assertNull($uri->getQuery());
		$this->assertEquals($uri->getFragment(),'frag:A!\'*+()arment');
	}

	/**
	 * @depends test_init
	 */
	public function test_path()
	{
		$uri = new ILIAS\Data\URI(self::URI_PATH);
		$this->assertEquals($uri->getSchema(),'git');
		$this->assertEquals($uri->getAuthority(),'git$,;hub.com:8080');
		$this->assertEquals($uri->getHost(),'git$,;hub.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someacc$,;ount/somerepo/somerepo.git');
		$this->assertNull($uri->getQuery());
		$this->assertNull($uri->getFragment());
		$this->assertEquals($uri->getBaseURI(),'git://git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git');
	}

	/**
	 * @depends test_init
	 */
	public function test_authority_only()
	{
		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_ONLY);
		$this->assertEquals($uri->getSchema(),'git');
		$this->assertEquals($uri->getAuthority(),'git$,;hub.com');
		$this->assertEquals($uri->getHost(),'git$,;hub.com');
		$this->assertNull($uri->getPort());
		$this->assertNull($uri->getPath());
		$this->assertNull($uri->getQuery());
		$this->assertNull($uri->getFragment());
		$this->assertEquals($uri->getBaseURI(),'git://git$,;hub.com');
	}

	/**
	 * @depends test_init
	 */
	public function test_no_schema()
	{
		$this->expectException(\TypeError::class);
		new ILIAS\Data\URI(self::URI_NO_SCHEMA);
	}

	/**
	 * @depends test_init
	 */
	public function test_no_authority()
	{
		$this->expectException(\TypeError::class);
		new ILIAS\Data\URI(self::URI_NO_AUTHORITY);
	}

	/**
	 * @depends test_init
	 */
	public function test_wrong_char_in_schema()
	{
		$this->expectException(\TypeError::class);
		new ILIAS\Data\URI(self::URI_WRONG_SCHEMA);
	}

	/**
	 * @depends test_init
	 */
	public function test_wrong_authority_in_schema_1()
	{
		$this->expectException(\InvalidArgumentException::class);
		new ILIAS\Data\URI(self::URI_WRONG_AUTHORITY_1);
	}

	/**
	 * @depends test_init
	 */
	public function test_wrong_authority_in_schema_2()
	{
		$this->expectException(\InvalidArgumentException::class);
		new ILIAS\Data\URI(self::URI_WRONG_AUTHORITY_2);
	}

	/**
	 * @depends test_init
	 */
	public function test_uri_invalid()
	{
		$this->expectException(\InvalidArgumentException::class);
		new ILIAS\Data\URI(self::URI_INVALID);
	}

	/**
	 * @depends test_init
	 */
	public function test_fakepcenc()
	{
		$this->expectException(\InvalidArgumentException::class);
		new ILIAS\Data\URI(self::URI_FAKEPCENC);
	}

	/**
	 * @depends test_init
	 */
	public function test_alphadigit_start_host()
	{
		$this->expectException(\InvalidArgumentException::class);
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_1);
	}

	/**
	 * @depends test_init
	 */
	public function test_alphadigit_start_host_2()
	{
		$this->expectException(\InvalidArgumentException::class);
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_2);
	}

	/**
	 * @depends test_init
	 */
	public function test_alphadigit_start_host_3()
	{
		$this->expectException(\InvalidArgumentException::class);
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_3);
	}

	/**
	 * @depends test_init
	 */
	public function test_alphadigit_start_host_4()
	{
		$this->expectException(\InvalidArgumentException::class);
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_4);
	}

	/**
	 * @depends test_init
	 */
	public function test_alphadigit_start_host_5()
	{
		$this->expectException(\InvalidArgumentException::class);
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_5);
	}

	/**
	 * @depends test_init
	 */
	public function test_with_schema($uri)
	{
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withSchema('http');
		$this->assertEquals($uri->getSchema(),'http');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
	}

	/**
	 * @depends test_with_schema
	 */
	public function test_with_schema_invalid_1()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withSchema('');
	}

	/**
	 * @depends test_with_schema
	 */
	public function test_with_schema_invalid_2()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withSchema('1aa');
	}

	/**
	 * @depends test_init
	 */
	public function test_with_port($uri)
	{
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withPort(80);
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:80');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'80');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withPort();
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertNull($uri->getPort());
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
	}


	/**
	 * @depends test_with_port
	 */
	public function test_with_port_invalid_1()
	{
		$this->expectException(\TypeError::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withPort('a111');
	}

	/**
	 * @depends test_with_port
	 */
	public function test_with_port_invalid_2()
	{
		$this->expectException(\TypeError::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withPort('foo');
	}

	/**
	 * @depends test_init
	 */
	public function test_with_host($uri)
	{
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withHost('ilias.de');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'ilias.de:8080');
		$this->assertEquals($uri->getHost(),'ilias.de');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
	}


	/**
	 * @depends test_with_host
	 */
	public function test_with_host_invalid_1()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withHost('-foo-.de');
	}

	/**
	 * @depends test_with_host
	 */
	public function test_with_host_invalid_3()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withHost('');
	}

	/**
	 * @depends test_with_host
	 */
	public function test_with_host_invalid_4()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withHost('ilias.de"><script');
	}

	/**
	 * @depends test_init
	 */
	public function test_with_authority($uri)
	{
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withAuthority('www1.ilias.de');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'www1.ilias.de');
		$this->assertEquals($uri->getHost(),'www1.ilias.de');
		$this->assertNull($uri->getPort());
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withAuthority('ilias.de:80');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'ilias.de:80');
		$this->assertEquals($uri->getHost(),'ilias.de');
		$this->assertEquals($uri->getPort(),'80');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withAuthority('a:1');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'a:1');
		$this->assertEquals($uri->getHost(),'a');
		$this->assertEquals($uri->getPort(),1);
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withAuthority('a');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'a');
		$this->assertEquals($uri->getHost(),'a');
		$this->assertNull($uri->getPort());
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withAuthority('1.2.3.4');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'1.2.3.4');
		$this->assertEquals($uri->getHost(),'1.2.3.4');
		$this->assertNull($uri->getPort());
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withAuthority('1.2.3.4:5');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'1.2.3.4:5');
		$this->assertEquals($uri->getHost(),'1.2.3.4');
		$this->assertEquals($uri->getPort(),5);
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withAuthority('localhost1');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'localhost1');
		$this->assertEquals($uri->getHost(),'localhost1');
		$this->assertNull($uri->getPort());
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withAuthority('localhost1:10');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'localhost1:10');
		$this->assertEquals($uri->getHost(),'localhost1');
		$this->assertEquals($uri->getPort(),10);
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
	}

	/**
	 * @depends test_with_authority
	 */
	public function test_with_authority_invalid_1()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withAuthority('-foo-.de');
	}

	/**
	 * @depends test_with_authority
	 */
	public function test_with_authority_invalid_2()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withAuthority('-bar-.de:6060');
	}


	/**
	 * @depends test_with_authority
	 */
	public function test_with_authority_invalid_3()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withHost('ilias.de:');
	}

	/**
	 * @depends test_with_authority
	 */
	public function test_with_authority_invalid_4()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withHost('ilias.de: ');
	}

	/**
	 * @depends test_with_authority
	 */
	public function test_with_authority_invalid_5()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withHost('ilias.de:aaa');
	}

	/**
	 * @depends test_with_authority
	 */
	public function test_with_authority_invalid_6()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withAuthority('foo.de&<script>');
	}


	/**
	 * @depends test_with_authority
	 */
	public function test_with_authority_invalid_7()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withAuthority('foo.de"><script>alert');
	}


	/**
	 * @depends test_with_authority
	 */
	public function test_with_authority_invalid_8()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withAuthority('   :80');
	}

	/**
	 * @depends test_init
	 */
	public function test_with_path($uri)
	{
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withPath('a/b');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'a/b');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withPath();
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertNull($uri->getPath());
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
	}

	/**
	 * @depends test_with_path
	 */
	public function test_with_path_invalid_1()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withPath('/<script>/a');
	}

	/**
	 * @depends test_with_path
	 */
	public function test_with_path_invalid_2()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withPath('//a/b');
	}

	/**
	 * @depends test_with_path
	 */
	public function test_with_path_invalid_3()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withPath(':a/b');
	}

	/**
	 * @depends test_init
	 */
	public function test_with_query($uri)
	{
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withQuery('query_par_a1=val_a1');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_a1=val_a1');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withQuery();
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertNull($uri->getQuery());
		$this->assertEquals($uri->getFragment(),'fragment');
	}

	/**
	 * @depends test_with_query
	 */
	public function test_with_query_invalid_1()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withQuery('<script>a');
	}

	/**
	 * @depends test_with_query
	 */
	public function test_with_query_invalid_2()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withQuery('aa[]');
	}

	/**
	 * @depends test_init
	 */
	public function test_with_fragment($uri)
	{
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'fragment');
		$uri = $uri->withFragment('someFragment');
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->getFragment(),'someFragment');
		$uri = $uri->withFragment();
		$this->assertEquals($uri->getSchema(),'g+it');
		$this->assertEquals($uri->getAuthority(),'github.com:8080');
		$this->assertEquals($uri->getHost(),'github.com');
		$this->assertEquals($uri->getPort(),'8080');
		$this->assertEquals($uri->getPath(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->getQuery(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertNull($uri->getFragment());
	}

	/**
	 * @depends test_with_fragment
	 */
	public function test_with_fragment_invalid_1()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withFragment('aaa[]');
	}

	/**
	 * @depends test_with_fragment
	 */
	public function test_with_fragment_invalid_2()
	{
		$this->expectException(\InvalidArgumentException::class);
		$uri = new ILIAS\Data\URI(self::URI_COMPLETE);
		$uri->withFragment('script>');
	}
}
