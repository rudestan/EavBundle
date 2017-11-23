<?php

namespace EavBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EavRequiredAttributesConstraint extends Constraint
{
    public $message = 'The required param "{{ param }}" is not set!';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
