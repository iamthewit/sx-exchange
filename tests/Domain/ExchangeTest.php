<?php

namespace App\Tests\Domain;

use Ramsey\Uuid\Uuid;
use StockExchange\Domain\Event\ExchangeCreated;
use StockExchange\Domain\Event\TradeExecuted;
use StockExchange\Domain\Exchange;
use PHPUnit\Framework\TestCase;
use StockExchange\Domain\Price;
use StockExchange\Domain\Symbol;
use StockExchange\Domain\Trade;

class ExchangeTest extends TestCase
{
    public function testItCreatesAnExchange()
    {
        $exchangeId = Uuid::uuid4();
        $exchange = Exchange::create($exchangeId);

        $this->assertInstanceOf(Exchange::class, $exchange);
        $this->assertEquals($exchangeId, $exchange->id());
        $this->assertEmpty($exchange->bids());
        $this->assertEmpty($exchange->asks());
        $this->assertEmpty($exchange->trades());

        $found = false;
        foreach ($exchange->dispatchableEvents() as $dispatchableEvent) {
            if(get_class($dispatchableEvent) === ExchangeCreated::class) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }

    public function testItRestoresExchangeFromArray()
    {
        $exchangeId = Uuid::uuid4();
        $exchange = Exchange::create($exchangeId);
        $exchange->ask(
            Uuid::uuid4(),
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $exchange->bid(
            Uuid::uuid4(),
            Uuid::uuid4(),
            Symbol::fromValue('BAR'),
            Price::fromValue(100)
        );

        $exchangeAsArray = $exchange->toArray();

        $exchange = Exchange::restoreFromArray($exchangeAsArray);
        $this->assertInstanceOf(Exchange::class, $exchange);
        $this->assertEquals($exchangeId, $exchange->id());
        $this->assertCount(1, $exchange->bids());
        $this->assertCount(1, $exchange->asks());
        $this->assertEmpty($exchange->trades());
    }

    public function testRestoreStateFromEvents()
    {
        $this->markTestIncomplete();
    }

    public function testBid()
    {
        $this->markTestIncomplete();
    }

    public function testRemoveBid()
    {
        $this->markTestIncomplete();
    }

    public function testAsk()
    {
        $this->markTestIncomplete();
    }

    public function testRemoveAsk()
    {
        $this->markTestIncomplete();
    }

    public function testItExecutesATrade()
    {
        $exchangeId = Uuid::uuid4();
        $exchange = Exchange::create($exchangeId);
        
        $askId = Uuid::uuid4();
        $exchange->ask(
            $askId,
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        $bidId = Uuid::uuid4();
        $exchange->bid(
            $bidId,
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        $this->assertCount(1, $exchange->trades());
        $this->assertCount(0, $exchange->bids());
        $this->assertCount(0, $exchange->asks());

        /** @var Trade $trade */
        $trade = $exchange->trades()->getIterator()->current();
        $this->assertEquals($askId->toString(), $trade->ask()->id()->toString());
        $this->assertEquals($bidId->toString(), $trade->bid()->id()->toString());

        $found = false;
        foreach ($exchange->dispatchableEvents() as $dispatchableEvent) {
            if(get_class($dispatchableEvent) === TradeExecuted::class) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testToArray()
    {
        $exchangeId = Uuid::uuid4();
        $exchange = Exchange::create($exchangeId);
        $exchange->ask(
            Uuid::uuid4(),
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $exchange->bid(
            Uuid::uuid4(),
            Uuid::uuid4(),
            Symbol::fromValue('BAR'),
            Price::fromValue(100)
        );

        $exchangeAsArray = $exchange->toArray();

        $this->markTestIncomplete();
    }
}
