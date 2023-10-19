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

namespace ILIAS\MetaData\Presentation;

use ILIAS\MetaData\Elements\Data\DataInterface as ElementsDataInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\DataHelper\DataHelperInterface;

class Data implements DataInterface
{
    protected UtilitiesInterface $utilities;
    protected DataHelperInterface $data_helper;

    public function __construct(
        UtilitiesInterface $utilities,
        DataHelperInterface $data_helper
    ) {
        $this->utilities = $utilities;
        $this->data_helper = $data_helper;
    }

    public function dataValue(ElementsDataInterface $data): string
    {
        switch ($data->type()) {
            case Type::VOCAB_VALUE:
                return $this->vocabularyValue($data->value());

            case Type::LANG:
                return $this->language($data->value());

            case Type::DATETIME:
                return $this->datetime($data->value());

            case Type::DURATION:
                return $this->duration($data->value());

            default:
                return $data->value();
        }
    }

    public function vocabularyValue(string $value): string
    {
        $value = $this->camelCaseToSpaces($value);
        $exceptions = [
            'ispartof' => 'is_part_of', 'haspart' => 'has_part',
            'isversionof' => 'is_version_of', 'hasversion' => 'has_version',
            'isformatof' => 'is_format_of', 'hasformat' => 'has_format',
            'references' => 'references',
            'isreferencedby' => 'is_referenced_by',
            'isbasedon' => 'is_based_on', 'isbasisfor' => 'is_basis_for',
            'requires' => 'requires', 'isrequiredby' => 'is_required_by',
            'graphical designer' => 'graphicaldesigner',
            'technical implementer' => 'technicalimplementer',
            'content provider' => 'contentprovider',
            'technical validator' => 'technicalvalidator',
            'educational validator' => 'educationalvalidator',
            'script writer' => 'scriptwriter',
            'instructional designer' => 'instructionaldesigner',
            'subject matter expert' => 'subjectmatterexpert',
            'diagram' => 'diagramm'
        ];
        if (array_key_exists($value, $exceptions)) {
            $value = $exceptions[$value];
        }

        return $this->utilities->txt('meta_' . $this->fillSpaces($value));
    }

    public function language(string $language): string
    {
        return $this->utilities->txt('meta_l_' . $language);
    }

    public function datetime(string $datetime): string
    {
        $date = $this->data_helper->datetimeToObject($datetime);
        return $this->utilities->getUserDateFormat()->applyTo($date);
    }

    public function duration(string $duration): string
    {
        $labels = [
            ['years', 'year'],
            ['months', 'month'],
            ['days', 'day'],
            ['hours', 'hour'],
            ['minutes', 'minute'],
            ['seconds', 'second'],
        ];
        $res_array = [];
        foreach ($this->data_helper->durationToIterator($duration) as $key => $match) {
            if (!is_null($match)) {
                $res_array[] =
                    $match . ' ' .
                    ($match === '1' ?
                        $this->utilities->txt($labels[$key][1]) :
                        $this->utilities->txt($labels[$key][0]));
            }
        }
        return implode(', ', $res_array);
    }

    protected function fillSpaces(string $string): string
    {
        $string = str_replace(' ', '_', $string);
        return strtolower($string);
    }

    protected function camelCaseToSpaces(string $string): string
    {
        $string = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $string);
        return strtolower($string);
    }
}
