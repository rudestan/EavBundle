<?php

namespace EavBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use EavBundle\Entity\EavDocument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniqueDocumentConstraintValidator
 */
class EavUniqueDocumentConstraintValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * EavUniqueDocumentConstraintValidator constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Validates the document for uniqueness
     *
     * @param EavDocument $entity
     * @param Constraint  $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        $criteria = $this->getQueryCriteria($entity, $constraint->attributes);

        if (empty($criteria)) {
            return;
        }

        if (!$this->isDocumentUnique($entity, $criteria)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    /**
     * Returns criteria for unique check query
     *
     * @param EavDocument $entity
     * @param array       $attributes
     *
     * @return array
     */
    protected function getQueryCriteria(EavDocument $entity, array $attributes)
    {
        $criteria = [];

        foreach ($attributes as $name) {
            $valueEntity = $entity->getValue($name);

            if ($valueEntity === null) {
                continue;
            }

            $criteria[$name] = $valueEntity->getValue();
        }

        return $criteria;
    }

    /**
     * Checks whether document is unique
     *
     * @param EavDocument $entity
     * @param array       $criteria
     *
     * @return bool
     */
    protected function isDocumentUnique(EavDocument $entity, array $criteria)
    {
        $typeId     = $entity->getType()->getId();
        $entityId   = $entity->getId();
        $excludeIds = [];

        if ($entityId) {
            $excludeIds[] = $entityId;
        }

        $repository = $this->em->getRepository('EavBundle:EavDocument');
        $documents  = $repository->findByTypeIdAndCriteria($typeId, $criteria, $excludeIds);

        return count($documents) == 0;
    }
}
