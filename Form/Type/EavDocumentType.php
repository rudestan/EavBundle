<?php

namespace EavBundle\Form\Type;

use EavBundle\Entity\EavDocument;
use EavBundle\Validator\Constraints\EavValueCollectionConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EavDocumentType
 */
class EavDocumentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('values', EavValueCollectionType::class,
            [
                'constraints' => [
                    new EavValueCollectionConstraint(),
                ],
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EavDocument::class,
        ]);
    }
}
