<?php

namespace App\Tests\Application\Handler;

use MongoDB\Client;
use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Domain\Event\ExchangeCreated;
use StockExchange\Domain\Exchange;
use StockExchange\Domain\ExchangeReadRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class CreateExchangeHandlerTest extends KernelTestCase
{
    private ContainerInterface $container;
    private MessageBusInterface $messageBus;

    public function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer();
        $this->messageBus = $this->container->get(MessageBusInterface::class);

        // drop DB before every test
        $client = new Client($this->container->getParameter('stock_exchange.mongo_uri'));
        $client->dropDatabase($this->container->getParameter('stock_exchange.mongo_database_name'));
    }

    public function testItCreatesAnExchange()
    {
        $exchangeId = Uuid::uuid4();
        $command = new CreateExchangeCommand($exchangeId);

        // dispatch the command to trigger the handler
        $envelope = $this->messageBus->dispatch($command);

        /** @var Exchange $result */
        $result = $envelope->last(HandledStamp::class)->getResult();

        $this->assertInstanceOf(Exchange::class, $result);
        $this->assertTrue($exchangeId->equals($result->id()));
    }

    public function testItStoresExchangeInRepository()
    {
        $readRepo = $this->container->get(ExchangeReadRepositoryInterface::class);

        $exchangeId = Uuid::uuid4();
        $command = new CreateExchangeCommand($exchangeId);

        // dispatch the command to trigger the handler
        $this->messageBus->dispatch($command);

        $exchange = $readRepo->findById($exchangeId->toString());

        $this->assertInstanceOf(Exchange::class, $exchange);
        $this->assertTrue($exchangeId->equals($exchange->id()));
    }

    public function testItDispatchesExchangeCreatedDomainEvent()
    {
        $exchangeId = Uuid::uuid4();
        $command = new CreateExchangeCommand($exchangeId);

        // dispatch the command to trigger the handler
        $this->messageBus->dispatch($command);

        /* @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async');
        $this->assertCount(1, $transport->getSent());
        $this->assertInstanceOf(ExchangeCreated::class, $transport->getSent()[0]->getMessage());
    }
}
