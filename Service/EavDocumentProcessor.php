<?php

namespace EavBundle\Service;

use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavDocument;
use EavBundle\Entity\EavPromisedAttribute;
use EavBundle\Entity\EavValue;
use EavBundle\Exception\EavAttributeNotFoundException;
use EavBundle\Exception\EavPromisedAttributeNotSetException;
use EavBundle\Repository\EavAttributeRepository;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class EavDocumentProcessor
 */
class EavDocumentProcessor
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var array
     */
    protected $processedEntities = [];

    /**
     * EavDocumentProcessor constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Hydrates the attribute of each EavValue
     *
     * @param EavValue               $entity
     * @param EavAttributeRepository $repository
     *
     * @throws EavPromisedAttributeNotSetException|EavAttributeNotFoundException
     */
    public function hydrateValueAttribute(EavValue $entity, $repository)
    {
        $name = null;
        $attr = $entity->getAttribute();

        if ($attr instanceof EavAttribute) {
            return;
        }

        if (!$attr instanceof EavPromisedAttribute || !$name = $attr->getName()) {
            throw new EavPromisedAttributeNotSetException('Promised attribute not set!');
        }

        $attribute = $repository->findOneBy(['name' => $name]);

        if (!$attribute) {
            throw new EavAttributeNotFoundException(
                sprintf('Attribute "%s" was not found in the Database!', $name)
            );
        }

        $entity->setAttribute($attribute);
    }

    /**
     * Processes the Eav value entity. Hydrates the attribute and validates it. Returns the list of violations
     * from validation process.
     *
     * @param EavValue               $entity
     * @param EavAttributeRepository $repository
     *
     * @return ConstraintViolationListInterface
     */
    public function processValueEntity(EavValue $entity, $repository)
    {
        $this->hydrateValueAttribute($entity, $repository);

        return $this->validate($entity);
    }

    /**
     * Validates the EavDocument or EavValue
     *
     * @param EavDocument|EavValue $entity
     *
     * @return ConstraintViolationListInterface
     */
    public function validate($entity)
    {
        return $this->validator->validate($entity);
    }

    /**
     * Validates complete document with values. Returns violation list.
     *
     * @param EavDocument            $entity
     * @param EavAttributeRepository $repository
     *
     * @return ConstraintViolationListInterface
     */
    public function validateCompleteDocument(EavDocument $entity, EavAttributeRepository $repository)
    {
        $eavValues  = $entity->getValues();
        $violations = $this->validate($entity);

        foreach ($eavValues as $eavValue) {
            $eavValueViolations = $this->processValueEntity($eavValue, $repository);

            $violations->addAll($eavValueViolations);
        }

        $entity->setLastViolations($violations);
        $this->addProcessedEntity($entity);

        return $violations;
    }

    /**
     * Validates the list of document entities. Returns true whether all entities are valid.
     *
     * @param array                  $documentEntities
     * @param EavAttributeRepository $repository
     *
     * @return bool
     */
    public function validateDocumentEntities(array $documentEntities, EavAttributeRepository $repository)
    {
        $areAllValid = true;

        foreach ($documentEntities as $index => $documentEntity) {
            if (!$documentEntity instanceof EavDocument) {
                continue;
            }

            if ($this->isEntityProcessed($documentEntity) === false) {
                $this->validateCompleteDocument($documentEntity, $repository);
            }

            if ($documentEntity->getLastViolations()->count()) {
                $areAllValid = false;
            }
        }

        return $areAllValid;
    }

    /**
     * Returns whether entity was processed
     *
     * @param object $entity
     *
     * @return bool
     */
    public function isEntityProcessed($entity)
    {
        $hashKey = spl_object_hash($entity);
        $hash    = isset($this->processedEntities[$hashKey]) ? $this->processedEntities[$hashKey] : null;

        return $hash === $this->getEntityHash($entity);
    }

    /**
     * Resets processed entities array
     */
    public function resetProcessedEntities()
    {
        $this->processedEntities = [];
    }

    /**
     * Adds processed entity hash to processed entities array
     *
     * @param object $entity
     */
    protected function addProcessedEntity($entity)
    {
        $hashKey = spl_object_hash($entity);

        $this->processedEntities[$hashKey] = $this->getEntityHash($entity);
    }

    /**
     * Returns entity's hash
     *
     * @param object $entity
     *
     * @return string
     */
    protected function getEntityHash($entity)
    {
        return md5(serialize($entity));
    }
}
