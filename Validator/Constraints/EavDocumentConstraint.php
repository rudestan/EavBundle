<?php

namespace EavBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EavDocumentConstraint extends Constraint
{
    public $message = 'The document constraint class "{{ class }}" not found!';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
