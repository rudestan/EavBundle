<?php

namespace EavBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use EavBundle\Exception\EavAttributeNotSetException;

/**
 * Class EavDocumentValuesTrait
 */
trait EavDocumentValuesTrait
{
    /**
     * Hydrates the values from assoc array
     *
     * @param array $values
     *
     * @return EavDocument
     */
    public function hydrateValues($values)
    {
        if (!$this->values instanceof ArrayCollection) {
            $this->values = new ArrayCollection();
        }

        foreach ($values as $name => $value) {
            $this->setValue($name, $value);
        }

        return $this;
    }

    /**
     * Sets value in values collection. If value does not exist - creates new value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setValue($name, $value)
    {
        $offset = $this->getValueIndexByName($name);

        if ($offset === null) {
            $this->hydrateNewValue($name, $value);
        } else {
            $this->offsetUpdateValue($offset, $value);
        }
    }

    /**
     * Sets raw EavValue in document's values collection
     *
     * @param EavValue $value
     *
     * @throws EavAttributeNotSetException
     */
    public function setRawEavValue(EavValue $value)
    {
        if (!$value->getDocument() instanceof EavDocument) {
            $value->setDocument($this);
        }

        $attr = $value->getAttribute();

        if (!$attr instanceof EavAttribute && !$attr instanceof EavPromisedAttribute) {
            throw new EavAttributeNotSetException('Attribute not set!');
        }

        $offset = $this->getValueIndexByName($value->getAttribute()->getName());

        $this->values->offsetSet($offset, $value);
    }

    /**
     * Returns value by the attribute name or null if it does not exist
     *
     * @param string $name
     *
     * @return null|EavValue
     */
    public function getValue($name)
    {
        if (!$this->values->count()) {
            return null;
        }

        $index = $this->getValueIndexByName($name);

        return $this->values->get($index);
    }

    /**
     * Removes the value by the attribute name from values collection. Returns null or removed EavValue instance
     *
     * @param string $name
     *
     * @return null|EavValue
     */
    public function removeValue($name)
    {
        $index = $this->getValueIndexByName($name);

        return $this->values->remove($index);
    }

    /**
     * Creates a new EavValue object and returns it
     *
     * @param mixed                                  $value
     * @param null|EavAttribute|EavPromisedAttribute $attribute
     *
     * @return EavValue
     */
    public function createNewValue($value, $attribute = null)
    {
        $val = new EavValue();
        $val->setDocument($this);

        if ($attribute instanceof EavAttribute ||
            $attribute instanceof EavPromisedAttribute
        ) {
            $val->setAttribute($attribute);
        }

        $val->setValue($value);

        return $val;
    }

    /**
     * Returns an array with existing attribute names of the values
     *
     * @return array
     */
    public function getAttributeNames()
    {
        $attrNames = [];

        foreach ($this->values as $value) {
            $attr = $value->getAttribute();

            if (!$attr instanceof EavAttribute) {
                continue;
            }

            $attrNames[] = $attr->getName();
        }

        return array_unique($attrNames);
    }

    /**
     * Checks and returns whether value is set in values collection
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasValue($name)
    {
        return !is_null($this->getValueIndexByName($name));
    }

    /**
     * Returns value entity by attribute name
     *
     * @param string $name
     *
     * @return EavValue|null
     */
    protected function getValueIndexByName($name)
    {
        foreach ($this->values as $index => $value) {
            if (!$value instanceof EavValue) {
                continue;
            }

            $attr = $value->getAttribute();

            if (!$attr instanceof EavAttribute &&
                !$attr instanceof EavPromisedAttribute
            ) {
                continue;
            }

            $attrName = $attr->getName();

            if ($attrName == $name) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Hydrates new value and sets the attribute. Creates promised attribute instance.
     *
     * @param string $name
     * @param mixed  $value
     */
    protected function hydrateNewValue($name, $value)
    {
        $attribute = new EavPromisedAttribute();
        $attribute->setName($name);

        $val = $this->createNewValue($value, $attribute);

        $this->values->add($val);
    }

    /**
     * Updates value at passed offset.
     *
     * @param int   $offset
     * @param mixed $value
     */
    protected function offsetUpdateValue($offset, $value)
    {
        $val = $this->values->get($offset);

        $val->setValue($value);
        $this->values->set($offset, $val);
    }
}
