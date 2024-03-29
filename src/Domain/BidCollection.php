<?php

declare(strict_types=1);

namespace StockExchange\Domain;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Ramsey\Uuid\UuidInterface;
use StockExchange\Domain\Exception\BidCollectionCreationException;

/**
 * Class BidCollection
 * @package StockExchange\StockExchange
 *
 * @implements IteratorAggregate<string, Bid>
 */
class BidCollection implements IteratorAggregate, Countable, JsonSerializable
{
    /**
     * @var Bid[]
     */
    private array $bids;

    /**
     * Images constructor.
     * @param Bid[] $bids
     * @throws BidCollectionCreationException
     */
    public function __construct(array $bids)
    {
        $this->bids = [];

        foreach ($bids as $bid) {
            if (!is_a($bid, Bid::class)) {
                throw new BidCollectionCreationException(
                    'Can only create a BidCollection from an array of Bid objects.'
                );
            }

            $this->bids[$bid->id()->toString()] = $bid;
        }
    }

    /**
     * @return Bid[]
     */
    public function toArray(): array
    {
        return array_map(
            function(Bid $bid) {
                return $bid->toArray();
            },
            $this->bids
        );
    }

    /**
     * @return ArrayIterator<string, Bid>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->bids);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->bids);
    }

    /**
     * @return Bid[]
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param Symbol $symbol
     * @param Price  $price
     *
     * @return BidCollection
     * @throws BidCollectionCreationException
     */
    public function filterBySymbolAndPrice(Symbol $symbol, Price $price): BidCollection
    {
        return new self(
            array_filter($this->bids, function (Bid $bid) use ($symbol, $price) {
                return $bid->symbol()->value() === $symbol->value()
                    && $bid->price()->value() === $price->value();
            })
        );
    }

    public function findById(UuidInterface $id): Bid
    {
        return $this->bids[$id->toString()];
    }
}
