<?php

namespace AppBundle\SearchRepository;

use App\Entity\GarageGroup;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use FOS\ElasticaBundle\Repository;
use Elastica\Util;

abstract class AbstractSearchRepository extends Repository implements SearchRepositoryInterface
{
    public const QUERY_FIELD = 'query';

    protected const FIELD_TYPE = 'type';
    protected const ARRAY_DELIMITER = ',';

    protected $queryFields = [];
    protected $garageGroupFieldName = 'garageGroup.id';
    protected $arrayFields = [self::FIELD_TYPE];
    protected $featureFieldsPrefix = '';

    private const FIELD_FAMILY = 'family';
    private const FIELD_STANDARD = 'standard';
    private const FIELD_FOR_DISABILITIES_PEOPLE = 'forDisabilitiesPeople';
    private const FIELD_KLAUS = 'klaus';
    private const FIELD_XXL = 'xxl';
    private const FIELD_NORMAL = 'normal';
    private const FIELD_BOX = 'box';

    private const FEATURE_FIELDS = [
        self::FIELD_FAMILY,
        self::FIELD_STANDARD,
        self::FIELD_FOR_DISABILITIES_PEOPLE,
        self::FIELD_KLAUS,
        self::FIELD_XXL,
        self::FIELD_NORMAL,
        self::FIELD_BOX,
    ];

    public function search(GarageGroup $garageGroup, array $sort = [], array $parameters = []): Query
    {
        $parameters[$this->garageGroupFieldName] = $garageGroup->getId();
        $features = $this->getFeaturesFromParameters($parameters);
        $query = new BoolQuery();

        foreach ($parameters as $fieldName => $parameterValue) {
            if (array_key_exists($fieldName, $features)) {
                continue;
            }
            if (self::QUERY_FIELD === $fieldName) {
                $query->addMust($this->createPhraseCondition($parameterValue, $this->queryFields));
                continue;
            }
            if (in_array($fieldName, $this->arrayFields)) {
                if (!is_array($parameterValue)) {
                    $parameterValue = explode(self::ARRAY_DELIMITER, $parameterValue);
                }
                if (1 === count($parameterValue)) {
                    $query->addMust($this->addBoolQuery($parameterValue[0], $fieldName));
                } else {
                    $query->addMust($this->addArrayParametersQuery($fieldName, $parameterValue));
                }
            } else {
                $query->addMust($this->addBoolQuery($parameterValue, $fieldName));
            }
        }

        if (!empty($features)) {
            $query->addMust($this->addFeaturesQuery($features));
        }
        $mainQuery = Query::create($query);
        if (!empty($sort)) {
            $mainQuery->setSort($sort);
        }

        return $mainQuery;
    }

    protected function addArrayParametersQuery(string $fieldName, array $values): BoolQuery
    {
        $query = new BoolQuery();
        foreach ($values as $value) {
            $query->addShould($this->addBoolQuery($value, $fieldName));
        }

        return $query;
    }

    protected function addBoolQuery($parameterValue, string $fieldName): BoolQuery
    {
        $boolQuery = new BoolQuery();
        $boolQuery->addMust(new Query\Term([$fieldName => ['value' => $parameterValue]]));

        return $boolQuery;
    }

    protected function createPhraseCondition(string $phrase, array $fields = []): Query\QueryString
    {
        $searchTerm = Util::escapeTerm($phrase);
        $searchTerm = '*'.$searchTerm.'*';

        $queryString = new Query\QueryString();
        $queryString->setFields($fields);
        $queryString->setQuery($searchTerm);

        return $queryString;
    }

    private function getFeaturesFromParameters(array $parameters): array
    {
        $features = [];
        $featureFields = $this->prepareFeatureFields();
        foreach ($parameters as $fieldName => $parameterValue) {
            if (!in_array($fieldName, $featureFields)) {
                continue;
            }
            $features[$fieldName] = $parameterValue;
        }

        return $features;
    }

    private function addFeaturesQuery(array $features): BoolQuery
    {
        $query = new BoolQuery();
        foreach ($features as $featureName => $value) {
            $query->addShould($this->addBoolQuery($value, $featureName));
        }

        return $query;
    }

    private function prepareFeatureFields(): array
    {
        if (!$this->featureFieldsPrefix) {
            return self::FEATURE_FIELDS;
        }
        $featureFields = [];
        foreach (self::FEATURE_FIELDS as $featureField) {
            $featureFields[] = $this->featureFieldsPrefix.$featureField;
        }

        return $featureFields;
    }
}
