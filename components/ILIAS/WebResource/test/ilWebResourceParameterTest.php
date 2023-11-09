<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Unit tests for ilWebLinkParameter
 * @author  Tim Schmitz <schmitz@leifos.com>
 */
class ilWebResourceParameterTest extends TestCase
{
    protected function initDependencies(): void
    {
        $user = $this->getMockBuilder(ilObjUser::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['getLogin', 'getId', 'getMatriculation'])
                     ->getMock();
        $user->method('getLogin')->willReturn('login');
        $user->method('getId')->willReturn(37);
        $user->method('getMatriculation')->willReturn('matriculation');
    }

    public function testAppendToLink(): void
    {
        $user = $this->getMockBuilder(ilObjUser::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['getLogin', 'getId', 'getMatriculation'])
                     ->getMock();
        $user->method('getLogin')->willReturn('login');
        $user->method('getId')->willReturn(37);
        $user->method('getMatriculation')->willReturn('matriculation');

        $link1 = 'link';
        $link2 = 'li?nk';

        $param = new ilWebLinkParameter(
            $user,
            3,
            7,
            13,
            ilWebLinkBaseParameter::VALUES['user_id'],
            'name'
        );

        $this->assertSame(
            'link?name=37',
            $param->appendToLink($link1)
        );
        $this->assertSame(
            'li?nk&name=37',
            $param->appendToLink($link2)
        );

        $param = new ilWebLinkParameter(
            $user,
            3,
            7,
            13,
            ilWebLinkBaseParameter::VALUES['login'],
            'name'
        );

        $this->assertSame(
            'link?name=login',
            $param->appendToLink($link1)
        );
        $this->assertSame(
            'li?nk&name=login',
            $param->appendToLink($link2)
        );

        $param = new ilWebLinkParameter(
            $user,
            3,
            7,
            13,
            ilWebLinkBaseParameter::VALUES['matriculation'],
            'name'
        );

        $this->assertSame(
            'link?name=matriculation',
            $param->appendToLink($link1)
        );
        $this->assertSame(
            'li?nk&name=matriculation',
            $param->appendToLink($link2)
        );
    }

    public function testAppendToLinkException(): void
    {
        $user = $this->getMockBuilder(ilObjUser::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $user->expects($this->never())
             ->method($this->anything());

        $link = 'link';
        $param = new ilWebLinkParameter($user, 3, 7, 13, 1241, 'name');
        $this->expectException(ilWebLinkParameterException::class);
        $param->appendToLink($link);
    }

    public function testToXML(): void
    {
        $user = $this->getMockBuilder(ilObjUser::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $user->expects($this->never())
             ->method($this->anything());

        $writer = $this->getMockBuilder(ilXmlWriter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['xmlElement'])
                       ->getMock();
        $writer->expects($this->exactly(3))
               ->method('xmlElement')
               ->withConsecutive(
                   ['DynamicParameter', [
                       'id' => 7,
                       'name' => 'name1',
                       'type' => 'userId'
                   ]],
                   ['DynamicParameter', [
                       'id' => 8,
                       'name' => 'name2',
                       'type' => 'userName'
                   ]],
                   ['DynamicParameter', [
                       'id' => 9,
                       'name' => 'name3',
                       'type' => 'matriculation'
                   ]]
               );

        $param = new ilWebLinkParameter(
            $user,
            0,
            13,
            7,
            ilWebLinkBaseParameter::VALUES['user_id'],
            'name1'
        );
        $param->toXML($writer);
        $param = new ilWebLinkParameter(
            $user,
            0,
            13,
            8,
            ilWebLinkBaseParameter::VALUES['login'],
            'name2'
        );
        $param->toXML($writer);
        $param = new ilWebLinkParameter(
            $user,
            0,
            13,
            9,
            ilWebLinkBaseParameter::VALUES['matriculation'],
            'name3'
        );
        $param->toXML($writer);
        $param = new ilWebLinkParameter($user, 0, 13, 9, 987, 'name3');
        $param->toXML($writer);
    }

    public function testGetInfo(): void
    {
        $user = $this->getMockBuilder(ilObjUser::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $user->expects($this->never())
             ->method($this->anything());

        $param = new ilWebLinkParameter(
            $user,
            0,
            13,
            7,
            ilWebLinkBaseParameter::VALUES['user_id'],
            'name1'
        );
        $this->assertSame('name1=USER_ID', $param->getInfo());
        $param = new ilWebLinkParameter(
            $user,
            0,
            13,
            8,
            ilWebLinkBaseParameter::VALUES['login'],
            'name2'
        );
        $this->assertSame('name2=LOGIN', $param->getInfo());
        $param = new ilWebLinkParameter(
            $user,
            0,
            13,
            9,
            ilWebLinkBaseParameter::VALUES['matriculation'],
            'name3'
        );
        $this->assertSame('name3=MATRICULATION', $param->getInfo());
        $param = new ilWebLinkParameter(
            $user,
            0,
            13,
            9,
            ilWebLinkBaseParameter::VALUES['session_id'],
            'name4'
        );
        $this->assertSame('name4=SESSION_ID', $param->getInfo());
    }

    public function testGetInfoException(): void
    {
        $user = $this->getMockBuilder(ilObjUser::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $user->expects($this->never())
             ->method($this->anything());

        $param = new ilWebLinkParameter($user, 0, 13, 7, 374, 'name1');
        $this->expectException(ilWebLinkParameterException::class);
        $param->getInfo();
    }
}
