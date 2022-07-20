<?php

namespace StockExchange\Application\Listener;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\Domain\EventWriteRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class StockExchangeEventListener implements MessageHandlerInterface
{
    private EventWriteRepositoryInterface $exchangeEventWriteRepository;

    public function __construct(EventWriteRepositoryInterface $exchangeEventWriteRepository)
    {
        $this->exchangeEventWriteRepository = $exchangeEventWriteRepository;
    }

    public function __invoke(DomainEvent $event): void
    {
        $this->exchangeEventWriteRepository->storeEvent($event);
    }
}
