<?php

namespace AppBundle\SearchRepository;

use App\Entity\GarageGroup;
use Elastica\Query;

interface SearchRepositoryInterface
{
    public function search(GarageGroup $garageGroup, array $sort, array $parameters = []): Query;
}
