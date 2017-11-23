<?php

namespace EavBundle\Model\Eav\Attribute\Grid;

/**
 * Eav Attribute Grid Settings
 */
class Settings
{
    /**
     * @var string
     */
    protected $type = 'text';

    /**
     * @var bool
     */
    protected $readOnly = false;

    /**
     * @var int
     */
    protected $width = 100;

    /**
     * @var bool
     */
    protected $editable;

    /**
     * @var string
     */
    protected $editUrl;

    /**
     * @var bool
     */
    protected $removable;

    /**
     * @var string
     */
    protected $removeUrl;

    /**
     * @var bool
     */
    protected $insertable;

    /**
     * @var string
     */
    protected $insertUrl;

    /**
     * Settings constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        if (isset($settings['type'])) {
            $this->type = $settings['type'];
        }

        if (isset($settings['readOnly'])) {
            $this->readOnly = $settings['readOnly'];
        }

        if (isset($settings['width'])) {
            $this->width = $settings['width'];
        }

        if (isset($settings['editable'])) {
            $this->editable = $settings['editable'];
        }

        if (isset($settings['editUrl'])) {
            $this->editUrl = $settings['editUrl'];
        }

        if (isset($settings['removable'])) {
            $this->removable = $settings['removable'];
        }

        if (isset($settings['removeUrl'])) {
            $this->removeUrl = $settings['removeUrl'];
        }

        if (isset($settings['insertable'])) {
            $this->insertable = $settings['insertable'];
        }

        if (isset($settings['insertUrl'])) {
            $this->insertUrl = $settings['insertUrl'];
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->editable;
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->editUrl;
    }

    /**
     * @return bool
     */
    public function isRemovable()
    {
        return $this->removable;
    }

    /**
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->removeUrl;
    }

    /**
     * @return bool
     */
    public function isInsertable()
    {
        return $this->insertable;
    }

    /**
     * @return string
     */
    public function getInsertUrl()
    {
        return $this->insertUrl;
    }
}
