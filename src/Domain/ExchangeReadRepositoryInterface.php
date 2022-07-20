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

    public function findExchangeById(string $id): Exchange; // TODO: this one can replace the first - two methods exist because of the transition to smaller aggregates

//    public function findShareById(string $id): Share; // TODO: not needed by this context... ?

//    public function findShareIdsBySymbolAndTraderId(Symbol $symbol, UuidInterface $traderId): array;

//    public function findAskById(string $id): \StockExchange\StockExchange\BidAsk\Ask;

//    public function findBidById(string $id): \StockExchange\StockExchange\BidAsk\Bid;
}
