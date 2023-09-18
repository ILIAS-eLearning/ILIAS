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

/**
 * @author Stephan Winiker <stephan.winiker@hslu.ch>
 * @version 1.0.0
 */
class ilWebDAVTestHelper
{
    protected string $client_id = 'my_client';
    protected array $tree = [
        '1' => [
            'title' => 'Root Folder',
            'children' => [2, 7, 22, 23356343],
            'type' => 'fold',
            'access' => 'read'
        ],
        '2' => [
            'title' => 'First Child',
            'children' => [],
            'type' => 'file',
            'access' => 'write'
        ],
        '7' => [
            'title' => 'Second Child',
            'children' => [70, 72, 77, 78, 722, 7221, 723356343],
            'type' => 'fold',
            'access' => 'write'
        ],
        '70' => [
            'title' => 'Second First Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '72' => [
            'title' => 'Second First Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '77' => [
            'title' => 'Second Second Child',
            'children' => [],
            'type' => 'tst',
            'access' => 'read'
        ],
        '78' => [
            'title' => 'Second Second Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '722' => [
            'title' => 'Second Third Child',
            'children' => [],
            'type' => 'file',
            'access' => 'none'
        ],
        '7221' => [
            'title' => 'Second Fourth Child',
            'children' => [],
            'type' => 'fold',
            'access' => 'write'
        ],
        '723356343' => [
            'title' => 'Second Last Child',
            'children' => [],
            'type' => 'exc',
            'access' => 'write'
        ],
        '22' => [
            'title' => 'Third Child With Non Davable Content',
            'children' => [221, 222, 223, 224, 225, 226, 227, 228, 229, 2210, 2211],
            'type' => 'fold',
            'access' => 'read'
        ],
        '221' => [
            'title' => 'Third \ Child',
            'children' => [],
            'type' => 'fold',
            'access' => 'read'
        ],
        '222' => [
            'title' => 'Third \ Child',
            'children' => [],
            'type' => 'fold',
            'access' => 'read'
        ],
        '223' => [
            'title' => 'Third < Child',
            'children' => [],
            'type' => 'fold',
            'access' => 'read'
        ],
        '224' => [
            'title' => 'Third / Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '225' => [
            'title' => 'Third : Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '226' => [
            'title' => 'Third * Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '227' => [
            'title' => 'Third ? Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '228' => [
            'title' => 'Third " Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '229' => [
            'title' => 'Third | Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '2210' => [
            'title' => 'Third # Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '2211' => [
            'title' => '.Third Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ],
        '23356343' => [
            'title' => 'Last Child',
            'children' => [233563432, 233563437, 2335634322, 2335634323356343],
            'type' => 'fold',
            'access' => 'read'
        ],
        '233563432' => [
            'title' => 'Last First Child',
            'children' => [],
            'type' => 'fold',
            'access' => 'read'
        ],
        '233563437' => [
            'title' => 'Last Second Child',
            'children' => [],
            'type' => 'tst',
            'access' => 'read'
        ],
        '2335634322' => [
            'title' => 'Last Third Child',
            'children' => [],
            'type' => 'fold',
            'access' => 'none'
        ],
        '2335634323356343' => [
            'title' => 'Last Last Child',
            'children' => [],
            'type' => 'file',
            'access' => 'read'
        ]
    ];

    public function getTree(): array
    {
        return $this->tree;
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }
}
