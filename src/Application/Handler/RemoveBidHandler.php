<?php


namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\RemoveBidCommand;
use StockExchange\Domain\ExchangeReadRepositoryInterface;
use StockExchange\Domain\ExchangeWriteRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class RemoveAskFromExchangeHandler
 * @package StockExchange\Application\Exchange\Handler
 */
class RemoveBidHandler implements MessageHandlerInterface
{
    private MessageBusInterface              $messageBus;
    private ExchangeReadRepositoryInterface  $exchangeReadRepository;
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

    public function __invoke(RemoveBidCommand $command)
    {
        $exchange = $this->exchangeReadRepository->findById($command->exchangeId()->toString());

        $exchange->removeBid($command->id());

        $this->exchangeWriteRepository->store($exchange);

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        return $exchange; // TODO: remove this
    }
}