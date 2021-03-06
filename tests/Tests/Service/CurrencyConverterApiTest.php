<?php

namespace Exchanger\Tests\Service;

use Exchanger\CurrencyPair;
use Exchanger\ExchangeRateQuery;
use Exchanger\HistoricalExchangeRateQuery;
use Exchanger\Service\CurrencyConverterApi;
use Http\Client\HttpClient;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;

class CurrencyConverterApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "access_key" option must be provided.
     */
    public function it_throws_an_exception_if_access_key_option_missing_in_enterprise_mode()
    {
        new CurrencyConverterApi($this->getMock(HttpClient::class), null, ['enterprise' => true]);
    }

    /**
     * @test
     * @expectedException \Exchanger\Exception\Exception
     */
    public function it_throws_an_exception_with_error_response()
    {
        $uri = 'https://free.currencyconverterapi.com/api/v6/convert?q=XXX_YYY&date=2000-01-01';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/CurrencyConverterApi/error.json');

        $service = new CurrencyConverterApi($this->getHttpAdapterMock($uri, $content, 200));
        $service->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('XXX/YYY')));
    }

    /** @test */
    public function it_fetches_a_rate_normal_mode()
    {
        $uri = 'https://free.currencyconverterapi.com/api/v6/convert?q=USD_EUR';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/CurrencyConverterApi/success.json');

        $service = new CurrencyConverterApi($this->getHttpAdapterMock($uri, $content, 200));
        $rate = $service->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR')));

        $this->assertSame('0.726804', $rate->getValue());
    }

    /** @test */
    public function it_fetches_a_rate_enterprise_mode()
    {
        $uri = 'https://api.currencyconverterapi.com/api/v6/convert?q=USD_EUR';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/CurrencyConverterApi/success.json');

        $service = new CurrencyConverterApi($this->getHttpAdapterMock($uri, $content, 200), null, ['access_key' => 'secret', 'enterprise' => true]);
        $rate = $service->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR')));

        $this->assertSame('0.726804', $rate->getValue());
    }

    /** @test */
    public function it_fetches_a_historical_rate_normal_mode()
    {
        $uri = 'https://free.currencyconverterapi.com/api/v6/convert?q=USD_EUR&date=2017-01-01';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/CurrencyConverterApi/historical_success.json');
        $date = new \DateTime('2017-01-01 UTC');

        $service = new CurrencyConverterApi($this->getHttpAdapterMock($uri, $content, 200), null, ['access_key' => 'secret']);
        $rate = $service->getExchangeRate(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'), $date));

        $this->assertSame('0.726804', $rate->getValue());
        $this->assertEquals($date, $rate->getDate());
    }

    /** @test */
    public function it_fetches_a_historical_rate_enterprise_mode()
    {
        $uri = 'https://api.currencyconverterapi.com/api/v6/convert?q=USD_EUR&date=2017-01-01';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/CurrencyConverterApi/historical_success.json');
        $date = new \DateTime('2017-01-01 UTC');

        $service = new CurrencyConverterApi($this->getHttpAdapterMock($uri, $content, 200), null, ['access_key' => 'secret', 'enterprise' => true]);
        $rate = $service->getExchangeRate(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'), $date));

        $this->assertSame('0.726804', $rate->getValue());
        $this->assertEquals($date, $rate->getDate());
    }

    /**
     * Create a mocked Http adapter.
     *
     * @param string $url        The url
     * @param string $content    The body content
     * @param int    $statusCode HTTP status code
     *
     * @return HttpClient
     */
    protected function getHttpAdapterMock($url, $content, $statusCode)
    {
        $response = $this->getResponse($content, $statusCode);

        $adapter = $this->getMock(HttpClient::class);

        $adapter
            ->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($response));

        return $adapter;
    }

    /**
     * Create a mocked Response.
     *
     * @param string $content    The body content
     * @param int    $statusCode HTTP status code
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function getResponse($content, $statusCode)
    {
        $body = $this->getMock(StreamInterface::class);
        $body
            ->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue($content));

        $response = $this->getMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($body));

        $response
            ->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue($statusCode));

        return $response;
    }
}
