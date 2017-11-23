<?php

namespace EavBundle\Service\Query\Filter;

use Nbb\Oms\ApiBundle\Contract\QueryFilterInterface;
use Nbb\Oms\ApiBundle\Service\Query\Filter\AbstractFilter;

/**
 * EavAbstractFilter class
 */
abstract class AbstractEavFilter extends AbstractFilter implements QueryFilterInterface
{
    /**
     * @var string The field name to which the filter applies
     */
    protected $field;

    /**
     * @var string|int|null The value to filter for
     */
    protected $value;

    /**
     * @var int
     */
    protected $attributeId;

    /**
     * @param int $attributeId
     */
    public function setAttributeId($attributeId)
    {
        $this->attributeId = $attributeId;
    }
}
