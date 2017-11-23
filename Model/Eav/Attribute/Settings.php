<?php

namespace EavBundle\Model\Eav\Attribute;

use EavBundle\Model\Eav\Attribute\Form\Settings as FormSettings;
use EavBundle\Model\Eav\Attribute\Grid\Settings as GridSettings;

/**
 * Class Settings
 */
class Settings
{
    /**
     * @var GridSettings
     */
    protected $gridSettings;

    /**
     * @var FormSettings
     */
    protected $formSettings;

    /**
     * Settings constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        if (isset($settings['grid']) && is_array($settings['grid'])) {
            $this->initGridSettings($settings['grid']);
        }
        if (isset($settings['form']) && is_array($settings['form'])) {
            $this->initFormSettings($settings['form']);
        }
    }

    /**
     * @return GridSettings|null
     */
    public function getGridSettings()
    {
        return $this->gridSettings;
    }

    /**
     * @return FormSettings|null
     */
    public function getFormSettings()
    {
        return $this->formSettings;
    }

    /**
     * @return bool
     */
    public function hasGridSettings()
    {
        return $this->gridSettings !== null;
    }

    /**
     * @return bool
     */
    public function hasFormSettings()
    {
        return $this->formSettings !== null;
    }

    /**
     * @param array $settings
     */
    protected function initGridSettings(array $settings)
    {
        $this->gridSettings = new GridSettings($settings);
    }

    /**
     * @param array $settings
     */
    protected function initFormSettings(array $settings)
    {
        $this->formSettings = new FormSettings($settings);
    }
}
