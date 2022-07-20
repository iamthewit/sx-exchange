<?php

namespace StockExchange\Domain\Event;

use StockExchange\Domain\Bid;

class BidAddedToExchange extends Event
{
    private Bid $bid;

    /**
     * BidAdded constructor.
     *
     * @param Bid $bid
     */
    public function __construct(Bid $bid)
    {
        $this->init();
        $this->setPayload($bid->toArray());
        $this->bid = $bid;
    }

    /**
     * @return Bid
     */
    public function bid(): Bid
    {
        return $this->bid;
    }
}
