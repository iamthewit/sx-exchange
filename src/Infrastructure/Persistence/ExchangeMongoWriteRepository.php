<?php

namespace StockExchange\Infrastructure\Persistence;

use DateTimeInterface;
use MongoDB\Client;
use StockExchange\Domain\Exchange;
use StockExchange\Domain\ExchangeWriteRepositoryInterface;

class ExchangeMongoWriteRepository implements ExchangeWriteRepositoryInterface
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

    public function store(Exchange $exchange): void
    {
        $databaseName = $this->databaseName;
        $collection = $this->client->$databaseName->exchanges;

        $collection->updateOne(
            ['_id' => $exchange->id()->toString()],
            ['$set' => $this->createExchangeArray($exchange)],
            ['upsert' => true]
        );
    }

    /**
     * @param Exchange $exchange
     *
     * @return array
     */
    protected function createExchangeArray(Exchange $exchange): mixed
    {
        return array_merge(
            ['_id' => $exchange->id()->toString()],
            json_decode(json_encode($exchange), true),
            [
                'last_applied_event' => array_merge(
                    $exchange->lastAppliedEvent()->toArray(),
                    [
                        'created_at' => $exchange
                            ->lastAppliedEvent()
                            ->createdAt()
                            ->format(DateTimeInterface::ISO8601)
                    ]
                )
            ]
        );
    }
}