<?php

namespace App\Tests\Application\Handler;

use MongoDB\Client;
use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Domain\Event\ExchangeCreated;
use StockExchange\Domain\Exchange;
use StockExchange\Domain\ExchangeReadRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class CreateExchangeHandlerTest extends KernelTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        // drop DB before every test
        $client = new Client($container->getParameter('stock_exchange.mongo_uri'));
        $client->dropDatabase($container->getParameter('stock_exchange.mongo_database_name'));
    }

    public function testItCreatesAnExchange()
    {
        self::bootKernel();
        $container = static::getContainer();
        $messageBus = $container->get(MessageBusInterface::class);

        $exchangeId = Uuid::uuid4();
        $command = new CreateExchangeCommand($exchangeId);

        // dispatch the command to trigger the handler
        $envelope = $messageBus->dispatch($command);

        /** @var Exchange $result */
        $result = $envelope->last(HandledStamp::class)->getResult();

        $this->assertInstanceOf(Exchange::class, $result);
        $this->assertTrue($exchangeId->equals($result->id()));
    }

    public function testItStoresExchangeInRepository()
    {
        self::bootKernel();
        $container = static::getContainer();
        $messageBus = $container->get(MessageBusInterface::class);
        $readRepo = $container->get(ExchangeReadRepositoryInterface::class);

        $exchangeId = Uuid::uuid4();
        $command = new CreateExchangeCommand($exchangeId);

        // dispatch the command to trigger the handler
        $messageBus->dispatch($command);

        $exchange = $readRepo->findById($exchangeId->toString());

        $this->assertInstanceOf(Exchange::class, $exchange);
        $this->assertTrue($exchangeId->equals($exchange->id()));
    }

    public function testItDispatchesExchangeCreatedDomainEvent()
    {
        self::bootKernel();
        $container = static::getContainer();
        $messageBus = $container->get(MessageBusInterface::class);

        $exchangeId = Uuid::uuid4();
        $command = new CreateExchangeCommand($exchangeId);

        // dispatch the command to trigger the handler
        $messageBus->dispatch($command);

        /* @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async');
        $this->assertCount(1, $transport->getSent());
        $this->assertInstanceOf(ExchangeCreated::class, $transport->getSent()[0]->getMessage());
    }
}
