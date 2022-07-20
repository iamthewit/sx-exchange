<?php

namespace StockExchange\Domain;

use StockExchange\Domain\Event\Event;

interface DispatchableEventsInterface
{
    /**
     * @return Event[]
     */
    public function dispatchableEvents(): array;
}
