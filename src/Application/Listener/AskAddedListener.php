<?php


namespace StockExchange\Application\Listener;

//use StockExchange\StockExchange\BidAsk\Event\AskAdded;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class AskAddedListener
 * @package StockExchange\Application\Listener
 *
 * This class does not need to exist since teh exchange is responsible for adding/removing asks and bids
 * Previously the Exchange listened to the BidAsk context - but this is no longer the case (it's actually teh other way around now)
 */
class AskAddedListener implements MessageHandlerInterface
{
    use HandleTrait;

    /**
     * AskAddedListener constructor.
     */
    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(\stdClass $param){}

//    public function __invoke(AskAdded $event)
//    {
//        $this->handle(
//            new AddAskToExchangeCommand(
//                Uuid::fromString($event->payload()['exchangeId']),
//                Uuid::fromString($event->payload()['id']),
//                Uuid::fromString($event->payload()['traderId']),
//                Symbol::fromValue($event->payload()['symbol']['value']),
//                Price::fromValue($event->payload()['price']['value'])
//            )
//        );
//    }
}