<?php
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;

class URITest extends PHPUnit_Framework_TestCase {

	const URI_COMPLETE = 'g+it://github.com:8080/someaccount/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';

	const URI_NO_PATH_1 = 'g-it://githu%2Db.com:8080?query_par_1=val_1&query_par_2=val_2#fragment';
	const URI_NO_PATH_2 = 'g.it://githu%1Fb.com:8080/?query_par_1=val_1&query_par_2=val_2#fragment';

	const URI_NO_QUERY_1 = 'git://github.com:8080/someaccount/somerepo/somerepo.git/#fragment';
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


	public function test_init()
	{
		return new ILIAS\Data\URI(self::URI_COMPLETE);
	}

	/**
	 * @depends test_init
	 */
	public function test_components($uri)
	{
		$this->assertEquals($uri->schema(),'g+it');
		$this->assertEquals($uri->authority(),'github.com:8080');
		$this->assertEquals($uri->host(),'github.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertEquals($uri->path(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->query(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->fragment(),'fragment');
	}

	/**
	 * @depends test_init
	 */
	public function test_base_uri($uri)
	{
		$this->assertEquals($uri->baseURI(),'g+it://github.com:8080/someaccount/somerepo/somerepo.git');
	}

	/**
	 * @depends test_init
	 */
	public function test_base_uri_idempotent($uri)
	{
		$base_uri = $uri->baseURI();
		$this->assertEquals($base_uri,'g+it://github.com:8080/someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->schema(),'g+it');
		$this->assertEquals($uri->authority(),'github.com:8080');
		$this->assertEquals($uri->host(),'github.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertEquals($uri->path(),'someaccount/somerepo/somerepo.git');
		$this->assertEquals($uri->query(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->fragment(),'fragment');

		$uri = new ILIAS\Data\URI($base_uri);
		$this->assertEquals($base_uri,$uri->baseURI());
		$this->assertEquals($uri->schema(),'g+it');
		$this->assertEquals($uri->authority(),'github.com:8080');
		$this->assertEquals($uri->host(),'github.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertEquals($uri->path(),'someaccount/somerepo/somerepo.git');
		$this->assertNull($uri->query());
		$this->assertNull($uri->fragment());
	}


	/**
	 * @depends test_init
	 */
	public function test_no_path()
	{
		$uri = new ILIAS\Data\URI(self::URI_NO_PATH_1);
		$this->assertEquals($uri->schema(),'g-it');
		$this->assertEquals($uri->authority(),'githu%2Db.com:8080');
		$this->assertEquals($uri->host(),'githu%2Db.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertNull($uri->path());
		$this->assertEquals($uri->query(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->fragment(),'fragment');

		$uri = new ILIAS\Data\URI(self::URI_NO_PATH_2);
		$this->assertEquals($uri->schema(),'g.it');
		$this->assertEquals($uri->authority(),'githu%1Fb.com:8080');
		$this->assertEquals($uri->host(),'githu%1Fb.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertNull($uri->path());
		$this->assertEquals($uri->query(),'query_par_1=val_1&query_par_2=val_2');
		$this->assertEquals($uri->fragment(),'fragment');
	}

	/**
	 * @depends test_init
	 */
	public function test_no_query()
	{
		$uri = new ILIAS\Data\URI(self::URI_NO_QUERY_1);
		$this->assertEquals($uri->schema(),'git');
		$this->assertEquals($uri->authority(),'github.com:8080');
		$this->assertEquals($uri->host(),'github.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertEquals($uri->path(),'someaccount/somerepo/somerepo.git');
		$this->assertNull($uri->query());
		$this->assertEquals($uri->fragment(),'fragment');

		$uri = new ILIAS\Data\URI(self::URI_NO_QUERY_2);
		$this->assertEquals($uri->schema(),'git');
		$this->assertEquals($uri->authority(),'github.com:8080');
		$this->assertEquals($uri->host(),'github.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertEquals($uri->path(),'someaccount/somerepo/somerepo.git');
		$this->assertNull($uri->query());
		$this->assertEquals($uri->fragment(),'fragment');
	}

	/**
	 * @depends test_init
	 */
	public function test_authority_and_query()
	{
		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_QUERY_1);
		$this->assertEquals($uri->schema(),'git');
		$this->assertEquals($uri->authority(),'github.com');
		$this->assertEquals($uri->host(),'github.com');
		$this->assertNull($uri->port());
		$this->assertNull($uri->path());
		$this->assertEquals($uri->query(),'query_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2');
		$this->assertNull($uri->fragment());

		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_QUERY_2);
		$this->assertEquals($uri->schema(),'git');
		$this->assertEquals($uri->authority(),'github.com');
		$this->assertEquals($uri->host(),'github.com');
		$this->assertNull($uri->port());
		$this->assertNull($uri->path());
		$this->assertEquals($uri->query(),'qu/ery_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2');
		$this->assertNull($uri->fragment());
	}

	/**
	 * @depends test_init
	 */
	public function test_authority_and_fragment()
	{
		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_FRAGMENT);
		$this->assertEquals($uri->schema(),'git');
		$this->assertEquals($uri->authority(),'github.com:8080');
		$this->assertEquals($uri->host(),'github.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertNull($uri->path());
		$this->assertNull($uri->query());
		$this->assertEquals($uri->fragment(),'fragment$,;:A!\'*+()ar_1=val_1&');
	}
	/**
	 * @depends test_init
	 */
	public function test_authority_path_fragment()
	{
		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_PATH_FRAGMENT);
		$this->assertEquals($uri->schema(),'git');
		$this->assertEquals($uri->authority(),'git$,;hub.com:8080');
		$this->assertEquals($uri->host(),'git$,;hub.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertEquals($uri->path(),'someacc$,;ount/somerepo/somerepo.git');
		$this->assertNull($uri->query());
		$this->assertEquals($uri->fragment(),'frag:A!\'*+()arment');
	}

	/**
	 * @depends test_init
	 */
	public function test_path()
	{
		$uri = new ILIAS\Data\URI(self::URI_PATH);
		$this->assertEquals($uri->schema(),'git');
		$this->assertEquals($uri->authority(),'git$,;hub.com:8080');
		$this->assertEquals($uri->host(),'git$,;hub.com');
		$this->assertEquals($uri->port(),'8080');
		$this->assertEquals($uri->path(),'someacc$,;ount/somerepo/somerepo.git');
		$this->assertNull($uri->query());
		$this->assertNull($uri->fragment());
		$this->assertEquals($uri->baseURI(),'git://git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git');
	}

	/**
	 * @depends test_init
	 */
	public function test_authority_only()
	{
		$uri = new ILIAS\Data\URI(self::URI_AUTHORITY_ONLY);
		$this->assertEquals($uri->schema(),'git');
		$this->assertEquals($uri->authority(),'git$,;hub.com');
		$this->assertEquals($uri->host(),'git$,;hub.com');
		$this->assertNull($uri->port());
		$this->assertNull($uri->path());
		$this->assertNull($uri->query());
		$this->assertNull($uri->fragment());
		$this->assertEquals($uri->baseURI(),'git://git$,;hub.com');
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_no_schema()
	{
		new ILIAS\Data\URI(self::URI_NO_SCHEMA);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_no_authority()
	{
		new ILIAS\Data\URI(self::URI_NO_AUTHORITY);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_wrong_char_in_schema()
	{
		new ILIAS\Data\URI(self::URI_WRONG_SCHEMA);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_wrong_authority_in_schema_1()
	{
		new ILIAS\Data\URI(self::URI_WRONG_AUTHORITY_1);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_wrong_authority_in_schema_2()
	{
		new ILIAS\Data\URI(self::URI_WRONG_AUTHORITY_2);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_uri_invalid()
	{
		new ILIAS\Data\URI(self::URI_INVALID);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_fakepcenc()
	{
		new ILIAS\Data\URI(self::URI_FAKEPCENC);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_alphadigit_start_host()
	{
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_1);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_alphadigit_start_host_2()
	{
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_2);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_alphadigit_start_host_3()
	{
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_3);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_alphadigit_start_host_4()
	{
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_4);
	}

	/**
	 * @depends test_init
	 * @expectedException \InvalidArgumentException
	 */
	public function test_alphadigit_start_host_5()
	{
		new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_5);
	}
}