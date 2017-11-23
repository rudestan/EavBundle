<?php

namespace EavBundle\Service;

use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavDocument;
use EavBundle\Entity\EavValue;

/**
 * Class EavValueTypeCast
 */
class EavValueTypeCast
{
    /**
     * Casting one value to appropriate type
     *
     * @param EavValue $valueEntity
     */
    public function typeCastValue($valueEntity)
    {
        $attr = $valueEntity->getAttribute();

        if (!$attr instanceof EavAttribute) {
            return;
        }

        $type  = $attr->getType();
        $value = $this->getTypeCastedValue($type, $valueEntity->getValue());

        $valueEntity->setValue($value);
    }

    /**
     * Casting value to specified type
     *
     * @param string $type
     * @param mixed  $value
     *
     * @return bool|float|int|string|array|null
     */
    protected function getTypeCastedValue($type, $value)
    {
        if (is_null($value)) {
            return null;
        }

        if ($type == 'array') {
            $value = json_decode($value, true);
        }

        settype($value, $type);

        return $value;
    }

    /**
     * Type casts value for storing in DB
     *
     * @param EavValue $eavValue
     *
     * @return EavValue
     */
    public function typeCastValueForWriting(EavValue $eavValue): EavValue
    {
        $attribute = $eavValue->getAttribute();
        $type      = $attribute->getType();
        $value     = $eavValue->getValue();

        if ($type == 'array') {
            $eavValue->setValue(json_encode($value));
        }

        return $eavValue;
    }

    /**
     * Type casts an array of documents for storing in DB
     *
     * @param array $eavDocuments
     */
    public function typeCastDocumentsForWriting(array $eavDocuments)
    {
        foreach ($eavDocuments as $eavDocument) {
            $this->typeCastDocumentForWriting($eavDocument);
        }
    }

    /**
     * Type casts document for storing in the db
     *
     * @param EavDocument $eavDocument
     *
     * @return EavDocument
     */
    public function typeCastDocumentForWriting(EavDocument $eavDocument): EavDocument
    {
        foreach ($eavDocument->getValues() as $eavValue) {
            $this->typeCastValueForWriting($eavValue);
        }

        return $eavDocument;
    }
}
