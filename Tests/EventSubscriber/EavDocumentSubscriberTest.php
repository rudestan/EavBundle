<?php

namespace EavBundle\Tests\EventSubscriber;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavDocument;
use EavBundle\Entity\EavPromisedAttribute;
use EavBundle\Entity\EavType;
use EavBundle\Entity\EavValue;
use EavBundle\EventSubscriber\EavDocumentSubscriber;
use EavBundle\Exception\EavDocumentValidationException;
use EavBundle\Repository\EavAttributeRepository;
use EavBundle\Service\EavDocumentProcessor;
use EavBundle\Service\EavValueTypeCast;
use EavBundle\Tests\EavKernelTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

/**
 * Class EavDocumentSubscriberTest
 */
class EavDocumentSubscriberTest extends EavKernelTestCase
{
    /**
     * @var EavDocumentSubscriber
     */
    protected $sut;

    /**
     * Returns processor prophecy
     *
     * @param EavDocument $returnedEntity
     * @param object      $attrRepoProphecy
     * @param array       $violations
     *
     * @return object|EavDocumentProcessor
     */
    public function getProcessorProphecy($returnedEntity, $attrRepoProphecy, $violations)
    {
        $processorProphecy = $this->prophesize(EavDocumentProcessor::class);
        $processorProphecy->processEntity($returnedEntity, $attrRepoProphecy)
            ->willReturn(new ConstraintViolationList($violations));

        $processorProphecy->validate(Argument::any())
            ->willReturn(new ConstraintViolationList($violations));

        return $processorProphecy->reveal();
    }

    /**
     * Returns type cast service prophecy
     *
     * @return object|EavValueTypeCast
     */
    public function getTypeCastProphecy()
    {
        $typeCastProphecy = $this->prophesize(EavValueTypeCast::class);
        $typeCastProphecy->typeCastValue(Argument::any())->willReturn(null);
        $typeCastProphecy->typeCastDocumentsForWriting(Argument::any())->willReturn(null);

        return $typeCastProphecy->reveal();
    }

    /**
     * Returns entity manager prophecy
     *
     * @param object $attrRepoProphecy
     *
     * @return object|EntityManager
     */
    public function getEntityManagerProphecy($attrRepoProphecy)
    {
        $entityManagerProphecy = $this->prophesize(EntityManager::class);
        $entityManagerProphecy->getRepository('EavBundle:EavAttribute')->willReturn($attrRepoProphecy);

        return $entityManagerProphecy->reveal();
    }

    /**
     * Returns attribute repository prophecy
     *
     * @return object|EavAttributeRepository
     */
    public function getAttributeRepositoryProphecy()
    {
        $attrRepoProphecy = $this->prophesize(EavAttributeRepository::class);

        return $attrRepoProphecy->reveal();
    }

    /**
     * Data provider for document subscriber event test
     *
     * @return array
     */
    public function postLoadEventDataProvider()
    {
        $eavDocument = new EavDocument();

        $eavAttribute = new EavAttribute();
        $eavAttribute
            ->setName('attr_name')
            ->setType('integer');

        $eavValue = new EavValue();
        $eavValue->setDocument($eavDocument);
        $eavValue->setAttribute($eavAttribute);

        return [
            [
                $eavValue,
                '12345',
                'string',
            ],
            [
                $eavValue,
                '678',
                'string',
            ],
            [
                $eavValue,
                789,
                'integer',
            ],
        ];
    }

    /**
     * Tests document event postLoad event
     *
     * @param EavValue $entity
     * @param mixed    $value
     * @param string   $type
     *
     * @dataProvider postLoadEventDataProvider
     */
    public function testPostLoadEvent($entity, $value, $type)
    {
        $processorProphecy     = $this->prophesize(EavDocumentProcessor::class)->reveal();
        $typeCast              = new EavValueTypeCast();
        $entityManagerProphecy = $this->getEntityManagerProphecy($this->getAttributeRepositoryProphecy());

        $args = new LifecycleEventArgs($entity, $entityManagerProphecy);

        $sut = new EavDocumentSubscriber($processorProphecy, $typeCast);

        $entity->setValue($value);

        $this->assertSame(gettype($value), $type);

        $sut->postLoad($args);

        $this->assertSame(gettype($entity->getValue()), $entity->getAttribute()->getType());
    }

    /**
     * Data provider for pre events calls
     *
     * @return array
     */
    public function preEventsDataProvider()
    {
        $eavAttribute = new EavAttribute();
        $eavAttribute->setType('integer');
        $eavAttribute->setName('int_attr');

        $eavPromisedAttribute = new EavPromisedAttribute();
        $eavPromisedAttribute->setName('int_attr');

        $eavValue1 = new EavValue();
        $eavValue1->setAttribute($eavPromisedAttribute);

        $eavValue2 = new EavValue();
        $eavValue2->setAttribute($eavPromisedAttribute);

        return [
            [
                'prePersist',
                $eavValue1,
                $eavAttribute,
            ],
            [
                'preUpdate',
                $eavValue2,
                $eavAttribute,
            ],
        ];
    }

    /**
     * Tests pre events calls
     *
     * @param string       $method
     * @param EavValue     $entity
     * @param EavAttribute $eavAttribute
     *
     * @dataProvider preEventsDataProvider
     */
    public function testPreEvents($method, $entity, $eavAttribute)
    {
        $processor        = new EavDocumentProcessor($this->getValidator());
        $typeCastProphecy = $this->getTypeCastProphecy();

        $attrRepoProphecy = $this->prophesize(EavAttributeRepository::class);
        $attrRepoProphecy->findOneBy(['name' => $eavAttribute->getName()])->willReturn($eavAttribute);

        $entityManagerProphecy = $this->getEntityManagerProphecy($attrRepoProphecy->reveal());

        $args = new LifecycleEventArgs($entity, $entityManagerProphecy);
        $sut  = new EavDocumentSubscriber($processor, $typeCastProphecy);

        $this->assertInstanceOf(EavPromisedAttribute::class, $entity->getAttribute());

        $sut->{$method}($args);

        $this->assertInstanceOf(EavAttribute::class, $entity->getAttribute());
        $this->assertSame($eavAttribute, $entity->getAttribute());
    }

    /**
     * Data provider for onFlush test
     *
     * @return array
     */
    public function onFlushDataProvider()
    {
        // valid case
        $eavType = new EavType();
        $eavType->setAttributes([]);

        $eavDocument = new EavDocument();
        $eavDocument->setType($eavType);

        $eavAttribute = new EavAttribute();
        $eavAttribute->setName('attribute_name');

        $eavValue = new EavValue();
        $eavValue->setDocument($eavDocument);
        $eavValue->setAttribute($eavAttribute);

        // not valid case
        $eavTypeNotValid = new EavType();
        $eavTypeNotValid->setAttributes([
            [
                'customerId' => [
                    'required' => true,
                ],
            ],
        ]);

        $notValidDocument = new EavDocument();
        $notValidDocument->setType($eavTypeNotValid);

        $notValidValue = new EavValue();
        $notValidValue->setDocument($notValidDocument);
        $notValidValue->setAttribute($eavAttribute);

        return [
            [
                [
                    'insert' => [],
                    'update' => [],
                    'delete' => [],
                ],
                [
                    'isForDelete' => null,
                    'isForInsert' => null,
                    'isForUpdate' => null,
                ],
            ],
            [
                [
                    'insert' => [
                        $eavDocument,
                        new EavAttribute(),
                    ],
                    'update' => [],
                    'delete' => [],
                ],
                [
                    'isForDelete' => [
                        [$eavDocument, true],
                    ],
                    'isForInsert' => null,
                    'isForUpdate' => null,
                ],
            ],
            [
                [
                    'insert' => [],
                    'update' => [],
                    'delete' => [
                        $eavDocument,
                    ],
                ],
                [
                    'isForDelete' => [
                        [$eavDocument, true],
                    ],
                    'isForInsert' => null,
                    'isForUpdate' => null,
                ],
            ],
            [
                [
                    'insert' => [],
                    'update' => [],
                    'delete' => [
                        $eavValue,
                    ],
                ],
                [
                    'isForDelete' => [
                        [$eavDocument, true],
                    ],
                    'isForInsert' => null,
                    'isForUpdate' => null,
                ],
            ],
            [
                [
                    'insert' => [],
                    'update' => [],
                    'delete' => [
                        $eavValue,
                    ],
                ],
                [
                    'isForDelete' => [
                        [$eavDocument, false],
                        [$eavValue, true],
                    ],
                    'isForInsert' => [
                        [$eavValue, false],
                    ],
                    'isForUpdate' => [
                        [$eavValue, false],
                    ],
                ],
            ],
            [
                [
                    'insert' => [
                        $eavDocument,
                    ],
                    'update' => [],
                    'delete' => [],
                ],
                [
                    'isForDelete' => [
                        [$eavDocument, false],
                    ],
                    'isForInsert' => null,
                    'isForUpdate' => null,
                ],
            ],
            [
                [
                    'insert' => [
                        $eavValue,
                    ],
                    'update' => [],
                    'delete' => [],
                ],
                [
                    'isForDelete' => [
                        [$eavDocument, false],
                        [$eavValue, false],
                    ],
                    'isForInsert' => [
                        [$eavValue, true],
                    ],
                    'isForUpdate' => null,
                ],
            ],
            [
                [
                    'insert' => [],
                    'update' => [
                        $eavValue,
                    ],
                    'delete' => [],
                ],
                [
                    'isForDelete' => [
                        [$eavDocument, false],
                        [$eavValue, false],
                    ],
                    'isForInsert' => [
                        [$eavValue, false],
                    ],
                    'isForUpdate' => [
                        [$eavValue, true],
                    ],
                ],
            ],
            [
                [
                    'insert' => [],
                    'update' => [
                        $notValidValue,
                    ],
                    'delete' => [],
                ],
                [
                    'isForDelete' => [
                        [$notValidDocument, false],
                        [$notValidValue, false],
                    ],
                    'isForInsert' => [
                        [$notValidValue, false],
                    ],
                    'isForUpdate' => [
                        [$notValidValue, true],
                    ],
                ],
                $notValidDocument,
            ],
        ];
    }

    /**
     * Tests onFlush event
     *
     * @param array $scheduledEntities
     * @param array $isScheduledChecks
     * @param null  $documentEntity
     *
     * @dataProvider onFlushDataProvider
     */
    public function testOnFlushEvent($scheduledEntities, $isScheduledChecks, $documentEntity = null)
    {
        $typeCastProphecy = $this->getTypeCastProphecy();
        $processor        = new EavDocumentProcessor($this->getValidator());
        $metaData         = new ClassMetadata('EavBundle:EavValue');

        $unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);
        $unitOfWorkProphecy->clear()->willReturn(null);
        $unitOfWorkProphecy->getScheduledEntityInsertions()->shouldBeCalledTimes(1)->willReturn($scheduledEntities['insert']);
        $unitOfWorkProphecy->getScheduledEntityUpdates()->shouldBeCalledTimes(1)->willReturn($scheduledEntities['update']);
        $unitOfWorkProphecy->getScheduledEntityDeletions()->shouldBeCalledTimes(1)->willReturn($scheduledEntities['delete']);
        $unitOfWorkProphecy->recomputeSingleEntityChangeSet($metaData, Argument::any())->willReturn(null);

        $this->setScheduledCheckEntities($isScheduledChecks, $unitOfWorkProphecy);

        $emProphecy = $this->prophesize(EntityManager::class);
        $emProphecy->getRepository('EavBundle:EavAttribute')->willReturn($this->getAttributeRepositoryProphecy());
        $emProphecy->getUnitOfWork()->willReturn($unitOfWorkProphecy->reveal());
        $emProphecy->getClassMetadata(Argument::any())->willReturn($metaData);

        $sut = new EavDocumentSubscriber($processor, $typeCastProphecy);

        $args = new OnFlushEventArgs($emProphecy->reveal());

        try {
            $sut->onFlush($args);
        } catch (\Exception $e) {
            $this->assertInstanceOf(EavDocumentValidationException::class, $e);

            $violations = $documentEntity->getLastViolations();

            $this->assertCount(1, $violations);
            $this->assertContains(
                'Attributes: "attribute_name" are not supported by this entity type!',
                (string) $violations
            );
        }
    }

    /**
     * Data provider for testGetDocumentEntity
     *
     * @return array
     */
    public function getDocumentEntityDataProvider()
    {
        $eavDocument = new EavDocument();

        $eavValue = new EavValue();
        $eavValue->setDocument($eavDocument);

        return [
            [
                $eavDocument,
                $eavDocument,
            ],
            [
                $eavValue,
                $eavDocument,
            ],
            [
                new EavAttribute(),
                null,
            ],
        ];
    }

    /**
     * Tests getDocumentEntity method
     *
     * @param object $entity
     * @param mixed  $expected
     *
     * @dataProvider getDocumentEntityDataProvider
     */
    public function testGetDocumentEntity($entity, $expected)
    {
        $sut            = new EavDocumentSubscriber(
            new EavDocumentProcessor($this->getValidator()),
            new EavValueTypeCast()
        );
        $documentEntity = $sut->getDocumentEntity($entity);

        $this->assertEquals($documentEntity, $expected);
    }

    /**
     * Sets isScheduledForNNN methods calls results
     *
     * @param array          $scheduledChecks
     * @param ObjectProphecy $uowProphecy
     */
    protected function setScheduledCheckEntities($scheduledChecks, $uowProphecy)
    {
        if ($scheduledChecks['isForDelete']) {
            foreach ($scheduledChecks['isForDelete'] as $isForDelete) {
                $uowProphecy->isScheduledForDelete($isForDelete[0])->shouldBeCalledTimes(1)->willReturn($isForDelete[1]);
            }
        }
        if ($scheduledChecks['isForInsert']) {
            foreach ($scheduledChecks['isForInsert'] as $isForInsert) {
                $uowProphecy->isScheduledForInsert($isForInsert[0])->shouldBeCalledTimes(1)->willReturn($isForInsert[1]);
            }
        }
        if ($scheduledChecks['isForUpdate']) {
            foreach ($scheduledChecks['isForUpdate'] as $isForUpdate) {
                $uowProphecy->isScheduledForUpdate($isForUpdate[0])->shouldBeCalledTimes(1)->willReturn($isForUpdate[1]);
            }
        }
    }

    /**
     * Returns validator
     *
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        return static::$container->get('validator');
    }
}
