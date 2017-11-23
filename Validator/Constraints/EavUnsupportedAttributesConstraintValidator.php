<?php

namespace EavBundle\Validator\Constraints;

use EavBundle\Entity\EavDocument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EavUnsupportedAttributesConstraintValidator
 */
class EavUnsupportedAttributesConstraintValidator extends ConstraintValidator
{
    /**
     * Validates the EavDocument for any unsupported attribute
     *
     * @param EavDocument $entity
     * @param Constraint  $constraint
     *
     * @return mixed
     */
    public function validate($entity, Constraint $constraint)
    {
        $attributes  = $entity->getType()->getAttributes();
        $entityAttrs = $entity->getAttributeNames();

        if (empty($attributes) || empty($entityAttrs)) {
            return;
        }

        $unsupportedAttrs = array_diff($entityAttrs, array_keys($attributes));

        if (empty($unsupportedAttrs)) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ attributes }}', implode(', ', $unsupportedAttrs))
            ->atPath('attributes')
            ->addViolation();
    }
}
