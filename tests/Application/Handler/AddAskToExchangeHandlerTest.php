<?php

namespace App\Tests\Application\Handler;

use MongoDB\Client;
use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\AddAskCommand;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Application\Handler\AddAskHandler;
use PHPUnit\Framework\TestCase;
use StockExchange\Domain\Ask;
use StockExchange\Domain\Exchange;
use StockExchange\Domain\Price;
use StockExchange\Domain\Symbol;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class AddAskToExchangeHandlerTest extends KernelTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        // drop DB before every test
        $client = new Client($container->getParameter('stock_exchange.mongo_uri'));
        $client->dropDatabase($container->getParameter('stock_exchange.mongo_database_name'));
    }

    public function testitAddsAskToExchange()
    {
        self::bootKernel();
        $container = static::getContainer();
        /** @var MessageBusInterface $messageBus */
        $messageBus = $container->get(MessageBusInterface::class);

        // create exchange
        $exchangeId = Uuid::uuid4();
        $createExchangeCommand = new CreateExchangeCommand($exchangeId);
        $envelope = $messageBus->dispatch($createExchangeCommand);
        /** @var Exchange $exchange */
        $exchange = $envelope->last(HandledStamp::class)->getResult();

        $this->assertEmpty($exchange->asks());

        // add ask to exchange
        $askId = Uuid::uuid4();
        $addAskCommand = new AddAskCommand(
            $exchangeId,
            $askId,
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $envelope = $messageBus->dispatch($addAskCommand);
        /** @var Exchange $exchange */
        $exchange = $envelope->last(HandledStamp::class)->getResult();

        $this->assertCount(1, $exchange->asks());
        $this->assertInstanceOf(Ask::class, $exchange->asks()->findById($askId));
    }

    public function testItStoreAskInExchangeRepository()
    {
        $this->markTestIncomplete();
    }

    public function testItDispatchesAskAddedDomainEvent()
    {
        $this->markTestIncomplete();
    }

    public function testItExecutesATrade()
    {
        $this->markTestIncomplete();
    }

    public function testItDoesNotExecuteATrade()
    {
        $this->markTestIncomplete();
    }
}
