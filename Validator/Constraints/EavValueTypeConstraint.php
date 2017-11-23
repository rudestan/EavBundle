<?php

namespace EavBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EavValueTypeConstraint extends Constraint
{
    public $message = 'The type of "{{ name }}" attribute must be "{{ expected_type }}". Got "{{ type }}".';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
