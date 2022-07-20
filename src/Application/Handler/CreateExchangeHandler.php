<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Domain\Exchange;
use StockExchange\Domain\ExchangeWriteRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateExchangeHandler implements MessageHandlerInterface
{
    private MessageBusInterface              $messageBus;
    private ExchangeWriteRepositoryInterface $exchangeWriteRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeWriteRepositoryInterface $exchangeWriteRepository
    ) {
        $this->messageBus = $messageBus;
        $this->exchangeWriteRepository = $exchangeWriteRepository;
    }

    public function __invoke(CreateExchangeCommand $command)
    {
        $exchange = Exchange::create($command->id());

        $this->exchangeWriteRepository->store($exchange);

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        return $exchange; // TODO: remove this
    }
}
