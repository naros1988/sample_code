<?php

namespace App\Service\ElasticaModelTransformer;

use App\Entity\GarageItemStorageSpot;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;

class GarageItemTransformer extends ModelToElasticaAutoTransformer
{
    protected function transformObjectToDocument($object, array $fields, $identifier = '')
    {
        $document = parent::transformObjectToDocument($object, $fields, $identifier);

        if ($object instanceof GarageItemStorageSpot) {
            return $this->setDocumentStorageType($object, $document);
        } else {
            return $this->setDocumentNullStorageType($document);
        }
    }

    private function setDocumentStorageType(GarageItemStorageSpot $storage, Document $document): Document
    {
        $storageType = $storage->getStorageType();
        if (GarageItemStorageSpot::STORAGE_TYPE_NORMAL === $storageType) {
            $document->set(GarageItemStorageSpot::STORAGE_TYPE_NORMAL, true);
            $document->set(GarageItemStorageSpot::STORAGE_TYPE_BOX, false);
        } else {
            $document->set(GarageItemStorageSpot::STORAGE_TYPE_NORMAL, false);
            $document->set(GarageItemStorageSpot::STORAGE_TYPE_BOX, true);
        }

        return $document;
    }

    private function setDocumentNullStorageType(Document $document): Document
    {
        $document->set(GarageItemStorageSpot::STORAGE_TYPE_NORMAL, null);
        $document->set(GarageItemStorageSpot::STORAGE_TYPE_BOX, null);

        return $document;
    }
}
