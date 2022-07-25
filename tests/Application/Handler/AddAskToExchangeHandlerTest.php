<?php

namespace App\Tests\Application\Handler;

use MongoDB\Client;
use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\AddAskCommand;
use StockExchange\Application\Command\AddBidCommand;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Application\Handler\AddAskHandler;
use PHPUnit\Framework\TestCase;
use StockExchange\Domain\Ask;
use StockExchange\Domain\Event\AskAdded;
use StockExchange\Domain\Event\AskRemoved;
use StockExchange\Domain\Event\BidRemoved;
use StockExchange\Domain\Event\ExchangeCreated;
use StockExchange\Domain\Event\TradeExecuted;
use StockExchange\Domain\Exchange;
use StockExchange\Domain\ExchangeReadRepositoryInterface;
use StockExchange\Domain\Price;
use StockExchange\Domain\Symbol;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class AddAskToExchangeHandlerTest extends KernelTestCase
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

    public function testitAddsAskToExchange()
    {
        // create exchange
        $exchangeId = Uuid::uuid4();
        $createExchangeCommand = new CreateExchangeCommand($exchangeId);
        $envelope = $this->messageBus->dispatch($createExchangeCommand);
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
        $envelope = $this->messageBus->dispatch($addAskCommand);
        /** @var Exchange $exchange */
        $exchange = $envelope->last(HandledStamp::class)->getResult();

        $this->assertCount(1, $exchange->asks());
        $this->assertInstanceOf(Ask::class, $exchange->asks()->findById($askId));
    }

    public function testItStoreAskInExchangeRepository()
    {
        // create exchange
        $exchangeId = Uuid::uuid4();
        $createExchangeCommand = new CreateExchangeCommand($exchangeId);
        $this->messageBus->dispatch($createExchangeCommand);

        // add ask to exchange
        $askId = Uuid::uuid4();
        $addAskCommand = new AddAskCommand(
            $exchangeId,
            $askId,
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $this->messageBus->dispatch($addAskCommand);

        // check database
        /** @var ExchangeReadRepositoryInterface $readRepo */
        $readRepo = $this->container->get(ExchangeReadRepositoryInterface::class);
        $exchange = $readRepo->findById($exchangeId->toString());

        $this->assertCount(1, $exchange->asks());
        $this->assertInstanceOf(Ask::class, $exchange->asks()->findById($askId));
    }

    public function testItDispatchesAskAddedDomainEvent()
    {
        // create exchange
        $exchangeId = Uuid::uuid4();
        $createExchangeCommand = new CreateExchangeCommand($exchangeId);
        $this->messageBus->dispatch($createExchangeCommand);

        // add ask to exchange
        $askId = Uuid::uuid4();
        $addAskCommand = new AddAskCommand(
            $exchangeId,
            $askId,
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $this->messageBus->dispatch($addAskCommand);

        /* @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async');
        $this->assertCount(2, $transport->getSent());
        $this->assertInstanceOf(AskAdded::class, $transport->getSent()[1]->getMessage());
    }

    public function testItDispatchesAnAddRemovedEventAndABidRemovedEventAndAATradeExecutedEvent()
    {
        // create exchange
        $exchangeId = Uuid::uuid4();
        $createExchangeCommand = new CreateExchangeCommand($exchangeId);
        $this->messageBus->dispatch($createExchangeCommand);

        // add ask to exchange
        $askId = Uuid::uuid4();
        $addAskCommand = new AddAskCommand(
            $exchangeId,
            $askId,
            Uuid::uuid4(),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $this->messageBus->dispatch($addAskCommand);

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

        /* @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async');
        $this->assertCount(6, $transport->getSent());
        $this->assertInstanceOf(BidRemoved::class, $transport->getSent()[3]->getMessage());
        $this->assertInstanceOf(AskRemoved::class, $transport->getSent()[4]->getMessage());
        $this->assertInstanceOf(TradeExecuted::class, $transport->getSent()[5]->getMessage());
    }
}
