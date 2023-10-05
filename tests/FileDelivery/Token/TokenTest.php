<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Tests\FileDelivery\Token;

use PHPUnit\Framework\TestCase;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\Salt\Salt;
use ILIAS\FileDelivery\Token\Signer\KeyRotatingSigner;
use ILIAS\FileDelivery\Token\Signer\NullSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Signing\ConcatSigningKeyGenerator;
use ILIAS\FileDelivery\Token\Compression\GZipCompression;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
class TokenTest extends TestCase
{
    public function testSomething(): void
    {
        $data_signer = new DataSigner(
            new SecretKeyRotation(
                new SecretKey('test_key_one')
            )
        );

        $payload_data = [
            't' => 1,
            'p' => 'fsv2/63a/6a5/5a2/1cf42b2ad0bc1ee729a5965/1/data',
            'u' => 6
        ];

        $singed_data = $data_signer->sign($payload_data, 'test_salt', new \DateTimeImmutable('2099-01-01 00:00:00'));

        $this->assertIsString($singed_data);
        $this->assertSame(
            'Fck5CsMwEEDRu0xt0GySJWF8EjWjrTbESRNy98jNh8f_wg2ZNrggw3x9uLggtmK-OG-L1KZyZetYG42xczKfwrpUXLfbYIM35PA7jvNU3DFhjIiPRpjapabGlTj2JsJch8rQmJ5lUVUo0B8',
            $singed_data
        );
        $this->assertEquals(143, strlen($singed_data));

        $retrieve = $data_signer->verify($singed_data, 'test_salt');
        $this->assertSame($payload_data, $retrieve);
    }

    public function providePayloads(): array
    {
        $random = static function (int $chars): string {
            for ($i = 0, $str = ''; $i < $chars; $i++) {
                $str .= chr(random_int(33, 125));
            }
            return $str;
        };

        return [
            ['lorem ipsum'],
            ['o@3z||w^h\F(G[Z,*qjo/n8$Q_yO({,%9h4]UK&s*E$H=/8L6:#2VDFb5<Is%;=3p=2\'xb{skeOKw^Pt]wwya$6JV_e{7qbZUmcl{V3JRl<w{N=M_512x4DV=>i]=2$X+od8+#KVG+mN"9yHWW+RGb>eDZ\+RW>\%ks2yj%m)29=)cpLT8w4{rBl]Yvx%njk3?)Mrc-|`Dd4I)F?y;f2%-W/ObD?v"TWk(:pHN4?FXTeT7f@{[)N/2XQW@c<ddk6\'b&;R;bcB8@W)[l(7RCvUU1EE4>[CN1w1.U1`+LVUKZaN_v<?CUAjTXe=B-@4c\'$.kB;HjI)\03<ll29`o_0$U@KF8JQH8=pQ^j>i:+R&m\jOOuys3"Ow|out8=[vM\^an:|^NZE-za668{I\'YPwXpIONsa"+fwjn)nj%{b$2{gZoljdq4:MA?I&dz9d;K-9t%A%8,@9rLwF1tuMxx@f{NoH+J[;PVRByLz&Z9,)wNXS"wO|eD%e$0wDW\Ie*fQsSHSuU(S8W9O8RNc+VQVbJAKC+0#gYHBBFtNPDKn#XvyhsOTEi/+lAO5\HA=PvG<g{d$lP;^|A1a^BvaJH:k|U88KH<34ub`S*J92Sw>(tTt{#/*\'%dAcdm$JJ<6MP+J4.ifdi-D_<`)D1Xf(O6rxB>$HCw9IVR/lJ]7eK1q&:.z7mUi4C@W+/d-35\:GdeL4Yu9jEvbL)yd9{49FXx<iV4]_HoF(CLySJm^l3eX|||_"RV*+[\'M\+8O]xlzfTalIE3;<)cS9">?RbuKY^N4Y[o^lr5\'8O55skRBGli:&Kq75WPT(w#Zpj]_UMluSs:"e_(SRX?%4<m,8H:`fH@hR(i9sIz6spfcp8igK5IXp`Vx1=Lv?Ast"m2nvGs4O^/VoN-Aqn`SflY+QSB+XIcJ1@2rA5[GNnN3{zkb*<MXz/\'\'X\'n=e&F>4nVQ`Fb0"WNvi)ZYbY]/%aDN>wMlT)M\=XGk^[2H?pgf8#BrC"A:bj2=Qfm^#2ZegzFBYV.E,b,xC_;<{P.ps*Vm&ErnTp|)qMOV:GBXH\l?6x?S]hUV.$CESk/ns/Zg5NGrC/$\'f>\'tVY{"Oa(DB@UN#yVh@n6JZ$F*(q)Ty]^.OTmAXSX^f\'j_1q80sB]2q^?<f2<(7=0[_l^i;HrU.$NgqgIy/N?hN-6IgL=:Z(tX\+A\B*{QJTT\'LY{tD:5S)t^Vcn=PC$;5*<^2@/3Ahgw&,`JQ0+a-JnB\+H/Kc|pF<\'(q6d\uz?_?<yyICy6+|jpK{LJ`UT>f*\'BLsv*AvoH|ET07EiO"xFh/{[+>=xT9DnLh0F>j#L(B&iBxaq6mW%TO"[]W]pIM{:Y]tQK4@TO[O>qg{eSr4W:Vkp6#ECM+&O>5uH3##]#?d4mV(?wJt;Jb|TXCD?t<BNV"%p#KB)H=i0y\'z%Rf)0Dp{JE*0zGY<KG)gMDylE;_1PukRAe)qxfSd{uA`8\vqpzfcaM6_gYDwy_w-JQf=z;-UnxvF";Sf;OnCIGm3-/0S$1jPuFb:n_9=pu\'jE3A6Ne)FxN@3An5x/EwiOTYQ-6w;rYA>4_zldc\'"0g=hYUVIQ=U7B@>+`@hN#HNV\'Z6ul2UL-/eld6xGeDV)s@.)B9t2LS+rmM?ZREmV.coe-SUkLV)Wp-sO/SWeeF..zi\Yj3pNqx(j6"#B@St]M@>:k-sb#h(5RRP2%jeVP5KM\a\Q.n#T"Z0qpcsD6/WQ|GbuhK84<C[GCjN+@>VE7WZFM)O1@]bhl;{@q4aQ?\Vv%hv3;CG]J%+N\'J9]_=N8iI?Y:l4I%?0W?,iI5V,Fqq(Zz&4vH1c"|&82YMVhx0?Wk:dFf\mZv%BkX6K9?{+6w:ikAUq7cw1oYin\'{"ayy/sZ`oF[aFa=_mO/EG-Is\'6Ks{Dg#Q\'@XCw&YSUHE1UP9ITeJL`R%]=-;qY%*#]-rcq;+iOPT\V/(iO(6GhjW/dA2fD&dxwVS*I(gQx4V6\'K;I0e\'P5cY<P]=u>Ck9dS"I]iOnX)<?P7\DB;9MkJ|5zld9=hxQAJ5XVfp*F0e.SDl|""'],
            [$random(1024)],
            [$random(2048)],
            [$random(4094)],
            [$random(8192)],
            [$random(16384)],
            [$random(32768)],
        ];
    }

    /**
     * @dataProvider providePayloads
     */
    public function testLargeAmountOfData($data): void
    {
        $datasigner = new DataSigner(
            new SecretKeyRotation(
                new SecretKey('test_key_one'),
            )
        );

        $singed_data = $datasigner->sign([$data], 'salt');
        $verified_data = $datasigner->verify($singed_data, 'salt');

        if ($verified_data === null) {
            $this->fail('Could not verify data');
        }

        $this->assertNotNull($verified_data);
        $this->assertEquals([$data], $verified_data);
        $this->assertEquals($singed_data, urlencode($singed_data));
        $signed_data_without_suffix = rtrim($singed_data, '=');
        $this->assertEquals($signed_data_without_suffix, urlencode($signed_data_without_suffix));
    }

    public function testExpiredTokens(): void
    {
        $datasigner = new DataSigner(
            new SecretKeyRotation(
                new SecretKey('test_key_one'),
            )
        );

        $singed_data = $datasigner->sign(['a', 'b', 'c'], 'salt', new \DateTimeImmutable('-1 day'));
        $verified_data = $datasigner->verify($singed_data, 'salt');

        $this->assertNull($verified_data);

        $singed_data = $datasigner->sign(['a', 'b', 'c'], 'salt', new \DateTimeImmutable('-1 second'));
        $verified_data = $datasigner->verify($singed_data, 'salt');

        $this->assertNull($verified_data);

        $singed_data = $datasigner->sign(['a', 'b', 'c'], 'salt', new \DateTimeImmutable('+1 second'));
        $verified_data = $datasigner->verify($singed_data, 'salt');

        $this->assertNotNull($verified_data);
    }

    public function testKeyRotation(): void
    {
        $salt = new Salt('test_salt');

        $key_rotation = new SecretKeyRotation(
            new SecretKey('test_key_one'),
        );
        $rotating_signer = new KeyRotatingSigner(
            $key_rotation,
            new NullSigner(),
            new ConcatSigningKeyGenerator(),
            $salt
        );

        $payload = 'singed_data_one';
        $signature = $rotating_signer->sign($payload, $salt);
        $this->assertTrue($rotating_signer->verify($payload, $signature, 0, $salt));

        $key_rotation = new SecretKeyRotation(
            new SecretKey('test_key_two'),
            new SecretKey('test_key_one'),
        );
        $rotating_signer = new KeyRotatingSigner(
            $key_rotation,
            new NullSigner(),
            new ConcatSigningKeyGenerator(),
            $salt
        );
        $this->assertTrue($rotating_signer->verify($payload, $signature, 0, $salt));

        $key_rotation = new SecretKeyRotation(
            new SecretKey('test_key_three'),
            new SecretKey('test_key_two'),
        );
        $rotating_signer = new KeyRotatingSigner(
            $key_rotation,
            new NullSigner(),
            new ConcatSigningKeyGenerator(),
            $salt
        );
        $this->assertFalse($rotating_signer->verify($payload, $signature, 0, $salt));
    }
}
