<?php

namespace EavBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EavUnsupportedAttributesConstraint extends Constraint
{
    public $message = 'Attributes: "{{ attributes }}" are not supported by this entity type!';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
