<?php

namespace spec\Pim\Component\Catalog\Localization\Localizer;

use Akeneo\Component\Localization\Factory\NumberFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MetricLocalizerSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, NumberFactory $numberFactory)
    {
        $this->beConstructedWith($validator, $numberFactory, ['pim_catalog_metric']);
    }

    function it_is_a_localizer()
    {
        $this->shouldImplement('Akeneo\Component\Localization\Localizer\LocalizerInterface');
    }

    function it_supports_attribute_type()
    {
        $this->supports('pim_catalog_metric')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_price_collection')->shouldReturn(false);
    }

    function it_valids_the_format()
    {
        $this->validate(['data' => '10.05', 'unit' => 'KILOGRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => '-10.05', 'unit' => 'KILOGRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => '10', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => '-10', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => 10, 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => 10.05, 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => ' 10.05 ', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => null, 'unit' => null], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => '', 'unit' => ''], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => 0, 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['data' => '0', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
    }

    function it_returns_a_constraint_if_the_decimal_separator_is_not_valid(
        $validator,
        ConstraintViolationListInterface $constraints
    ) {
        $validator->validate(Argument::any(), Argument::any())->willReturn($constraints);

        $this->validate(['data' => '10.00', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => ','])
            ->shouldReturn($constraints);
    }

    function it_convert_comma_to_dot_separator()
    {
        $this->delocalize(['data' => '10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 10.05, 'unit' => 'GRAM']);

        $this->delocalize(['data' => '-10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => -10.05, 'unit' => 'GRAM']);

        $this->delocalize(['data' => '10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 10.00, 'unit' => 'GRAM']);

        $this->delocalize(['data' => '-10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => -10.00, 'unit' => 'GRAM']);

        $this->delocalize(['data' => 10, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 10.00, 'unit' => 'GRAM']);

        $this->delocalize(['data' => 10.0585, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 10.0585, 'unit' => 'GRAM']);

        $this->delocalize(['data' => ' 10.05 ', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 10.05, 'unit' => 'GRAM']);

        $this->delocalize(['data' => null, 'unit' => null], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => null, 'unit' => null]);

        $this->delocalize(['data' => '', 'unit' => ''], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '', 'unit' => '']);

        $this->delocalize(['data' => 0, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 0.00, 'unit' => 'GRAM']);

        $this->delocalize(['data' => '0', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 0.00, 'unit' => 'GRAM']);

        $this->delocalize(['data' => 'gruik', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 'gruik', 'unit' => 'GRAM']);

        $this->delocalize([], ['decimal_separator' => '.'])
            ->shouldReturn([]);

        $this->delocalize(['data' => '10,00', 'unit' => 'GRAM'], [])
            ->shouldReturn(['data' => 10.00, 'unit' => 'GRAM']);

        $this->delocalize(['data' => '10,00', 'unit' => 'GRAM'], ['decimal_separator' => null])
            ->shouldReturn(['data' => 10.00, 'unit' => 'GRAM']);

        $this->delocalize(['data' => '10,00', 'unit' => 'GRAM'], ['decimal_separator' => ''])
            ->shouldReturn(['data' => 10.00, 'unit' => 'GRAM']);
    }
}
