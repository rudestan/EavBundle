<?php

namespace EavBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EavBundle\Model\Eav\Attribute\Settings;

/**
 * Class EavAttribute
 *
 * @ORM\Table(
 *      name="eav_attribute",
 *      indexes={
 *          @ORM\Index(name="idx_eav_attribute_name", columns={"name"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="idx_eav_attribute_name_unique", columns={"name"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="EavBundle\Repository\EavAttributeRepository")
 */
class EavAttribute
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
     * @ORM\Column(type="enumEavAttributeType", nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    protected $name;

    /**
     * @var array
     *
     * @ORM\Column(name="validators", type="json_array", nullable=true)
     */
    protected $validators;

    /**
     * @var array
     *
     * @ORM\Column(name="settings", type="json_array", nullable=true)
     */
    protected $settings;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    protected $sortOrder;

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return EavAttribute
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * @return EavAttribute
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return EavAttribute
     */
    public function setValidators(array $validators)
    {
        $this->validators = $validators;

        return $this;
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return new Settings($this->settings);
    }

    /**
     * @param array $settings
     *
     * @return EavAttribute
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     *
     * @return EavAttribute
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
