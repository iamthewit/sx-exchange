<?php


namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\AddBidToExchangeCommand;
use StockExchange\Domain\ExchangeReadRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class AddBidToExchangeHandler
 * @package StockExchange\Application\Exchange\Handler
 */
class AddBidToExchangeHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;
    private ExchangeReadRepositoryInterface $exchangeReadRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeReadRepositoryInterface $exchangeReadRepository,
    ) {
        $this->messageBus = $messageBus;
        $this->exchangeReadRepository = $exchangeReadRepository;
    }

    public function __invoke(AddBidToExchangeCommand $command)
    {
        $exchange = $this->exchangeReadRepository->findById($command->exchangeId()->toString());

        $exchange->bid(
            $command->id(),
            $command->traderId(),
            $command->symbol(),
            $command->price()
        );

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        return $exchange; // TODO: remove this
    }
}