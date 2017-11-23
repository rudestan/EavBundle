<?php

namespace EavBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use EavBundle\Repository\EavAttributeRepository;
use EavBundle\Service\EavDocumentProcessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class EavValueCollectionConstraintValidator
 */
class EavValueCollectionConstraintValidator extends ConstraintValidator
{
    /**
     * @var EavDocumentProcessor
     */
    protected $documentProcessor;

    /**
     * @var EavAttributeRepository
     */
    protected $eavAttrRepo;

    /**
     * EavValueCollectionConstraintValidator constructor.
     *
     * @param EntityManager        $em
     * @param EavDocumentProcessor $documentProcessor
     */
    public function __construct(EntityManager $em, EavDocumentProcessor $documentProcessor)
    {
        $this->eavAttrRepo       = $em->getRepository('EavBundle:EavAttribute');
        $this->documentProcessor = $documentProcessor;
    }

    /**
     * Validates value collection
     *
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        foreach ($value as $entity) {
            $this->addViolationsToCurrentContext(
                $this->documentProcessor->processValueEntity($entity, $this->eavAttrRepo)
            );
        }
    }

    /**
     * Adds all passed violations to the current context
     *
     * @param ConstraintViolationListInterface $violations
     */
    protected function addViolationsToCurrentContext(ConstraintViolationListInterface $violations)
    {
        foreach ($violations as $violation) {
            $this->context->setConstraint($violation->getConstraint());
            $this->context
                ->buildViolation($violation->getMessage(), $violation->getParameters())
                ->atPath($violation->getPropertyPath())
                ->addViolation();
        }
    }
}
