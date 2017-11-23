<?php

namespace EavBundle\Validator\Constraints;

use EavBundle\Entity\EavType;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Class EavDocumentConstraintValidator
 */
class EavDocumentConstraintValidator extends AbstractEntityConstraintValidator
{
    /**
     * @return array
     */
    protected function getValidators()
    {
        $type = $this->entity->getType();

        if (!$type instanceof EavType) {
            throw new ValidatorException('The document type must be an instance of EavType class');
        }

        return $this->entity->getType()->getValidators();
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        return 'document';
    }

    /**
     * @return object
     */
    protected function getValueToValidate()
    {
        return $this->entity;
    }
}
