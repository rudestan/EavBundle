<?php

namespace EavBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EavDocumentConditionalNotRequiredConstraint extends Constraint
{
    /**
     * @var string
     */
    public $checkAttribute;

    /**
     * @var string
     */
    public $requiredAttribute;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var string
     */
    public $message = 'The conditional ("{{ check }} != {{ value }}") required param "{{ required }}" is not set!';

    /**
     * @return array
     */
    public function getRequiredOptions()
    {
        return ['checkAttribute', 'requiredAttribute', 'value'];
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
