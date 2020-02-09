<?php

namespace AppBundle\SearchRepository;

class GarageItemRepository extends AbstractSearchRepository implements SearchRepositoryInterface
{
    private const FIELD_NUMBER = 'number';
    private const FIELD_PRICE = 'price_query';
    private const FIELD_RESERVATION = 'orderProducts.reservationId';
    private const FIELD_STATUS = 'reservationStatus';
    private const FIELD_NOTE = 'note';

    protected $queryFields = [
        self::FIELD_NUMBER,
        self::FIELD_PRICE,
        self::FIELD_RESERVATION,
        self::FIELD_NOTE,
        self::FIELD_STATUS,
    ];

    protected $arrayFields = [self::FIELD_TYPE, self::FIELD_STATUS];
}
