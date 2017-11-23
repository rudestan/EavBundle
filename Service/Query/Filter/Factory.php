<?php

namespace EavBundle\Service\Query\Filter;

use Nbb\Oms\ApiBundle\Contract\QueryFilterInterface;
use Nbb\Oms\ApiBundle\Service\Query\Filter\Factory as BaseFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Factory
 */
class Factory extends BaseFactory
{
    /**
     * Creates an instance of the desired filter based on resolved filter options.
     *
     * @param array $filterOptions
     *
     * @return QueryFilterInterface
     */
    public function make(array $filterOptions = [])
    {
        $resolver = $this->getOptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($filterOptions);

        $filterClassName = sprintf('\\EavBundle\\Service\\Query\\Filter\\%s', ucfirst($options['type']));

        /** @var AbstractEavFilter $filter */
        $filter = new $filterClassName($options['field'], $options['value']);
        $filter->setAttributeId($options['attributeId']);

        return $filter;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['attributeId', 'type', 'value']);
        $resolver->setAllowedTypes('attributeId', 'int');
    }
}
