<?php

namespace StockExchange\Domain\Event;

use Ramsey\Uuid\UuidInterface;

class BidRemoved extends Event
{
    private UuidInterface $bidId;

    /**
     * RemoveBidFromExchange constructor.
     *
     * @param UuidInterface $bidId
     */
    public function __construct(UuidInterface $bidId)
    {
        $this->init();
        $this->setPayload(['bidId' => $bidId->toString()]);
        $this->bidId = $bidId;
    }

    /**
     * @return UuidInterface
     */
    public function bidId(): UuidInterface
    {
        return $this->bidId;
    }
}
