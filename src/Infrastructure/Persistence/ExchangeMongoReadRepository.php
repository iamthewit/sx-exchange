<?php

namespace StockExchange\Infrastructure\Persistence;

use MongoDB\Client;
use StockExchange\Domain\Exchange;
use StockExchange\Domain\ExchangeReadRepositoryInterface;

class ExchangeMongoReadRepository implements ExchangeReadRepositoryInterface
{
    private Client $client;
    private string $databaseName;

    /**
     * @param Client $client
     */
    public function __construct(Client $client, string $databaseName)
    {
        $this->client = $client;
        $this->databaseName = $databaseName;
    }

    /**
     * @inheritDoc
     */
    public function findById(string $id): Exchange
    {
        $databaseName = $this->databaseName;

        $collection = $this->client->$databaseName->exchanges;

        $result = $collection->findOne(
            ['_id' => $id],
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
        );

        return Exchange::restoreFromArray($result);
    }
}