<?php
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;

class URITest extends PHPUnit_Framework_TestCase
{
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
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), '10.0.0.86:8080');
        $this->assertEquals($uri->host(), '10.0.0.86');
        $this->assertEquals($uri->port(), 8080);
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
    }


    /**
     * @depends test_init
     */
    public function test_localhost()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE_LOCALHOST);
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'localhost:8080');
        $this->assertEquals($uri->host(), 'localhost');
        $this->assertEquals($uri->port(), 8080);
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
    }


    /**
     * @depends test_init
     */
    public function test_components($uri)
    {
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
    }

    /**
     * @depends test_init
     */
    public function test_base_uri($uri)
    {
        $this->assertEquals($uri->baseURI(), 'g+it://github.com:8080/someaccount/somerepo/somerepo.git');
    }

    /**
     * @depends test_init
     */
    public function test_base_uri_idempotent($uri)
    {
        $base_uri = $uri->baseURI();
        $this->assertEquals($base_uri, 'g+it://github.com:8080/someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');

        $uri = new ILIAS\Data\URI($base_uri);
        $this->assertEquals($base_uri, $uri->baseURI());
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertNull($uri->query());
        $this->assertNull($uri->fragment());
    }


    /**
     * @depends test_init
     */
    public function test_no_path()
    {
        $uri = new ILIAS\Data\URI(self::URI_NO_PATH_1);
        $this->assertEquals($uri->schema(), 'g-it');
        $this->assertEquals($uri->authority(), 'ilias%2Da.de:8080');
        $this->assertEquals($uri->host(), 'ilias%2Da.de');
        $this->assertEquals($uri->port(), '8080');
        $this->assertNull($uri->path());
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');

        $uri = new ILIAS\Data\URI(self::URI_NO_PATH_2);
        $this->assertEquals($uri->schema(), 'g.it');
        $this->assertEquals($uri->authority(), 'amaz;on.co.uk:8080');
        $this->assertEquals($uri->host(), 'amaz;on.co.uk');
        $this->assertEquals($uri->port(), '8080');
        $this->assertNull($uri->path());
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
    }

    /**
     * @depends test_init
     */
    public function test_no_query()
    {
        $uri = new ILIAS\Data\URI(self::URI_NO_QUERY_1);
        $this->assertEquals($uri->schema(), 'git');
        $this->assertEquals($uri->authority(), 'one-letter-top-level.a:8080');
        $this->assertEquals($uri->host(), 'one-letter-top-level.a');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertNull($uri->query());
        $this->assertEquals($uri->fragment(), 'fragment');

        $uri = new ILIAS\Data\URI(self::URI_NO_QUERY_2);
        $this->assertEquals($uri->schema(), 'git');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertNull($uri->query());
        $this->assertEquals($uri->fragment(), 'fragment');
    }

    /**
     * @depends test_init
     */
    public function test_authority_and_query()
    {
        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_QUERY_1);
        $this->assertEquals($uri->schema(), 'git');
        $this->assertEquals($uri->authority(), 'github.com');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertNull($uri->port());
        $this->assertNull($uri->path());
        $this->assertEquals($uri->query(), 'query_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2');
        $this->assertNull($uri->fragment());

        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_QUERY_2);
        $this->assertEquals($uri->schema(), 'git');
        $this->assertEquals($uri->authority(), 'github.com');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertNull($uri->port());
        $this->assertNull($uri->path());
        $this->assertEquals($uri->query(), 'qu/ery_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2');
        $this->assertNull($uri->fragment());
    }

    /**
     * @depends test_init
     */
    public function test_authority_and_fragment()
    {
        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_FRAGMENT);
        $this->assertEquals($uri->schema(), 'git');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertNull($uri->path());
        $this->assertNull($uri->query());
        $this->assertEquals($uri->fragment(), 'fragment$,;:A!\'*+()ar_1=val_1&');
    }
    /**
     * @depends test_init
     */
    public function test_authority_path_fragment()
    {
        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_PATH_FRAGMENT);
        $this->assertEquals($uri->schema(), 'git');
        $this->assertEquals($uri->authority(), 'git$,;hub.com:8080');
        $this->assertEquals($uri->host(), 'git$,;hub.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someacc$,;ount/somerepo/somerepo.git');
        $this->assertNull($uri->query());
        $this->assertEquals($uri->fragment(), 'frag:A!\'*+()arment');
    }

    /**
     * @depends test_init
     */
    public function test_path()
    {
        $uri = new ILIAS\Data\URI(self::URI_PATH);
        $this->assertEquals($uri->schema(), 'git');
        $this->assertEquals($uri->authority(), 'git$,;hub.com:8080');
        $this->assertEquals($uri->host(), 'git$,;hub.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someacc$,;ount/somerepo/somerepo.git');
        $this->assertNull($uri->query());
        $this->assertNull($uri->fragment());
        $this->assertEquals($uri->baseURI(), 'git://git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git');
    }

    /**
     * @depends test_init
     */
    public function test_authority_only()
    {
        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_ONLY);
        $this->assertEquals($uri->schema(), 'git');
        $this->assertEquals($uri->authority(), 'git$,;hub.com');
        $this->assertEquals($uri->host(), 'git$,;hub.com');
        $this->assertNull($uri->port());
        $this->assertNull($uri->path());
        $this->assertNull($uri->query());
        $this->assertNull($uri->fragment());
        $this->assertEquals($uri->baseURI(), 'git://git$,;hub.com');
    }

    /**
     * @depends test_init
     * @expectedException \TypeError
     */
    public function test_no_schema()
    {
        new ILIAS\Data\URI(self::URI_NO_SCHEMA);
    }

    /**
     * @depends test_init
     * @expectedException \TypeError
     */
    public function test_no_authority()
    {
        new ILIAS\Data\URI(self::URI_NO_AUTHORITY);
    }

    /**
     * @depends test_init
     * @expectedException \TypeError
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

    /**
     * @depends test_init
     */
    public function test_with_schema($uri)
    {
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withSchema('http');
        $this->assertEquals($uri->schema(), 'http');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
    }

    /**
     * @depends test_with_schema
     * @expectedException \InvalidArgumentException
     */
    public function test_with_schema_invalid_1()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withSchema('');
    }

    /**
     * @depends test_with_schema
     * @expectedException \InvalidArgumentException
     */
    public function test_with_schema_invalid_2()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withSchema('1aa');
    }

    /**
     * @depends test_init
     */
    public function test_with_port($uri)
    {
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withPort(80);
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:80');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '80');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withPort();
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertNull($uri->port());
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
    }


    /**
     * @depends test_with_port
     * @expectedException \TypeError
     */
    public function test_with_port_invalid_1()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPort('a111');
    }

    /**
     * @depends test_with_port
     * @expectedException \TypeError
     */
    public function test_with_port_invalid_2()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPort('foo');
    }

    /**
     * @depends test_init
     */
    public function test_with_host($uri)
    {
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withHost('ilias.de');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'ilias.de:8080');
        $this->assertEquals($uri->host(), 'ilias.de');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
    }


    /**
     * @depends test_with_host
     * @expectedException \InvalidArgumentException
     */
    public function test_with_host_invalid_1()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('-foo-.de');
    }

    /**
     * @depends test_with_host
     * @expectedException \TypeError
     */
    public function test_with_host_invalid_2()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost(null);
    }


    /**
     * @depends test_with_host
     * @expectedException \InvalidArgumentException
     */
    public function test_with_host_invalid_3()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('');
    }

    /**
     * @depends test_with_host
     * @expectedException \InvalidArgumentException
     */
    public function test_with_host_invalid_4()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('ilias.de"><script');
    }

    /**
     * @depends test_init
     */
    public function test_with_authority($uri)
    {
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withAuthority('www1.ilias.de');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'www1.ilias.de');
        $this->assertEquals($uri->host(), 'www1.ilias.de');
        $this->assertNull($uri->port());
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withAuthority('ilias.de:80');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'ilias.de:80');
        $this->assertEquals($uri->host(), 'ilias.de');
        $this->assertEquals($uri->port(), '80');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withAuthority('a:1');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'a:1');
        $this->assertEquals($uri->host(), 'a');
        $this->assertEquals($uri->port(), 1);
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withAuthority('a');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'a');
        $this->assertEquals($uri->host(), 'a');
        $this->assertNull($uri->port());
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withAuthority('1.2.3.4');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), '1.2.3.4');
        $this->assertEquals($uri->host(), '1.2.3.4');
        $this->assertNull($uri->port());
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withAuthority('1.2.3.4:5');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), '1.2.3.4:5');
        $this->assertEquals($uri->host(), '1.2.3.4');
        $this->assertEquals($uri->port(), 5);
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withAuthority('localhost1');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'localhost1');
        $this->assertEquals($uri->host(), 'localhost1');
        $this->assertNull($uri->port());
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withAuthority('localhost1:10');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'localhost1:10');
        $this->assertEquals($uri->host(), 'localhost1');
        $this->assertEquals($uri->port(), 10);
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
    }

    /**
     * @depends test_with_authority
     * @expectedException \InvalidArgumentException
     */
    public function test_with_authority_invalid_1()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withAuthority('-foo-.de');
    }

    /**
     * @depends test_with_authority
     * @expectedException \TypeError
     */
    public function test_with_authority_invalid_2()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost(null);
    }


    /**
     * @depends test_with_authority
     * @expectedException \InvalidArgumentException
     */
    public function test_with_authority_invalid_3()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('ilias.de:');
    }

    /**
     * @depends test_with_authority
     * @expectedException \InvalidArgumentException
     */
    public function test_with_authority_invalid_4()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('ilias.de: ');
    }

    /**
     * @depends test_with_authority
     * @expectedException \InvalidArgumentException
     */
    public function test_with_authority_invalid_5()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('ilias.de:aaa');
    }

    /**
     * @depends test_with_authority
     * @expectedException \InvalidArgumentException
     */
    public function test_with_authority_invalid_6()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withAuthority('foo.de&<script>');
    }


    /**
     * @depends test_with_authority
     * @expectedException \InvalidArgumentException
     */
    public function test_with_authority_invalid_7()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withAuthority('foo.de"><script>alert');
    }


    /**
     * @depends test_with_authority
     * @expectedException \InvalidArgumentException
     */
    public function test_with_authority_invalid_8()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withAuthority('   :80');
    }

    /**
     * @depends test_init
     */
    public function test_with_path($uri)
    {
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withPath('a/b');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'a/b');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withPath();
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertNull($uri->path());
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
    }

    /**
     * @depends test_with_path
     * @expectedException \InvalidArgumentException
     */
    public function test_with_path_invalid_1()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPath('/<script>/a');
    }

    /**
     * @depends test_with_path
     * @expectedException \InvalidArgumentException
     */
    public function test_with_path_invalid_2()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPath('//a/b');
    }

    /**
     * @depends test_with_path
     * @expectedException \InvalidArgumentException
     */
    public function test_with_path_invalid_3()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPath(':a/b');
    }

    /**
     * @depends test_init
     */
    public function test_with_query($uri)
    {
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withQuery('query_par_a1=val_a1');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_a1=val_a1');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withQuery();
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertNull($uri->query());
        $this->assertEquals($uri->fragment(), 'fragment');
    }

    /**
     * @depends test_with_query
     * @expectedException \InvalidArgumentException
     */
    public function test_with_query_invalid_1()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withQuery('<script>a');
    }

    /**
     * @depends test_with_query
     * @expectedException \InvalidArgumentException
     */
    public function test_with_query_invalid_2()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withQuery('aa[]');
    }

    /**
     * @depends test_init
     */
    public function test_with_fragment($uri)
    {
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'fragment');
        $uri = $uri->withFragment('someFragment');
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertEquals($uri->fragment(), 'someFragment');
        $uri = $uri->withFragment();
        $this->assertEquals($uri->schema(), 'g+it');
        $this->assertEquals($uri->authority(), 'github.com:8080');
        $this->assertEquals($uri->host(), 'github.com');
        $this->assertEquals($uri->port(), '8080');
        $this->assertEquals($uri->path(), 'someaccount/somerepo/somerepo.git');
        $this->assertEquals($uri->query(), 'query_par_1=val_1&query_par_2=val_2');
        $this->assertNull($uri->fragment());
    }

    /**
     * @depends test_with_fragment
     * @expectedException \InvalidArgumentException
     */
    public function test_with_fragment_invalid_1()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withFragment('aaa[]');
    }

    /**
     * @depends test_with_fragment
     * @expectedException \InvalidArgumentException
     */
    public function test_with_fragment_invalid_2()
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withFragment('script>');
    }
}
