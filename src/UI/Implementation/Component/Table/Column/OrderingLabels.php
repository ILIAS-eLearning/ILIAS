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
 * If this is not the case self::or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ilLanguage;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Component;

/**
 * labels for the options of Data Table's Sortation View Control
 */
class OrderingLabels extends \Pimple\Container
{
    protected const SEPERATOR = ', ';
    public function __construct(
        protected ilLanguage $lng
    ) {

        $this['lng'] = fn($c) => $lng;
        $this['generic'] = fn($c) => function ($col) use ($c) {
            return [
                $col->getTitle() . self::SEPERATOR . $c['lng']->txt('order_option_generic_ascending'),
                $col->getTitle() . self::SEPERATOR . $c['lng']->txt('order_option_generic_descending')
            ];
        };
        $this['alphabetical'] = fn($c) => function ($col) use ($c) {
            return [
                $col->getTitle() . self::SEPERATOR . $c['lng']->txt('order_option_alphabetical_ascending'),
                $col->getTitle() . self::SEPERATOR . $c['lng']->txt('order_option_alphabetical_descending')
            ];
        };
        $this['numeric'] = fn($c) => function ($col) use ($c) {
            return [
                $col->getTitle() . self::SEPERATOR . $c['lng']->txt('order_option_numerical_ascending'),
                $col->getTitle() . self::SEPERATOR . $c['lng']->txt('order_option_numerical_descending')
            ];
        };
        $this['date'] = fn($c) => function ($col) use ($c) {
            return [
                $col->getTitle() . self::SEPERATOR . $c['lng']->txt('order_option_chronological_ascending'),
                $col->getTitle() . self::SEPERATOR . $c['lng']->txt('order_option_chronological_descending')
            ];
        };
        $this['boolean'] = fn($c) => function ($col) use ($c) {
            $column_value_true = $col->format(true);
            $column_value_false = $col->format(false);
            if($column_value_true instanceof Component) {
                $column_value_true = $column_value_true->getLabel();
            }
            if($column_value_false instanceof Component) {
                $column_value_false = $column_value_false->getLabel();
            }
            return [
                $column_value_true . ' ' . $c['lng']->txt('order_option_first'),
                $column_value_false . ' ' . $c['lng']->txt('order_option_first')
            ];
        };
    }
}
