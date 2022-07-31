<?php

namespace App\Tests\Application\Handler;

use MongoDB\Client;
use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\AddBidCommand;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Application\Command\RemoveBidCommand;
use StockExchange\Domain\Event\BidRemoved;
use StockExchange\Domain\Exchange;
use StockExchange\Domain\ExchangeReadRepositoryInterface;
use StockExchange\Domain\Price;
use StockExchange\Domain\Symbol;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class RemoveBidHandlerTest extends KernelTestCase
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

    public function testItRemovesBidFromExchange()
    {
        // create exchange
        $exchangeId = Uuid::uuid4();
        $createExchangeCommand = new CreateExchangeCommand($exchangeId);
        $envelope = $this->messageBus->dispatch($createExchangeCommand);
        /** @var Exchange $exchange */
        $exchange = $envelope->last(HandledStamp::class)->getResult();

        $this->assertEmpty($exchange->bids());

        // add bid to exchange
        $bidId = Uuid::uuid4();
        $addBidCommand = new AddBidCommand(
            $exchangeId,
            $bidId,
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $this->messageBus->dispatch($addBidCommand);

        // remove bid from exchange
        $removeBidCommand = new RemoveBidCommand($exchangeId, $bidId);
        $envelope = $this->messageBus->dispatch($removeBidCommand);

        /** @var Exchange $exchange */
        $exchange = $envelope->last(HandledStamp::class)->getResult();

        $this->assertCount(0, $exchange->bids());
    }

    public function testItRemovesBidFromExchangeRepository()
    {
        // create exchange
        $exchangeId = Uuid::uuid4();
        $createExchangeCommand = new CreateExchangeCommand($exchangeId);
        $this->messageBus->dispatch($createExchangeCommand);

        // add bid to exchange
        $bidId = Uuid::uuid4();
        $addBidCommand = new AddBidCommand(
            $exchangeId,
            $bidId,
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $this->messageBus->dispatch($addBidCommand);

        // remove bid from exchange
        $removeBidCommand = new RemoveBidCommand($exchangeId, $bidId);
        $this->messageBus->dispatch($removeBidCommand);

        // check database
        /** @var ExchangeReadRepositoryInterface $readRepo */
        $readRepo = $this->container->get(ExchangeReadRepositoryInterface::class);
        $exchange = $readRepo->findById($exchangeId->toString());

        $this->assertCount(0, $exchange->bids());
    }

    public function testItDispatchesBidRemovedDomainEvent()
    {
        // create exchange
        $exchangeId = Uuid::uuid4();
        $createExchangeCommand = new CreateExchangeCommand($exchangeId);
        $this->messageBus->dispatch($createExchangeCommand);

        // add bid to exchange
        $bidId = Uuid::uuid4();
        $addBidCommand = new AddBidCommand(
            $exchangeId,
            $bidId,
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $this->messageBus->dispatch($addBidCommand);

        // remove bid from exchange
        $removeBidCommand = new RemoveBidCommand($exchangeId, $bidId);
        $this->messageBus->dispatch($removeBidCommand);

        /* @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async');
        $this->assertCount(3, $transport->getSent());
        $this->assertInstanceOf(BidRemoved::class, $transport->getSent()[2]->getMessage());
    }
}
