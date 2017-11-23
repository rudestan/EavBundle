<?php

namespace EavBundle\Validator\Constraints;

use EavBundle\Entity\EavDocument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EavDocumentConditionalNotRequiredConstraintValidator
 */
class EavDocumentConditionalNotRequiredConstraintValidator extends ConstraintValidator
{
    /**
     * Validates the EavDocument entity for required attributes depending on condition
     *
     * @param EavDocument $entity
     * @param Constraint  $constraint
     *
     * @return mixed
     */
    public function validate($entity, Constraint $constraint)
    {
        $checkValue = $entity->getValue($constraint->checkAttribute);

        if (!$checkValue || $checkValue->getValue() === null || $checkValue->getValue() === $constraint->value) {
            return false;
        }

        $requiredValue = $entity->getValue($constraint->requiredAttribute);

        if ($requiredValue && !empty($requiredValue->getValue())) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setParameters([
                '{{ check }}'    => $constraint->checkAttribute,
                '{{ value }}'    => $constraint->value,
                '{{ required }}' => $constraint->requiredAttribute,
            ])
            ->addViolation();
    }
}
