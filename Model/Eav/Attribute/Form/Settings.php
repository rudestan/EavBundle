<?php

namespace EavBundle\Model\Eav\Attribute\Form;

/**
 * Eav Attribute Form Settings
 */
class Settings
{
    /**
     * @var bool
     */
    protected $disabled = false;

    /**
     * @var bool
     */
    protected $editable = true;

    /**
     * @var bool
     */
    protected $shouldRender = true;

    /**
     * Settings constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        if (isset($settings['disabled'])) {
            $this->disabled = $settings['disabled'];
        }
        if (isset($settings['editable'])) {
            $this->editable = $settings['editable'];
        }
        if (isset($settings['render'])) {
            $this->shouldRender = $settings['render'];
        }
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->editable;
    }

    /**
     * @return bool
     */
    public function shouldRender()
    {
        return $this->shouldRender;
    }
}
