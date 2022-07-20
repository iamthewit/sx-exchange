<?php


namespace StockExchange\Infrastructure\Persistence;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\Domain\EventWriteRepositoryInterface;

/**
 * Class NullEventWriteRepository
 * @package StockExchange\Infrastructure\Persistence
 */
class NullEventWriteRepository implements EventWriteRepositoryInterface
{
    public function storeEvent(DomainEvent $event): void
    {
    }
}