<?php

namespace StockExchange\Domain\Event;

use StockExchange\Domain\Trade;

class TradeExecuted extends Event
{
    private Trade $trade;

    /**
     * TradeExecuted constructor.
     * @param Trade $trade
     */
    public function __construct(Trade $trade)
    {
        $this->init();
        $this->setPayload($trade->toArray());
        $this->trade = $trade;
    }

    /**
     * @return Trade
     */
    public function trade(): Trade
    {
        return $this->trade;
    }
}
