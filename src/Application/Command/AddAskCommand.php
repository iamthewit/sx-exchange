<?php

namespace StockExchange\Application\Command;

use Ramsey\Uuid\UuidInterface;
use StockExchange\Domain\Price;
use StockExchange\Domain\Symbol;

class AddAskCommand
{
    private UuidInterface $exchangeId;
    private UuidInterface $id;
    private UuidInterface $traderId;
    private Symbol $symbol;
    private Price $price;

    /**
     * CreateAskCommand constructor.
     *
     * @param UuidInterface $exchangeId
     * @param UuidInterface $id
     * @param UuidInterface $traderId
     * @param Symbol        $symbol
     * @param Price         $price
     */
    public function __construct(
        UuidInterface $exchangeId,
        UuidInterface $id,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ) {
        $this->exchangeId = $exchangeId;
        $this->id = $id;
        $this->traderId = $traderId;
        $this->symbol = $symbol;
        $this->price = $price;
    }

    /**
     * @return UuidInterface
     */
    public function exchangeId(): UuidInterface
    {
        return $this->exchangeId;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return UuidInterface
     */
    public function traderId(): UuidInterface
    {
        return $this->traderId;
    }

    /**
     * @return Symbol
     */
    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    /**
     * @return Price
     */
    public function price(): Price
    {
        return $this->price;
    }
}
