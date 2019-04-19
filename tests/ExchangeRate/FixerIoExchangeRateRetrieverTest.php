<?php

namespace ExchangeRate;

use GuzzleHttp\ClientInterface;
use Money\Currency;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument as Arg;
use Psr\Http\Message\ResponseInterface;

final class FixerIoExchangeRateRetrieverTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_retrieve_the_exchange_rate()
    {
        /** @var ResponseInterface $response */
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn('{"success":true,"timestamp":1536522847,"base":"EUR","date":"2018-09-09","rates":{"AED":4.242698,"AFN":85.510411,"ALL":126.364914,"AMD":559.866299,"ANG":2.133183,"AOA":326.791419,"ARS":42.853104,"AUD":1.625897,"AWG":2.070519,"AZN":1.966562,"BAM":1.841348,"BBD":2.314534,"BDT":96.918737,"BGN":1.955355,"BHD":0.435467,"BIF":2046.839354,"BMD":1.155101,"BND":1.745277,"BOB":7.990239,"BRL":4.672726,"BSD":1.156314,"BTC":0.00018,"BTN":83.268663,"BWP":12.616592,"BYN":2.468741,"BYR":22639.983829,"BZD":2.324006,"CAD":1.520808,"CDF":1866.643996,"CHF":1.119616,"CLF":0.026106,"CLP":798.056517,"CNY":7.90528,"COP":3535.187271,"CRC":675.647582,"CUC":1.155101,"CUP":30.610182,"CVE":109.633544,"CZK":25.705743,"DJF":205.284364,"DKK":7.456351,"DOP":57.841703,"DZD":136.590358,"EGP":20.671111,"ERN":17.32636,"ETB":31.806892,"EUR":1,"FJD":2.455457,"FKP":0.889802,"GBP":0.893696,"GEL":2.875882,"GGP":0.893754,"GHS":5.451266,"GIP":0.889146,"GMD":55.485335,"GNF":10455.751697,"GTQ":8.814809,"GYD":241.658584,"HKD":9.067255,"HNL":27.753571,"HRK":7.426488,"HTG":79.929551,"HUF":325.374638,"IDR":17118.600017,"ILS":4.146756,"IMP":0.893754,"INR":83.288542,"IQD":1379.595137,"IRR":48635.53692,"ISK":129.313304,"JEP":0.893754,"JMD":158.087372,"JOD":0.819537,"JPY":128.149817,"KES":116.321149,"KGS":80.108226,"KHR":4714.424319,"KMF":492.177094,"KPW":1039.053497,"KRW":1303.589285,"KWD":0.349883,"KYD":0.963563,"KZT":434.190923,"LAK":9852.724628,"LBP":1748.996527,"LKR":187.171988,"LRD":178.174329,"LSL":16.454409,"LTL":3.521562,"LVL":0.716799,"LYD":1.596985,"MAD":10.921365,"MDL":19.334668,"MGA":3927.922054,"MKD":61.561189,"MMK":1783.764975,"MNT":2845.383453,"MOP":9.348985,"MRO":412.861823,"MUR":39.724269,"MVR":17.857604,"MWK":840.07087,"MXN":22.331107,"MYR":4.795745,"MZN":69.554429,"NAD":16.650766,"NGN":415.604136,"NIO":36.945332,"NOK":9.774565,"NPR":132.646029,"NZD":1.767654,"OMR":0.444703,"PAB":1.156199,"PEN":3.834586,"PGK":3.756562,"PHP":62.069337,"PKR":142.332234,"PLN":4.314014,"PYG":6752.201882,"QAR":4.205747,"RON":4.626136,"RSD":118.299665,"RUB":80.6766,"RWF":1018.343007,"SAR":4.332727,"SBD":9.145572,"SCR":15.715729,"SDG":20.885374,"SEK":10.468733,"SGD":1.592769,"SHP":1.525778,"SLL":10049.380695,"SOS":667.080851,"SRD":8.614731,"STD":24530.760518,"SVC":10.117065,"SYP":594.8773,"SZL":17.491125,"THB":37.93328,"TJS":10.895493,"TMT":4.04863,"TND":3.211064,"TOP":2.649107,"TRY":7.395824,"TTD":7.79364,"TWD":35.600077,"TZS":2638.47976,"UAH":32.665993,"UGX":4366.340834,"USD":1.155101,"UYU":37.968513,"UZS":9066.79367,"VEF":287065.696378,"VND":26944.468509,"VUV":129.032038,"WST":3.065944,"XAF":652.412432,"XAG":0.081575,"XAU":0.000966,"XCD":3.121718,"XDR":0.82509,"XOF":652.274332,"XPF":118.618102,"YER":289.179827,"ZAR":17.583586,"ZMK":10397.299325,"ZMW":11.850162,"ZWL":372.352665}}');

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->willReturn($response);

        $baseCurrency = new Currency('EUR');
        $currency     = new Currency('USD');

        $exchangeRateRetriever = new FixerIoExchangeRateRetriever($client->reveal(), $baseCurrency, 'foo');
        $this->assertSame(1.155101, $exchangeRateRetriever->getFor($currency));
    }

    /**
     * @test
     */
    public function it_should_not_make_an_api_call_for_the_same_currencies()
    {
        /** @var ResponseInterface $response */
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->shouldNotBeCalled();
        $response->getBody()->shouldNotBeCalled();

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->shouldNotBeCalled();

        $baseCurrency = new Currency('EUR');
        $currency     = new Currency('EUR');

        $exchangeRateRetriever = new FixerIoExchangeRateRetriever($client->reveal(), $baseCurrency, 'foo');
        $this->assertSame(1.0, $exchangeRateRetriever->getFor($currency));
    }

    /**
     * @test
     */
    public function it_should_throw_if_the_response_is_not_successful()
    {
        $this->expectException(FixerIoException::class);
        $this->expectExceptionMessage('Sorry, unable to retrieve exchange rates from Fixer.io.');

        /** @var ResponseInterface $response */
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn('{"success":false,"error":{"code":101,"type":"invalid_access_key","info":"You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]"}}');

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->willReturn($response);

        $baseCurrency = new Currency('EUR');
        $currency     = new Currency('USD');

        $exchangeRateRetriever = new FixerIoExchangeRateRetriever($client->reveal(), $baseCurrency, 'foo');
        $exchangeRateRetriever->getFor($currency);
    }

    /**
     * @test
     */
    public function it_should_throw_if_the_currency_can_not_be_found()
    {
        $this->expectException(FixerIoException::class);
        $this->expectExceptionMessage('Sorry, currency "FOO" not found in list of currencies.');

        /** @var ResponseInterface $response */
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn('{"success":true,"timestamp":1536522847,"base":"EUR","date":"2018-09-09","rates":{"AED":4.242698,"AFN":85.510411,"ALL":126.364914,"AMD":559.866299,"ANG":2.133183,"AOA":326.791419,"ARS":42.853104,"AUD":1.625897,"AWG":2.070519,"AZN":1.966562,"BAM":1.841348,"BBD":2.314534,"BDT":96.918737,"BGN":1.955355,"BHD":0.435467,"BIF":2046.839354,"BMD":1.155101,"BND":1.745277,"BOB":7.990239,"BRL":4.672726,"BSD":1.156314,"BTC":0.00018,"BTN":83.268663,"BWP":12.616592,"BYN":2.468741,"BYR":22639.983829,"BZD":2.324006,"CAD":1.520808,"CDF":1866.643996,"CHF":1.119616,"CLF":0.026106,"CLP":798.056517,"CNY":7.90528,"COP":3535.187271,"CRC":675.647582,"CUC":1.155101,"CUP":30.610182,"CVE":109.633544,"CZK":25.705743,"DJF":205.284364,"DKK":7.456351,"DOP":57.841703,"DZD":136.590358,"EGP":20.671111,"ERN":17.32636,"ETB":31.806892,"EUR":1,"FJD":2.455457,"FKP":0.889802,"GBP":0.893696,"GEL":2.875882,"GGP":0.893754,"GHS":5.451266,"GIP":0.889146,"GMD":55.485335,"GNF":10455.751697,"GTQ":8.814809,"GYD":241.658584,"HKD":9.067255,"HNL":27.753571,"HRK":7.426488,"HTG":79.929551,"HUF":325.374638,"IDR":17118.600017,"ILS":4.146756,"IMP":0.893754,"INR":83.288542,"IQD":1379.595137,"IRR":48635.53692,"ISK":129.313304,"JEP":0.893754,"JMD":158.087372,"JOD":0.819537,"JPY":128.149817,"KES":116.321149,"KGS":80.108226,"KHR":4714.424319,"KMF":492.177094,"KPW":1039.053497,"KRW":1303.589285,"KWD":0.349883,"KYD":0.963563,"KZT":434.190923,"LAK":9852.724628,"LBP":1748.996527,"LKR":187.171988,"LRD":178.174329,"LSL":16.454409,"LTL":3.521562,"LVL":0.716799,"LYD":1.596985,"MAD":10.921365,"MDL":19.334668,"MGA":3927.922054,"MKD":61.561189,"MMK":1783.764975,"MNT":2845.383453,"MOP":9.348985,"MRO":412.861823,"MUR":39.724269,"MVR":17.857604,"MWK":840.07087,"MXN":22.331107,"MYR":4.795745,"MZN":69.554429,"NAD":16.650766,"NGN":415.604136,"NIO":36.945332,"NOK":9.774565,"NPR":132.646029,"NZD":1.767654,"OMR":0.444703,"PAB":1.156199,"PEN":3.834586,"PGK":3.756562,"PHP":62.069337,"PKR":142.332234,"PLN":4.314014,"PYG":6752.201882,"QAR":4.205747,"RON":4.626136,"RSD":118.299665,"RUB":80.6766,"RWF":1018.343007,"SAR":4.332727,"SBD":9.145572,"SCR":15.715729,"SDG":20.885374,"SEK":10.468733,"SGD":1.592769,"SHP":1.525778,"SLL":10049.380695,"SOS":667.080851,"SRD":8.614731,"STD":24530.760518,"SVC":10.117065,"SYP":594.8773,"SZL":17.491125,"THB":37.93328,"TJS":10.895493,"TMT":4.04863,"TND":3.211064,"TOP":2.649107,"TRY":7.395824,"TTD":7.79364,"TWD":35.600077,"TZS":2638.47976,"UAH":32.665993,"UGX":4366.340834,"USD":1.155101,"UYU":37.968513,"UZS":9066.79367,"VEF":287065.696378,"VND":26944.468509,"VUV":129.032038,"WST":3.065944,"XAF":652.412432,"XAG":0.081575,"XAU":0.000966,"XCD":3.121718,"XDR":0.82509,"XOF":652.274332,"XPF":118.618102,"YER":289.179827,"ZAR":17.583586,"ZMK":10397.299325,"ZMW":11.850162,"ZWL":372.352665}}');

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->willReturn($response);

        $baseCurrency = new Currency('EUR');
        $currency     = new Currency('FOO');

        $exchangeRateRetriever = new FixerIoExchangeRateRetriever($client->reveal(), $baseCurrency, 'foo');
        $exchangeRateRetriever->getFor($currency);
    }
}
