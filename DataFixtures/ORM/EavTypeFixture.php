<?php

namespace EavBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use EavBundle\Entity\EavType;

/**
 * Class EavTypeFixture
 */
class EavTypeFixture implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $eavType = new EavType();
        $eavType->setName('Customer document');
        $eavType->setAlias('customer_document');
        $eavType->setAttributes([
            'customerId'           => ['required' => true, 'role' => 'subjectId'],
            'documentReference'    => ['required' => false, 'role' => 'documentReference'],
            'customerDocumentType' => ['required' => true, 'alias' => 'type', 'role' => 'type'],
            'originalFilename'     => ['required' => false],
            'versions'             => ['required' => false],
        ]);
        $eavType->setValidators([
            'EavDocumentConditionalNotRequiredConstraint' => [
                'checkAttribute'    => 'customerDocumentType',
                'value'             => 'other',
                'requiredAttribute' => 'documentReference',
            ],
            'EavUniqueDocumentConstraint'                 => [
                'attributes' => [
                    'documentReference',
                    'customerDocumentType',
                    'originalFilename',
                ],
            ],
        ]);
        $manager->persist($eavType);

        $eavType = new EavType();
        $eavType->setName('Supplier document');
        $eavType->setAlias('supplier_document');
        $eavType->setAttributes([
            'supplierId'           => ['required' => true, 'role' => 'subjectId'],
            'documentReference'    => ['required' => false, 'role' => 'documentReference'],
            'supplierDocumentType' => ['required' => true, 'alias' => 'type', 'role' => 'type'],
            'originalFilename'     => ['required' => false],
            'versions'             => ['required' => false],
        ]);
        $eavType->setValidators([
            'EavDocumentConditionalNotRequiredConstraint' => [
                'checkAttribute'    => 'supplierDocumentType',
                'value'             => 'other',
                'requiredAttribute' => 'documentReference',
            ],
            'EavUniqueDocumentConstraint'                 => [
                'attributes' => [
                    'documentReference',
                    'supplierDocumentType',
                    'originalFilename',
                ],
            ],
        ]);
        $manager->persist($eavType);

        $manager->flush();
    }
}
