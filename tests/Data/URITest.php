<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class URITest extends TestCase
{
    private const URI_COMPLETE = 'g+it://github.com:8080/someaccount/somerepo/somerepo.git?query_par_1=val_1&query_par_2=val_2#fragment';

    private const URI_COMPLETE_IPV4 = 'g+it://10.0.0.86:8080/someaccount/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';

    private const URI_COMPLETE_LOCALHOST = 'g+it://localhost:8080/someaccount/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';


    private const URI_NO_PATH_1 = 'g-it://ilias%2Da.de:8080?query_par_1=val_1&query_par_2=val_2#fragment';
    private const URI_NO_PATH_2 = 'g.it://amaz;on.co.uk:8080/?query_par_1=val_1&query_par_2=val_2#fragment';

    private const URI_NO_QUERY_1 = 'git://one-letter-top-level.a:8080/someaccount/somerepo/somerepo.git/#fragment';
    private const URI_NO_QUERY_2 = 'git://github.com:8080/someaccount/somerepo/somerepo.git#fragment';

    private const URI_AUTHORITY_AND_QUERY_1 = 'git://github.com?query_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2';
    private const URI_AUTHORITY_AND_QUERY_2 = 'git://github.com/?qu/ery_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2';

    private const URI_AUTHORITY_AND_FRAGMENT = 'git://github.com:8080/#fragment$,;:A!\'*+()ar_1=val_1&';

    private const URI_AUTHORITY_PATH_FRAGMENT = 'git://git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git#frag:A!\'*+()arment';

    private const URI_PATH = 'git://git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git/';

    private const URI_AUTHORITY_ONLY = 'git://git$,;hub.com';

    private const URI_NO_SCHEMA = 'git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git/';

    private const URI_NO_AUTHORITY = 'git://:8080/someaccount/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';

    private const URI_WRONG_SCHEMA = 'gi$t://git$,;hub.com';

    private const URI_WRONG_AUTHORITY_1 = 'git://git$,;hu<b.com:8080/someacc$,;ount/somerepo/somerepo.git/';
    private const URI_WRONG_AUTHORITY_2 = 'git://git$,;hu=b.com/someacc$,;ount/somerepo/somerepo.git/';


    private const URI_INVALID = 'https://host.de/ilias.php/"><script>alert(1)</script>?baseClass=ilObjChatroomGUI&cmd=getOSDNotifications&cmdMode=asynch&max_age=15192913';

    private const URI_FAKEPCENC = 'g+it://github.com:8080/someaccoun%t/somerepo/somerepo.git/?query_par_1=val_1&query_par_2=val_2#fragment';
   
    private const URI_REALPCTENC = 'g+it://github.com:8080/someaccount%2Fsomerepo/som%2brepo.git/?par_lower=val_%2b&par_upper=val_%C3%A1#fragment';
    private const PATH_REALPCTENC = 'someaccount%2Fsomerepo/som%2brepo.git';
    private const PARAMS_REALPCTENC = [
        'par_lower' => 'val_+',
        'par_upper' => 'val_á'
    ];

    private const URI_HOST_ALPHADIG_START_1 = 'g+it://-github.com:8080/someaccount';
    private const URI_HOST_ALPHADIG_START_2 = 'g+it://github-.com:8080/someaccount';
    private const URI_HOST_ALPHADIG_START_3 = 'http://.';
    private const URI_HOST_ALPHADIG_START_4 = 'http://../';
    private const URI_HOST_ALPHADIG_START_5 = 'http://-error-.invalid/';

    private const URI_BASE = 'git://github.com:8080/someaccount/somerepo/somerepo.git';
    private const PARAMS = [
        'par_1' => 'val_1',
        'par_2' => 'val_2'
    ];


    /**
     * @doesNotPerformAssertions
     */
    public function test_init() : \ILIAS\Data\URI
    {
        return new ILIAS\Data\URI(self::URI_COMPLETE);
    }

    /**
     * @depends test_init
     */
    public function test_ipv4() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE_IPV4);
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('10.0.0.86:8080', $uri->getAuthority());
        $this->assertEquals('10.0.0.86', $uri->getHost());
        $this->assertEquals(8080, $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }


    /**
     * @depends test_init
     */
    public function test_localhost() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE_LOCALHOST);
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('localhost:8080', $uri->getAuthority());
        $this->assertEquals('localhost', $uri->getHost());
        $this->assertEquals(8080, $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }


    /**
     * @depends test_init
     */
    public function test_components($uri) : void
    {
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    /**
     * @depends test_init
     */
    public function test_base_uri($uri) : void
    {
        $this->assertEquals('g+it://github.com:8080/someaccount/somerepo/somerepo.git', $uri->getBaseURI());
    }

    /**
     * @depends test_init
     */
    public function test_base_uri_idempotent($uri) : void
    {
        $base_uri = $uri->getBaseURI();
        $this->assertEquals('g+it://github.com:8080/someaccount/somerepo/somerepo.git', $base_uri);
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());

        $uri = new ILIAS\Data\URI($base_uri);
        $this->assertEquals($base_uri, $uri->getBaseURI());
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertNull($uri->getQuery());
        $this->assertNull($uri->getFragment());
    }


    /**
     * @depends test_init
     */
    public function test_no_path() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_NO_PATH_1);
        $this->assertEquals('g-it', $uri->getSchema());
        $this->assertEquals('ilias%2Da.de:8080', $uri->getAuthority());
        $this->assertEquals('ilias%2Da.de', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertNull($uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());

        $uri = new ILIAS\Data\URI(self::URI_NO_PATH_2);
        $this->assertEquals('g.it', $uri->getSchema());
        $this->assertEquals('amaz;on.co.uk:8080', $uri->getAuthority());
        $this->assertEquals('amaz;on.co.uk', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertNull($uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    /**
     * @depends test_init
     */
    public function test_no_query() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_NO_QUERY_1);
        $this->assertEquals('git', $uri->getSchema());
        $this->assertEquals('one-letter-top-level.a:8080', $uri->getAuthority());
        $this->assertEquals('one-letter-top-level.a', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertNull($uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());

        $uri = new ILIAS\Data\URI(self::URI_NO_QUERY_2);
        $this->assertEquals('git', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertNull($uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    /**
     * @depends test_init
     */
    public function test_authority_and_query() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_QUERY_1);
        $this->assertEquals('git', $uri->getSchema());
        $this->assertEquals('github.com', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertNull($uri->getPath());
        $this->assertEquals('query_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2', $uri->getQuery());
        $this->assertNull($uri->getFragment());

        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_QUERY_2);
        $this->assertEquals('git', $uri->getSchema());
        $this->assertEquals('github.com', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertNull($uri->getPath());
        $this->assertEquals('qu/ery_p$,;:A!\'*+()ar_1=val_1&quer?y_par_2=val_2', $uri->getQuery());
        $this->assertNull($uri->getFragment());
    }

    /**
     * @depends test_init
     */
    public function test_authority_and_fragment() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_AND_FRAGMENT);
        $this->assertEquals('git', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertNull($uri->getPath());
        $this->assertNull($uri->getQuery());
        $this->assertEquals('fragment$,;:A!\'*+()ar_1=val_1&', $uri->getFragment());
    }
    /**
     * @depends test_init
     */
    public function test_authority_path_fragment() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_PATH_FRAGMENT);
        $this->assertEquals('git', $uri->getSchema());
        $this->assertEquals('git$,;hub.com:8080', $uri->getAuthority());
        $this->assertEquals('git$,;hub.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someacc$,;ount/somerepo/somerepo.git', $uri->getPath());
        $this->assertNull($uri->getQuery());
        $this->assertEquals('frag:A!\'*+()arment', $uri->getFragment());
    }

    /**
     * @depends test_init
     */
    public function test_path() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_PATH);
        $this->assertEquals('git', $uri->getSchema());
        $this->assertEquals('git$,;hub.com:8080', $uri->getAuthority());
        $this->assertEquals('git$,;hub.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someacc$,;ount/somerepo/somerepo.git', $uri->getPath());
        $this->assertNull($uri->getQuery());
        $this->assertNull($uri->getFragment());
        $this->assertEquals('git://git$,;hub.com:8080/someacc$,;ount/somerepo/somerepo.git', $uri->getBaseURI());
    }

    /**
     * @depends test_init
     */
    public function test_authority_only() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_AUTHORITY_ONLY);
        $this->assertEquals('git', $uri->getSchema());
        $this->assertEquals('git$,;hub.com', $uri->getAuthority());
        $this->assertEquals('git$,;hub.com', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertNull($uri->getPath());
        $this->assertNull($uri->getQuery());
        $this->assertNull($uri->getFragment());
        $this->assertEquals('git://git$,;hub.com', $uri->getBaseURI());
    }

    /**
     * @depends test_init
     */
    public function test_no_schema() : void
    {
        $this->expectException(TypeError::class);
        new ILIAS\Data\URI(self::URI_NO_SCHEMA);
    }

    /**
     * @depends test_init
     */
    public function test_no_authority() : void
    {
        $this->expectException(TypeError::class);
        new ILIAS\Data\URI(self::URI_NO_AUTHORITY);
    }

    /**
     * @depends test_init
     */
    public function test_wrong_char_in_schema() : void
    {
        $this->expectException(TypeError::class);
        new ILIAS\Data\URI(self::URI_WRONG_SCHEMA);
    }

    /**
     * @depends test_init
     */
    public function test_wrong_authority_in_schema_1() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ILIAS\Data\URI(self::URI_WRONG_AUTHORITY_1);
    }

    /**
     * @depends test_init
     */
    public function test_wrong_authority_in_schema_2() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ILIAS\Data\URI(self::URI_WRONG_AUTHORITY_2);
    }

    /**
     * @depends test_init
     */
    public function test_uri_invalid() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ILIAS\Data\URI(self::URI_INVALID);
    }

    /**
     * @depends test_init
     */
    public function test_realpctenc() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_REALPCTENC);
        $this->assertEquals(self::PATH_REALPCTENC, $uri->getPath());
        $this->assertEquals(self::PARAMS_REALPCTENC, $uri->getParameters());
    }

    /**
     * @depends test_init
     */
    public function test_fakepcenc() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ILIAS\Data\URI(self::URI_FAKEPCENC);
    }

    /**
     * @depends test_init
     */
    public function test_alphadigit_start_host() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_1);
    }

    /**
     * @depends test_init
     */
    public function test_alphadigit_start_host_2() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_2);
    }

    /**
     * @depends test_init
     */
    public function test_alphadigit_start_host_3() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_3);
    }

    /**
     * @depends test_init
     */
    public function test_alphadigit_start_host_4() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_4);
    }

    /**
     * @depends test_init
     */
    public function test_alphadigit_start_host_5() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ILIAS\Data\URI(self::URI_HOST_ALPHADIG_START_5);
    }

    /**
     * @depends test_init
     */
    public function test_with_schema($uri) : void
    {
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withSchema('http');
        $this->assertEquals('http', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    /**
     * @depends test_with_schema
     */
    public function test_with_schema_invalid_1() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withSchema('');
    }

    /**
     * @depends test_with_schema
     */
    public function test_with_schema_invalid_2() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withSchema('1aa');
    }

    /**
     * @depends test_init
     */
    public function test_with_port($uri) : void
    {
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withPort(80);
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:80', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('80', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withPort();
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }


    /**
     * @depends test_with_port
     */
    public function test_with_port_invalid_1() : void
    {
        $this->expectException(TypeError::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPort('a111');
    }

    /**
     * @depends test_with_port
     */
    public function test_with_port_invalid_2() : void
    {
        $this->expectException(TypeError::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPort('foo');
    }

    /**
     * @depends test_init
     */
    public function test_with_host($uri) : void
    {
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withHost('ilias.de');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('ilias.de:8080', $uri->getAuthority());
        $this->assertEquals('ilias.de', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }


    /**
     * @depends test_with_host
     */
    public function test_with_host_invalid_1() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('-foo-.de');
    }

    /**
     * @depends test_with_host
     */
    public function test_with_host_invalid_3() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('');
    }

    /**
     * @depends test_with_host
     */
    public function test_with_host_invalid_4() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('ilias.de"><script');
    }

    /**
     * @depends test_init
     */
    public function test_with_authority($uri) : void
    {
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withAuthority('www1.ilias.de');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('www1.ilias.de', $uri->getAuthority());
        $this->assertEquals('www1.ilias.de', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withAuthority('ilias.de:80');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('ilias.de:80', $uri->getAuthority());
        $this->assertEquals('ilias.de', $uri->getHost());
        $this->assertEquals('80', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withAuthority('a:1');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('a:1', $uri->getAuthority());
        $this->assertEquals('a', $uri->getHost());
        $this->assertEquals(1, $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withAuthority('a');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('a', $uri->getAuthority());
        $this->assertEquals('a', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withAuthority('1.2.3.4');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('1.2.3.4', $uri->getAuthority());
        $this->assertEquals('1.2.3.4', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withAuthority('1.2.3.4:5');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('1.2.3.4:5', $uri->getAuthority());
        $this->assertEquals('1.2.3.4', $uri->getHost());
        $this->assertEquals(5, $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withAuthority('localhost1');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('localhost1', $uri->getAuthority());
        $this->assertEquals('localhost1', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withAuthority('localhost1:10');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('localhost1:10', $uri->getAuthority());
        $this->assertEquals('localhost1', $uri->getHost());
        $this->assertEquals(10, $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    /**
     * @depends test_with_authority
     */
    public function test_with_authority_invalid_1() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withAuthority('-foo-.de');
    }

    /**
     * @depends test_with_authority
     */
    public function test_with_authority_invalid_2() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withAuthority('-bar-.de:6060');
    }


    /**
     * @depends test_with_authority
     */
    public function test_with_authority_invalid_3() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('ilias.de:');
    }

    /**
     * @depends test_with_authority
     */
    public function test_with_authority_invalid_4() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('ilias.de: ');
    }

    /**
     * @depends test_with_authority
     */
    public function test_with_authority_invalid_5() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withHost('ilias.de:aaa');
    }

    /**
     * @depends test_with_authority
     */
    public function test_with_authority_invalid_6() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withAuthority('foo.de&<script>');
    }


    /**
     * @depends test_with_authority
     */
    public function test_with_authority_invalid_7() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withAuthority('foo.de"><script>alert');
    }


    /**
     * @depends test_with_authority
     */
    public function test_with_authority_invalid_8() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withAuthority('   :80');
    }

    /**
     * @depends test_init
     */
    public function test_with_path($uri) : void
    {
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withPath('a/b');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('a/b', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withPath();
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertNull($uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    /**
     * @depends test_with_path
     */
    public function test_with_path_invalid_1() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPath('/<script>/a');
    }

    /**
     * @depends test_with_path
     */
    public function test_with_path_invalid_2() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPath('//a/b');
    }

    /**
     * @depends test_with_path
     */
    public function test_with_path_invalid_3() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withPath(':a/b');
    }

    /**
     * @depends test_init
     */
    public function test_with_query($uri) : void
    {
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withQuery('query_par_a1=val_a1');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_a1=val_a1', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withQuery();
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertNull($uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    /**
     * @depends test_with_query
     */
    public function test_with_query_invalid_1() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withQuery('<script>a');
    }

    /**
     * @depends test_with_query
     */
    public function test_with_query_invalid_2() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withQuery('aa[]');
    }

    /**
     * @depends test_init
     */
    public function test_with_fragment($uri) : void
    {
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $uri = $uri->withFragment('someFragment');
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertEquals('someFragment', $uri->getFragment());
        $uri = $uri->withFragment();
        $this->assertEquals('g+it', $uri->getSchema());
        $this->assertEquals('github.com:8080', $uri->getAuthority());
        $this->assertEquals('github.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('someaccount/somerepo/somerepo.git', $uri->getPath());
        $this->assertEquals('query_par_1=val_1&query_par_2=val_2', $uri->getQuery());
        $this->assertNull($uri->getFragment());
    }

    /**
     * @depends test_with_fragment
     */
    public function test_with_fragment_invalid_1() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withFragment('aaa[]');
    }

    /**
     * @depends test_with_fragment
     */
    public function test_with_fragment_invalid_2() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $uri->withFragment('script>');
    }

    public function testToString() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_COMPLETE);
        $this->assertEquals(
            self::URI_COMPLETE,
            $uri
        );
    }

    public function testGetParameters() : ILIAS\Data\URI
    {
        $url = self::URI_BASE . '?' . http_build_query(self::PARAMS);
        $uri = new ILIAS\Data\URI($url);
        $this->assertEquals(
            self::PARAMS,
            $uri->getParameters()
        );
        return $uri;
    }

    /**
     * @depends testGetParameters
     */
    public function testGetParameter(ILIAS\Data\URI $uri) : void
    {
        $k = array_keys(self::PARAMS)[0];
        $this->assertEquals(
            self::PARAMS[$k],
            $uri->getParameter($k)
        );
    }

    /**
     * @depends testGetParameters
     */
    public function testWithParameters(ILIAS\Data\URI $uri) : ILIAS\Data\URI
    {
        $params = ['x' => 1, 'y' => 2];
        $uri = $uri->withParameters($params);
        $this->assertEquals(
            $params,
            $uri->getParameters()
        );
        return $uri;
    }

    /**
     * @depends testWithParameters
     */
    public function testSubstituteParameter(ILIAS\Data\URI $uri) : void
    {
        $uri = $uri->withParameter('x', 5);
        $this->assertEquals(
            5,
            $uri->getParameter('x')
        );
    }
    /**
     * @depends testWithParameters
     */
    public function testAppendParameter(ILIAS\Data\URI $uri) : void
    {
        $params = [
            'x' => 1, 'y' => 2,
            'z' => 5
        ];
        $uri = $uri->withParameter('z', 5);
        $this->assertEquals(
            $params,
            $uri->getParameters()
        );
    }

    /**
     * @depends testGetParameters
     */
    public function testWithArrayParameters(ILIAS\Data\URI $uri) : void
    {
        $params = ['x' => 1, 'y' => [10, 11, 12]];
        $uri = $uri->withParameters($params);
        $this->assertEquals(
            $params,
            $uri->getParameters()
        );
        $this->assertEquals(
            'git://github.com:8080/someaccount/somerepo/somerepo.git?x=1&y%5B0%5D=10&y%5B1%5D=11&y%5B2%5D=12',
            $uri
        );
        $this->assertEquals(
            $params['y'],
            $uri->getParameter('y')
        );
    }

    public function testWithOutParameters() : void
    {
        $uri = new ILIAS\Data\URI(self::URI_NO_QUERY_2);
        $this->assertEquals(
            [],
            $uri->getParameters()
        );

        $this->assertNull($uri->getParameter('y'));

        $this->assertEquals(
            self::URI_NO_QUERY_2,
            (string) $uri
        );
    }
}
