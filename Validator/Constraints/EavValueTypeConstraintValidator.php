<?php

namespace EavBundle\Validator\Constraints;

use EavBundle\Entity\EavValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EavValueConstraintValidator
 */
class EavValueTypeConstraintValidator extends ConstraintValidator
{
    /**
     * Validates the entity type
     *
     * @param EavValue   $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        $value     = $entity->getValue();
        $attribute = $entity->getAttribute();
        $name      = $attribute->getName();
        $type      = $attribute->getType();

        if (is_null($value)) {
            return;
        }

        $currentType = gettype($value);

        if ($currentType !== $type) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameters([
                    '{{ name }}'          => $name,
                    '{{ expected_type }}' => $type,
                    '{{ type }}'          => $currentType,
                ])
                ->atPath($name)
                ->addViolation();
        }
    }
}
