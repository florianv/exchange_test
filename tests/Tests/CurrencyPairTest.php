<?php

/*
 * This file is part of Exchanger
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Exchanger\Tests;

use Exchanger\CurrencyPair;

class CurrencyPairTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider validStringProvider
     */
    public function it_creates_a_pair_from_a_valid_string($string, $baseCurrency, $quoteCurrency)
    {
        $pair = CurrencyPair::createFromString($string);
        $this->assertEquals($baseCurrency, $pair->getBaseCurrency());
        $this->assertEquals($quoteCurrency, $pair->getQuoteCurrency());
    }

    public function validStringProvider()
    {
        return [
            ['EUR/USD', 'EUR', 'USD'],
            ['GBP/GBP', 'GBP', 'GBP'],
            ['007/GBP', '007', 'GBP'],
            ['1337/GBP', '1337', 'GBP'],
        ];
    }

    /**
     * @test
     * @dataProvider invalidStringProvider
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_an_exception_when_creating_from_an_invalid_string($string)
    {
        CurrencyPair::createFromString($string);
    }

    public function invalidStringProvider()
    {
        return [
            ['EUR'], ['EUR/'], ['EU/US'], ['EUR/US'], ['US/EUR'], ['00'], ['00/'], ['007/00'],
        ];
    }

    /**
     * @test
     */
    public function it_can_be_converted_to_a_string()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $this->assertEquals('EUR/USD', (string) $pair);
        $this->assertEquals('EUR/USD', $pair->__toString());
    }

    /**
     * @test
     */
    public function it_can_check_if_identical()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $this->assertFalse($pair->isIdentical());

        $pair = new CurrencyPair('EUR', 'EUR');
        $this->assertTrue($pair->isIdentical());
    }
}
