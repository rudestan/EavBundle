<?php

namespace EavBundle\Validator\Constraints;

use EavBundle\Entity\EavDocument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EavRequiredAttributesConstraintValidator
 */
class EavRequiredAttributesConstraintValidator extends ConstraintValidator
{
    /**
     * Validates the EavDocument entity for required attributes
     *
     * @param EavDocument $entity
     * @param Constraint  $constraint
     *
     * @return mixed
     */
    public function validate($entity, Constraint $constraint)
    {
        $attributes = $entity->getType()->getAttributes();

        if (empty($attributes)) {
            return;
        }

        foreach ($attributes as $name => $options) {
            $required = isset($options['required']) ? $options['required'] : false;

            if (!$required) {
                continue;
            }

            $valueEntity = $entity->getValue($name);

            if (!$valueEntity) {
                if (isset($options['alias'])) {
                    $name = $options['alias'] . ' (' . $name . ')';
                }

                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ param }}', $name)
                    ->atPath($name)
                    ->addViolation();
            }
        }
    }
}
