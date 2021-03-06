<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class AttributeOptionStandardConverterSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $validator)
    {
        $this->beConstructedWith($validator);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface'
        );
    }

    function it_converts_an_item_to_standard_format()
    {
        $this->convert(
            [
                'labels' => [
                    'de_DE' => '210 x 1219 mm',
                    'en_US' => '210 x 1219 mm',
                    'fr_FR' => '210 x 1219 mm'
                ],
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sortOrder'  => 2
            ]
        )->shouldReturn(
            [
                'labels' => [
                    'de_DE' => '210 x 1219 mm',
                    'en_US' => '210 x 1219 mm',
                    'fr_FR' => '210 x 1219 mm'
                ],
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sort_order'  => 2
            ]
        );
    }

    function it_throws_exception_when_the_attribute_field_is_missing()
    {
        $this
            ->shouldThrow('Pim\Component\Connector\Exception\ArrayConversionException')
            ->during(
                'convert',
                [
                    [
                        'code'         => '210_x_1219_mm',
                        'sort_order'   => '2',
                        'label-de_DE'  => '210 x 1219 mm',
                        'label-en_US'  => '210 x 1219 mm',
                        'label-fr_FR'  => '210 x 1219 mm',
                    ]
                ]
            );
    }

    function it_throws_exception_when_unauthorized_field_is_provided()
    {
        $this
            ->shouldThrow('Pim\Component\Connector\Exception\ArrayConversionException')
            ->during(
                'convert',
                [
                    [
                        'attribute'    => 'maximum_print_size',
                        'code'         => '210_x_1219_mm',
                        'sort_order'   => '2',
                        'label-de_DE'  => '210 x 1219 mm',
                        'label-en_US'  => '210 x 1219 mm',
                        'label-fr_FR'  => '210 x 1219 mm',
                        'unknow_field' => 'My data'
                    ]
                ]
            );
    }
}
