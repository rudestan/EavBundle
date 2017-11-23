<?php

namespace EavBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EavUniqueDocumentConstraint extends Constraint
{
    /**
     * @var array
     */
    public $attributes;

    /**
     * @var string
     */
    public $message = 'The EAV Document is not unique!';

    /**
     * @return array
     */
    public function getRequiredOptions()
    {
        return ['attributes'];
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
