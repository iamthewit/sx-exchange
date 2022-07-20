<?php

namespace StockExchange\Infrastructure\Persistence;

use MongoDB\Client;
use Ramsey\Uuid\UuidInterface;
use StockExchange\Domain\Exchange;
use StockExchange\Domain\ExchangeReadRepositoryInterface;
use StockExchange\Domain\Symbol;

class ExchangeMongoReadRepository implements ExchangeReadRepositoryInterface
{
    private Client $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function findById(string $id): Exchange
    {
        $collection = $this->client->stock_exchange->exchanges;

        $result = $collection->findOne(
            ['_id' => $id],
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
        );

        return Exchange::restoreFromValues($result);
    }

    public function findExchangeById(string $id): Exchange
    {
        // TODO: Implement findExchangeById() method.
    }

//    public function findShareById(string $id): \StockExchange\StockExchange\Share\Share
//    {
//        // TODO: Implement findShareById() method.
//    }

//    public function findAskById(string $id): \StockExchange\StockExchange\BidAsk\Ask
//    {
//        // TODO: Implement findAskById() method.
//    }

//    public function findBidById(string $id): \StockExchange\StockExchange\BidAsk\Bid
//    {
//        // TODO: Implement findBidById() method.
//    }

//    public function findShareIdsBySymbolAndTraderId(Symbol $symbol, UuidInterface $traderId): array
//    {
//        // TODO: Implement findShareBySymbolAndTraderId() method.
//    }
}