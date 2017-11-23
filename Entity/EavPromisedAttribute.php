<?php

namespace EavBundle\Entity;

/**
 * Class EavPromisedAttribute
 */
class EavPromisedAttribute
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return EavPromisedAttribute
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
