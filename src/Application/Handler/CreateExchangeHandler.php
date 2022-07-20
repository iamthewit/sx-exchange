<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Domain\Exchange;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateExchangeHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateExchangeCommand $command)
    {
        $exchange = Exchange::create($command->id());
        // TODO: store exchange

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        return $exchange; // TODO: remove this
    }
}
