<?php

namespace StockExchange\Domain\Event;

use Ramsey\Uuid\UuidInterface;

class AskRemoved extends Event
{
    private UuidInterface $askId;

    /**
     * RemoveAskFromExchange constructor.
     *
     * @param UuidInterface $askId
     */
    public function __construct(UuidInterface $askId)
    {
        $this->init();
        $this->setPayload(['askId' => $askId]);
        $this->askId = $askId;
    }

    /**
     * @return UuidInterface
     */
    public function askId(): UuidInterface
    {
        return $this->askId;
    }
}
