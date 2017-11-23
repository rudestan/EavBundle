<?php

namespace EavBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use EavBundle\Entity\EavDocument;
use EavBundle\Entity\EavValue;
use EavBundle\Exception\EavDocumentValidationException;
use EavBundle\Service\EavDocumentProcessor;
use EavBundle\Service\EavValueTypeCast;

/**
 * Class EavDocumentSubscriber
 */
class EavDocumentSubscriber implements EventSubscriber
{
    /**
     * @var EavDocumentProcessor
     */
    protected $processor;

    /**
     * @var EavValueTypeCast
     */
    protected $typeCastService;

    /**
     * EavDocumentSubscriber constructor
     *
     * @param EavDocumentProcessor $processor
     * @param EavValueTypeCast     $typeCastService
     */
    public function __construct(EavDocumentProcessor $processor, EavValueTypeCast $typeCastService)
    {
        $this->processor       = $processor;
        $this->typeCastService = $typeCastService;
    }

    /**
     * Returns subscribed events
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::onFlush,
            Events::postPersist,
            Events::postUpdate,
            Events::postLoad,
        ];
    }

    /**
     * PrePersist event listener
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->preProcessEvent($args);
    }

    /**
     * PreUpdate event listener
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->preProcessEvent($args);
    }

    /**
     * OnFlush event listener. Triggered once on flushing all changes. Collects the documents scheduled
     * for changes and validates them. Throws an exception in case there is any invalid document in the change set
     * and clears the UnitOfWork to not keep the old violations in the future flush calls for the same entities
     *
     * @param OnFlushEventArgs $args
     *
     * @throws EavDocumentValidationException
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em         = $args->getEntityManager();
        $uow        = $em->getUnitOfWork();
        $repository = $em->getRepository('EavBundle:EavAttribute');

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions()
        );

        if (!$this->hasSubscribedEntities($entities)) {
            return;
        }

        $eavDocuments      = $this->getDocumentsScheduledForValidation($entities, $uow);
        $areDocumentsValid = $this->processor->validateDocumentEntities($eavDocuments, $repository);

        $this->processor->resetProcessedEntities();

        if (!$areDocumentsValid) {
            /*
             * Clear scheduled lists in case of any violations and throw an exception. If not perform cleaning then
             * same entities that were already scheduled for persistence will be iterated again.
             */
            $uow->clear();

            throw new EavDocumentValidationException(
                'Validation errors, please call EavDocument::getLastViolations() to get the violation list!'
            );
        }

        $this->typeCastService->typeCastDocumentsForWriting($eavDocuments);
        $this->recomputeAllValuesChangeSet($eavDocuments, $em);
    }

    /**
     * PostPersist event Listener
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->postProcessEvent($args);
    }

    /**
     * PostUpdate event Listener
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->postProcessEvent($args);
    }

    /**
     * PostLoad event listener
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $this->postProcessEvent($args);
    }

    /**
     * Processes the event for subscribed entities
     *
     * @param LifecycleEventArgs $args
     */
    public function preProcessEvent(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$this->isValueEntity($entity)) {
            return;
        }

        $repository = $args->getEntityManager()->getRepository('EavBundle:EavAttribute');

        $this->processor->hydrateValueAttribute($entity, $repository);
    }

    /**
     * Post processes the event for subscribed entities
     *
     * @param LifecycleEventArgs $args
     */
    public function postProcessEvent(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$this->isValueEntity($entity)) {
            return;
        }

        $this->typeCastService->typeCastValue($entity);
    }

    /**
     * Returns EavDocument entity or null
     *
     * @param object $entity
     *
     * @return EavDocument|null
     */
    public function getDocumentEntity($entity)
    {
        if ($entity instanceof EavDocument) {
            return $entity;
        }

        if ($entity instanceof EavValue) {
            return $entity->getDocument();
        }

        return null;
    }

    /**
     * Recomputes change set of every value of the passed documents
     *
     * @param array         $eavDocuments
     * @param EntityManager $em
     */
    protected function recomputeAllValuesChangeSet(array $eavDocuments, EntityManager $em)
    {
        $uow = $em->getUnitOfWork();

        foreach ($eavDocuments as $eavDocument) {
            foreach ($eavDocument->getValues() as $eavValue) {
                $metaData = $em->getClassMetadata(get_class($eavValue));

                $uow->recomputeSingleEntityChangeSet($metaData, $eavValue);
            }
        }
    }

    /**
     * Collects the parent documents from EavValues for validation. Returns an array of documents
     *
     * @param array      $entities
     * @param UnitOfWork $uow
     *
     * @return array
     */
    protected function getDocumentsScheduledForValidation(array $entities, UnitOfWork $uow)
    {
        $scheduledForValidation = [];

        // process entities
        foreach ($entities as $entity) {
            // process only with EavDocument or EavValue
            if (!$this->isEntitySubscribed($entity)) {
                continue;
            }

            $eavDocument = $this->getDocumentEntity($entity);

            // no need to validate the document that is set for deletion
            if ($uow->isScheduledForDelete($eavDocument)) {
                continue;
            }

            $this->processScheduledEntity($eavDocument, $entity, $uow);

            $documentHash                          = spl_object_hash($eavDocument);
            $scheduledForValidation[$documentHash] = $eavDocument;
        }

        return $scheduledForValidation;
    }

    /**
     * Processes scheduled entity
     *
     * @param EavDocument          $eavDocument
     * @param EavDocument|EavValue $entity
     * @param UnitOfWork           $uow
     */
    protected function processScheduledEntity(EavDocument $eavDocument, $entity, UnitOfWork $uow)
    {
        if (!$this->isValueEntity($entity)) {
            return;
        }

        // remove from values collection of parent document
        if ($uow->isScheduledForDelete($entity)) {
            $eavDocument->removeValue($entity->getAttribute()->getName());
        }

        // update values collection of parent document
        if ($uow->isScheduledForInsert($entity) || $uow->isScheduledForUpdate($entity)) {
            $eavDocument->setRawEavValue($entity);
        }
    }

    /**
     * Returns whether entity is subscribed
     *
     * @param object $entity
     *
     * @return bool
     */
    protected function isEntitySubscribed($entity)
    {
        return $entity instanceof EavValue || $entity instanceof EavDocument;
    }

    /**
     * Returns if entity is an instance of EavValue
     *
     * @param mixed $entity
     *
     * @return bool
     */
    protected function isValueEntity($entity)
    {
        return $entity instanceof EavValue;
    }

    /**
     * Checks an array of entities that it contains the subscribed entities
     *
     * @param array $entities
     *
     * @return bool
     */
    protected function hasSubscribedEntities(array $entities)
    {
        foreach ($entities as $entity) {
            if ($this->isEntitySubscribed($entity)) {
                return true;
            }
        }

        return false;
    }
}
