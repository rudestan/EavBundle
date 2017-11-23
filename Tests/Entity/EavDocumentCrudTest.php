<?php

namespace EavBundle\Tests\Entity;

use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavDocument;
use EavBundle\Entity\EavType;
use EavBundle\Entity\EavValue;
use EavBundle\Exception\EavDocumentValidationException;
use EavBundle\Repository\EavAttributeRepository;
use EavBundle\Repository\EavDocumentRepository;
use EavBundle\Repository\EavTypeRepository;
use EavBundle\Repository\EavValueRepository;
use EavBundle\Tests\EavDbTestCase;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use EavBundle\DataFixtures\ORM\EavTypeFixture;
use EavBundle\DataFixtures\ORM\EavAttributeFixture;

/**
 * Class EavDocumentCrudTest
 */
class EavDocumentCrudTest extends EavDbTestCase
{
    protected function setUp()
    {
        $this->markTestSkipped('The functional tests should be adjusted when the appropriate containers will be added!');
    }

    /**
     * Valid document data provider
     *
     * @return array
     */
    public function validDocumentDataProvider()
    {
        return [
            [
                'customer_document',
                'some/path/to/bucket/document1.pdf',
                [
                    'customerId'           => 123,
                    'documentReference'    => 'F 1010101',
                    'customerDocumentType' => 'customer_invoice',
                    'originalFilename'     => 'F 1010101.pdf',
                ],
            ],
            [
                'supplier_document',
                'some/path/to/bucket/document2.pdf',
                [
                    'supplierId'           => 234,
                    'documentReference'    => 'G 55555',
                    'supplierDocumentType' => 'supplier_purchase_order',
                    'originalFilename'     => 'G 55555.pdf',
                ],
            ],
        ];
    }

    /**
     * Tests successful document creation
     *
     * @param string $typeAlias
     * @param string $path
     * @param array  $values
     *
     * @dataProvider validDocumentDataProvider
     */
    public function testCreateDocumentSuccess($typeAlias, $path, $values)
    {
        $eavType = $this->getEavTypeRepo()->findOneBy(['alias' => $typeAlias]);

        $this->assertInstanceOf(EavType::class, $eavType);

        $eavDocument = new EavDocument();
        $eavDocument
            ->setType($eavType)
            ->setPath($path)
            ->hydrateValues($values);

        $this->em->persist($eavDocument);
        $this->em->flush();

        $this->assertNotNull($eavDocument->getId());
    }

    /**
     * Tests reading of the document from the db
     *
     * @param string $typeAlias
     * @param string $path
     * @param array  $values
     *
     * @dataProvider validDocumentDataProvider
     */
    public function testReadDocument($typeAlias, $path, $values)
    {
        $eavDocument = $this->getEavDocumentRepo()->findOneBy(['path' => $path]);

        $this->assertInstanceOf(EavDocument::class, $eavDocument);

        /**
         * @var EavDocument
         */
        $type = $eavDocument->getType();

        $this->assertSame($typeAlias, $type->getAlias());
        $this->assertGreaterThan(1, $eavDocument->getValues()->count());
        $this->checkValues($eavDocument, $values);
    }

    /**
     * Invalid document data provider
     *
     * @return array
     */
    public function invalidDocumentDataProvider()
    {
        return [
            [
                'customer_document',
                'some/path/to/bucket/document1.pdf',
                [
                    'customerId'        => '234324',
                    'documentReference' => 213,
                ],
                [
                    'Object(EavBundle\Entity\EavDocument).type',
                    'Object(EavBundle\Entity\EavValue).customerId',
                    'Object(EavBundle\Entity\EavValue).documentReference',
                ],
            ],
            [
                'supplier_document',
                'some/path/to/bucket/document2.pdf',
                [
                    'supplierId'           => -1,
                    'documentReference'    => '',
                    'supplierDocumentType' => 'supplier_purchase_order',
                ],
                [
                    'Object(EavBundle\Entity\EavValue).supplierId',
                    'Object(EavBundle\Entity\EavDocument).document',
                ],
            ],
            [
                'customer_document',
                'some/path/to/bucket/document1.pdf',
                [],
                [
                    'Object(EavBundle\Entity\EavDocument).customerId',
                    'Object(EavBundle\Entity\EavDocument).type',
                ],
            ],
            [
                'customer_document',
                'some/path/to/bucket/document1.pdf',
                [
                    'customerId'           => 123,
                    'documentReference'    => 'F 1010101',
                    'customerDocumentType' => 'customer_invoice',
                    'originalFilename'     => 'F 1010101.pdf',
                ],
                [
                    'Object(EavBundle\Entity\EavDocument).document',
                ],
            ],
            [
                'customer_document',
                'some/path/to/bucket/document1.pdf',
                [
                    'customerId'           => 5678,
                    'customerDocumentType' => 'customer_invoice',
                    'originalFilename'     => 'F 23423423.pdf',
                ],
                [
                    'Object(EavBundle\Entity\EavDocument).document',
                ],
            ],
            [
                'supplier_document',
                'some/path/to/bucket/document2.pdf',
                [
                    'supplierId'           => 234,
                    'documentReference'    => 'G 55555',
                    'supplierDocumentType' => 'supplier_purchase_order',
                    'originalFilename'     => 'G 55555.pdf',
                ],
                [
                    'Object(EavBundle\Entity\EavDocument).document',
                ],
            ],
            [
                'supplier_document',
                'some/path/to/bucket/document2.pdf',
                [
                    'supplierId'           => 9876,
                    'supplierDocumentType' => 'supplier_invoice',
                    'originalFilename'     => 'F 23423423.pdf',
                ],
                [
                    'Object(EavBundle\Entity\EavDocument).document',
                ],
            ],
        ];
    }

    /**
     * Tests that creation of the document failed
     *
     * @param string $typeAlias
     * @param string $path
     * @param array  $values
     * @param array  $violationMessages
     *
     * @dataProvider invalidDocumentDataProvider
     */
    public function testCreateDocumentFailed($typeAlias, $path, $values, $violationMessages)
    {
        $eavType = $this->getEavTypeRepo()->findOneBy(['alias' => $typeAlias]);

        $this->assertInstanceOf(EavType::class, $eavType);

        $eavDocument = new EavDocument();
        $eavDocument
            ->setType($eavType)
            ->setPath($path)
            ->hydrateValues($values);

        $this->em->persist($eavDocument);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            $this->assertInstanceOf(EavDocumentValidationException::class, $e);
        }

        $lastViolations = $eavDocument->getLastViolations();
        $this->assertCount(count($violationMessages), $lastViolations);

        foreach ($violationMessages as $violationMessage) {
            $this->assertContains($violationMessage, (string) $lastViolations);
        }
    }

    /**
     * Test updating of the document's value
     */
    public function testUpdateDocumentValue()
    {
        $eavDocument = $this->getEavDocumentRepo()->findOneBy(['path' => 'some/path/to/bucket/document1.pdf']);

        $this->assertInstanceOf(EavDocument::class, $eavDocument);

        $documentId = $eavDocument->getId();

        $this->assertTrue($eavDocument->hasValue('documentReference'));

        $eavDocument->setValue('documentReference', 'F 2020202');
        $this->em->persist($eavDocument);
        $this->em->flush();

        $eavDocument = $this->getEavDocumentRepo()->find($documentId);

        $this->assertInstanceOf(EavDocument::class, $eavDocument);

        $eavValue = $eavDocument->getValue('documentReference');

        $this->assertSame('F 2020202', $eavValue->getValue());
    }

    /**
     * Test updating of the document's value
     */
    public function testUpdateDocumentValueFailed()
    {
        $eavDocument = $this->getEavDocumentRepo()->findOneBy(['path' => 'some/path/to/bucket/document1.pdf']);

        $this->assertInstanceOf(EavDocument::class, $eavDocument);
        $this->assertTrue($eavDocument->hasValue('customerId'));

        $eavDocument->setValue('customerId', '6789');
        $this->em->persist($eavDocument);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            $this->assertInstanceOf(EavDocumentValidationException::class, $e);
        }

        $violations = $eavDocument->getLastViolations();

        $this->assertCount(1, $violations);
        $this->assertContains('customerId', (string) $violations);
    }

    /**
     * Tests validation errors in case direct (without EavDocument pre-loading) EavValue delete operation
     */
    public function testDocumentIntegrityAfterManualValueDeletion()
    {
        $eavValue = $this->getEavValueRepo()->findOneBy(['value' => '123']);
        $this->em->remove($eavValue);

        $eavValue = $this->getEavValueRepo()->findOneBy(['value' => 'customer_invoice']);
        $this->em->remove($eavValue);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            $this->assertInstanceOf(EavDocumentValidationException::class, $e);
        }

        $violations = $eavValue->getDocument()->getLastViolations();

        $this->assertCount(2, $violations);
        $this->assertContains('customerId', (string) $violations);
        $this->assertContains('customerDocumentType', (string) $violations);
    }

    /**
     * Tests validation errors in case direct (without EavDocument pre-loading) EavValue insert operation
     */
    public function testDocumentIntegrityAfterValueInsertion()
    {
        $eavAttribute = $this->getEavAttrRepo()->findOneBy(['name' => 'supplierId']);

        $this->assertNotNull($eavAttribute);

        $eavDocument = $this->getEavDocumentRepo()->findOneBy(['path' => 'some/path/to/bucket/document1.pdf']);

        $this->assertNotNull($eavDocument);

        $eavValue = new EavValue();
        $eavValue->setAttribute($eavAttribute);
        $eavValue->setDocument($eavDocument);
        $eavValue->setValue(1234);
        $this->em->persist($eavValue);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            $this->assertInstanceOf(EavDocumentValidationException::class, $e);
        }

        $violations = $eavValue->getDocument()->getLastViolations();

        $this->assertCount(1, $violations);
        $this->assertContains('supplierId', (string) $violations);
    }

    /**
     * Tests validation errors in case direct (without EavDocument pre-loading) EavValue update operation
     */
    public function testValueValidationForUpdate()
    {
        $eavValue = $this->getEavValueRepo()->findOneBy(['value' => 'customer_invoice']);

        $this->assertNotNull($eavValue);

        $eavValue->setValue('not_existing_type');

        $this->em->persist($eavValue);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            $this->assertInstanceOf(EavDocumentValidationException::class, $e);
        }

        $violations = $eavValue->getDocument()->getLastViolations();

        $this->assertCount(1, $violations);
    }

    /**
     * Test deletion of the document with related values
     */
    public function testDeleteDocumentWithValues()
    {
        $eavDocument = $this->getEavDocumentRepo()->findOneBy(['path' => 'some/path/to/bucket/document1.pdf']);

        $this->assertInstanceOf(EavDocument::class, $eavDocument);
        $documentId = $eavDocument->getId();

        $values = $eavDocument->getValues();

        $this->assertGreaterThan(0, $values->count());

        $this->em->remove($eavDocument);
        $this->em->flush();

        $eavDocument = $this->getEavDocumentRepo()->find($documentId);

        $this->assertNull($eavDocument);

        $eavValues = $this->getEavValueRepo()->findBy(['document' => $documentId]);

        $this->assertEmpty($eavValues);
    }

    /**
     * @return EavAttributeRepository
     */
    protected function getEavAttrRepo()
    {
        return $this->getRepo('EavAttribute');
    }

    /**
     * @return EavTypeRepository
     */
    protected function getEavTypeRepo()
    {
        return $this->getRepo('EavType');
    }

    /**
     * @return EavDocumentRepository
     */
    protected function getEavDocumentRepo()
    {
        return $this->getRepo('EavDocument');
    }

    /**
     * @return EavValueRepository
     */
    protected function getEavValueRepo()
    {
        return $this->getRepo('EavValue');
    }

    /**
     * @param string $name
     *
     * @return EntityRepository
     */
    protected function getRepo($name)
    {
        return static::$container->get('doctrine')->getRepository('EavBundle:' . $name);
    }

    /**
     * Provides an ArrayCollection of Fixtures that should be loaded for the test
     *
     * @return ArrayCollection
     */
    protected static function getFixtures()
    {
        return new ArrayCollection([
            new EavTypeFixture(),
            new EavAttributeFixture(),
        ]);
    }

    /**
     * Checks document's values
     *
     * @param EavDocument $eavDocument
     * @param array       $values
     */
    protected function checkValues(EavDocument $eavDocument, $values)
    {
        foreach ($values as $name => $value) {
            $this->assertTrue($eavDocument->hasValue($name));

            $eavValue = $eavDocument->getValue($name);

            $this->assertInstanceOf(EavValue::class, $eavValue);
            $this->assertSame($value, $eavValue->getValue());

            $eavAttr = $eavValue->getAttribute();

            $this->assertInstanceOf(EavAttribute::class, $eavAttr);

            $type = $eavAttr->getType();

            $this->assertSame(gettype($value), $type);
            $this->assertSame(gettype($value), gettype($eavValue->getValue()));
        }
    }
}
