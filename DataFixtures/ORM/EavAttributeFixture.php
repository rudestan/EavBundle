<?php

namespace EavBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use EavBundle\Entity\EavAttribute;

/**
 * Class EavAttributeFixture
 */
class EavAttributeFixture implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $eavAttribute = new EavAttribute();
        $eavAttribute->setName('customerId');
        $eavAttribute->setType('integer');
        $eavAttribute->setSortOrder(1);
        $eavAttribute->setValidators([
            'NotBlank' => null,
            'Range'    => [
                'min' => 1,
            ],
        ]);
        $eavAttribute->setSettings([
            'grid' => [
                'type'     => 'text',
                'readOnly' => false,
                'width'    => 60,
            ],
        ]);
        $manager->persist($eavAttribute);

        $eavAttribute = new EavAttribute();
        $eavAttribute->setName('supplierId');
        $eavAttribute->setType('integer');
        $eavAttribute->setSortOrder(1);
        $eavAttribute->setValidators([
            'NotBlank' => null,
            'Range'    => [
                'min' => 1,
            ],
        ]);
        $eavAttribute->setSettings([
            'grid' => [
                'type'     => 'text',
                'readOnly' => false,
                'width'    => 60,
            ],
        ]);
        $manager->persist($eavAttribute);

        $eavAttribute = new EavAttribute();
        $eavAttribute->setName('documentReference');
        $eavAttribute->setType('string');
        $eavAttribute->setSortOrder(3);
        $eavAttribute->setSettings([
            'grid' => [
                'type'     => 'text',
                'readOnly' => false,
                'width'    => 100,
            ],
            'form' => [
                'editable' => false,
            ],
        ]);
        $manager->persist($eavAttribute);

        $eavAttribute = new EavAttribute();
        $eavAttribute->setName('originalFilename');
        $eavAttribute->setType('string');
        $eavAttribute->setSortOrder(5);
        $eavAttribute->setSettings([
            'grid' => [
                'type'     => 'text',
                'readOnly' => false,
                'width'    => 200,
            ],
            'form' => [
                'disabled' => true,
            ],
        ]);
        $manager->persist($eavAttribute);

        $eavAttribute = new EavAttribute();
        $eavAttribute->setName('customerDocumentType');
        $eavAttribute->setType('string');
        $eavAttribute->setSortOrder(2);
        $eavAttribute->setValidators([
            'NotBlank' => null,
            'Choice'   => [
                'strict'  => true,
                'choices' => [
                    'customer_sales_offer',
                    'customer_sales_order',
                    'customer_sales_prepaid_order',
                    'customer_upfront_replacement_invoice',
                    'customer_credit_note_request',
                    'customer_invoice',
                    'customer_credit_note',
                    'customer_replacement_invoice',
                    'customer_replacement_order',
                    'customer_service_credit_note_request',
                    'customer_service_cost_estimation',
                    'customer_service_order',
                    'customer_service_invoice',
                    'customer_service_credit_note',
                    'cash_invoice',
                    'cash_credit_note',
                    'other',
                ],
            ],
        ]);
        $eavAttribute->setSettings([
            'grid' => [
                'type'     => 'select',
                'readOnly' => false,
                'width'    => 150,
            ],
            'form' => [
                'editable' => false,
            ],
        ]);
        $manager->persist($eavAttribute);

        $eavAttribute = new EavAttribute();
        $eavAttribute->setName('supplierDocumentType');
        $eavAttribute->setType('string');
        $eavAttribute->setSortOrder(2);
        $eavAttribute->setValidators([
            'NotBlank' => null,
            'Choice'   => [
                'strict'  => true,
                'choices' => [
                    'supplier_purchase_enquiry',
                    'supplier_purchase_order',
                    'supplier_return',
                    'supplier_inbound_receipt',
                    'supplier_inbound_return_receipt',
                    'supplier_return_refund',
                    'supplier_invoice',
                    'supplier_credit_note',
                    'supplier_bonus_credit_note',
                    'other',
                ],
            ],
        ]);
        $eavAttribute->setSettings([
            'grid' => [
                'type'     => 'select',
                'readOnly' => false,
                'width'    => 150,
            ],
            'form' => [
                'editable' => false,
            ],
        ]);
        $manager->persist($eavAttribute);

        $eavAttribute = new EavAttribute();
        $eavAttribute->setName('comment');
        $eavAttribute->setType('string');
        $eavAttribute->setSortOrder(4);
        $eavAttribute->setValidators([
            'Length' => [
                'min' => 0,
                'max' => 255,
            ],
        ]);
        $manager->persist($eavAttribute);

        $eavAttribute = new EavAttribute();
        $eavAttribute->setName('versions');
        $eavAttribute->setType('array');
        $eavAttribute->setSortOrder(5);
        $eavAttribute->setSettings([
            'form' => ['render' => false],
        ]);
        $manager->persist($eavAttribute);

        $manager->flush();
    }
}
