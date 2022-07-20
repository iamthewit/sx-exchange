<?php

namespace StockExchange\Domain;

interface ExchangeWriteRepositoryInterface
{
    public function store(Exchange $exchange): void;
}