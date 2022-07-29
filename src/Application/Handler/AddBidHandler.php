<?php


namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\AddBidCommand;
use StockExchange\Domain\ExchangeReadRepositoryInterface;
use StockExchange\Domain\ExchangeWriteRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class AddBidToExchangeHandler
 * @package StockExchange\Application\Exchange\Handler
 */
class AddBidHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;
    private ExchangeReadRepositoryInterface $exchangeReadRepository;
    private ExchangeWriteRepositoryInterface $exchangeWriteRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeReadRepositoryInterface $exchangeReadRepository,
        ExchangeWriteRepositoryInterface $exchangeWriteRepository
    ) {
        $this->messageBus = $messageBus;
        $this->exchangeReadRepository = $exchangeReadRepository;
        $this->exchangeWriteRepository = $exchangeWriteRepository;
    }

    public function __invoke(AddBidCommand $command)
    {
        $exchange = $this->exchangeReadRepository->findById($command->exchangeId()->toString());

        $exchange->bid(
            $command->id(),
            $command->traderId(),
            $command->symbol(),
            $command->price()
        );

        $this->exchangeWriteRepository->store($exchange);

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        return $exchange; // TODO: remove this
    }
}