<?php

namespace EavBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class EavType
 *
 * @ORM\Table(
 *      name="eav_type",
 *      indexes={
 *          @ORM\Index(name="idx_eav_type_alias", columns={"alias"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="idx_eav_type_alias_unique", columns={"alias"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="EavBundle\Repository\EavTypeRepository")
 */
class EavType
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=120, nullable=false)
     */
    protected $alias;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     */
    protected $name;

    /**
     * @var array
     *
     * @ORM\Column(name="attributes", type="json_array", nullable=true)
     */
    protected $attributes = [];

    /**
     * @var array
     *
     * @ORM\Column(name="validators", type="json_array", nullable=true)
     */
    protected $validators = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     *
     * @return EavType
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

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
     * @return EavType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns an array of aliases to names attribute mapping
     *
     * @return array
     */
    public function getAttributeAliasesMapping()
    {
        $mapping = [];

        foreach ($this->attributes as $name => $attribute) {
            $key           = isset($attribute['alias']) ? $attribute['alias'] : $name;
            $mapping[$key] = $name;
        }

        return $mapping;
    }

    /**
     * Returns an attribute name by provided alias
     *
     * @param string $alias
     *
     * @return string|null
     */
    public function getAttributeNameByAlias($alias)
    {
        foreach ($this->attributes as $name => $settings) {
            if (isset($settings['alias']) && $settings['alias'] === $alias) {
                return $name;
            }
        }

        return null;
    }

    /**
     * @param array $attributes
     *
     * @return EavType
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * @param array $validators
     *
     * @return EavType
     */
    public function setValidators($validators)
    {
        $this->validators = $validators;

        return $this;
    }
}
