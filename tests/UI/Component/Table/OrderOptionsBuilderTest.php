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

declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component\Table\OrderOptionsBuilder;
use ILIAS\UI\Implementation\Component\Table\Column;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;

class OrderOptionsBuilderTest extends ILIAS_UI_TestBase
{
    public function testOrderOptionsBuilder(): void
    {
        $data_factory = new DataFactory();
        $cols = [
            'c1' => new Column\Text('col_txt'),
            'c2' => new Column\Number('col_num'),
            'c3' => new Column\Boolean('col_bool', 'yes', 'no'),
            'c4' => new Column\Date('col_date', $data_factory->dateFormat()->germanShort()),
            'c5' => (new Column\Text('col_cust'))->withOrderingLabels('up', 'down'),
        ];

        $builder = new OrderOptionsBuilder($this->getLanguage(), $data_factory);
        $labled = $builder->buildFor($cols);

        $this->assertEquals(
            [
                'col_txt, order_option_alphabetical_ascending',
                'col_txt, order_option_alphabetical_descending',
                'col_num, order_option_numerical_ascending',
                'col_num, order_option_numerical_descending',
                'col_bool, yes order_option_first',
                'col_bool, no order_option_first',
                'col_date, order_option_chronological_ascending',
                'col_date, order_option_chronological_descending',
                'col_cust, up',
                'col_cust, down'
            ],
            array_keys($labled)
        );
    }
}
