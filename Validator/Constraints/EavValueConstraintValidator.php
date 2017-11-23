<?php

namespace EavBundle\Validator\Constraints;

use EavBundle\Entity\EavAttribute;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Class EavValueConstraintValidator
 */
class EavValueConstraintValidator extends AbstractEntityConstraintValidator
{
    /**
     * @return array
     *
     * @throws ValidatorException
     */
    protected function getValidators()
    {
        $attribute = $this->entity->getAttribute();

        if (!$attribute instanceof EavAttribute) {
            throw new ValidatorException('The attribute must be an instance of EavAttribute class');
        }

        return $attribute->getValidators();
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        return $this->entity->getAttribute()->getName();
    }

    /**
     * @return object
     */
    protected function getValueToValidate()
    {
        return $this->entity->getValue();
    }
}
