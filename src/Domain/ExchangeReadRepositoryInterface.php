<?php

namespace StockExchange\Domain;

use Ramsey\Uuid\UuidInterface;
use StockExchange\Domain\Exception\ExchangeNotFoundException;

interface ExchangeReadRepositoryInterface
{
    /**
     * @param string $id
     *
     * @return Exchange
     *
     * @throws ExchangeNotFoundException
     */
    public function findById(string $id): Exchange;
}
