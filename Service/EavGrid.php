<?php

namespace EavBundle\Service;

use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavType;
use GridBundle\Grid\Contract\ColumnInterface;
use GridBundle\Grid\Contract\ColumnFactoryInterface;
use GridBundle\Grid\Contract\GridBuilderInterface;
use GridBundle\Grid\Contract\GridInterface;
use GridBundle\Grid\Director;
use GridBundle\Grid\Factory\AbstractColumnFactory;
use Symfony\Component\Translation\Translator;

/**
 * Class EavGrid
 */
class EavGrid
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var Director
     */
    protected $director;

    /**
     * @var GridBuilderInterface
     */
    protected $gridBuilder;

    /**
     * @var ColumnFactoryInterface
     */
    protected $columnFactory;

    /**
     * @var EavType
     */
    private $documentType;

    /**
     * @var array
     */
    private $filters;

    /**
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Director $director
     */
    public function setDirector(Director $director)
    {
        $this->director = $director;
    }

    /**
     * @param GridBuilderInterface $gridBuilder
     */
    public function setGridBuilder(GridBuilderInterface $gridBuilder)
    {
        $this->gridBuilder = $gridBuilder;
    }

    /**
     * @param ColumnFactoryInterface $columnFactory
     */
    public function setColumnFactory(ColumnFactoryInterface $columnFactory)
    {
        $this->columnFactory = $columnFactory;
    }

    /**
     * @param string      $id
     * @param EavType     $documentType
     * @param array       $attributes
     * @param array       $filters
     * @param string|null $editUrl
     * @param string|null $removeUrl
     * @param string|null $insertUrl
     *
     * @return GridInterface
     */
    public function createGrid(
        $id,
        EavType $documentType,
        array $attributes,
        array $filters,
        $editUrl = null,
        $removeUrl = null,
        $insertUrl = null
    ) {
        $this->documentType = $documentType;
        $this->filters      = $filters;
        $grid               = $this->director->build($id, $this->gridBuilder, $this->filters);

        foreach ($attributes as $attribute) {
            /** @var EavAttribute $attribute */
            $settings = $attribute->getSettings();
            if (!$settings->hasGridSettings()) {
                continue;
            }
            $grid->addColumn($this->createColumn($attribute));
        }

        $grid->addColumn($this->createDownloadColumn());
        $grid->addColumn($this->createControlColumn($editUrl, $removeUrl, $insertUrl));

        return $grid;
    }

    /**
     * @param EavAttribute $attribute
     *
     * @return ColumnInterface
     */
    protected function createColumn(EavAttribute $attribute)
    {
        $columnSettings = $this->getColumnSettings($attribute);
        $columnType     = $this->columnFactory->determineColumnType($columnSettings);

        switch ($columnType) {
            case AbstractColumnFactory::TYPE_CONTROL:
                $column = $this->columnFactory->createControlColumn($columnSettings);
                break;

            case AbstractColumnFactory::TYPE_SELECT:
                $column = $this->createSelectColumn($attribute);
                break;

            case AbstractColumnFactory::TYPE_TEXT:
            default:
                $column = $this->columnFactory->createTextColumn($columnSettings);
        }

        return $column;
    }

    /**
     * @param EavAttribute $attribute
     *
     * @return ColumnInterface
     */
    protected function createSelectColumn(EavAttribute $attribute)
    {
        $columnSettings = $this->getColumnSettings($attribute);
        $selectOptions  = ['' => $this->translator->trans('view.grid.filter.type.any')];
        if (isset($attribute->getValidators()['Choice']['choices'])) {
            foreach ($attribute->getValidators()['Choice']['choices'] as $type) {
                $selectOptions[$type] = $this->translator->trans('view.grid.filter.type.' . $type);
            }
        }

        $attributeName = $this->getAttributeName($attribute);

        $selectSettings = [
            'selectOptions'         => $selectOptions,
            'selectOptionIdField'   => 'id',
            'selectOptionNameField' => 'name',
            'selectedOption'        => isset($this->filters[$attributeName]) ? $this->filters[$attributeName] : '',
        ];
        $column         = $this->columnFactory->createSelectColumn(
            array_merge($columnSettings, $selectSettings)
        );

        return $column;
    }

    /**
     * @return ColumnInterface
     */
    protected function createDownloadColumn()
    {
        $pathAttribute = new EavAttribute();
        $pathAttribute->setName('path');
        $pathAttribute->setSettings(
            [
                'grid' => [
                    'type'       => 'text',
                    'width'      => 100,
                    'readOnly'   => true,
                    'filterable' => false,
                ],
            ]
        );

        return $this->createColumn($pathAttribute);
    }

    /**
     * @return ColumnInterface
     */
    protected function createControlColumn($editUrl = null, $removeUrl = null, $insertUrl = null)
    {
        $fakeAttribute = new EavAttribute();
        $fakeAttribute->setName('control');
        $gridSettings = [
            'type'  => 'control',
            'width' => 100,
        ];
        if ($editUrl !== null) {
            $gridSettings['editable'] = true;
            $gridSettings['editUrl']  = $editUrl;
        }
        if ($removeUrl !== null) {
            $gridSettings['removable'] = true;
            $gridSettings['removeUrl'] = $removeUrl;
        }
        if ($insertUrl !== null) {
            $gridSettings['insertable'] = true;
            $gridSettings['insertUrl']  = $insertUrl;
        }
        $fakeAttribute->setSettings(
            [
                'grid' => $gridSettings,
            ]
        );

        return $this->createColumn($fakeAttribute);
    }

    /**
     * @param EavAttribute $attribute
     *
     * @return array
     */
    protected function getAttributeGridSettings(EavAttribute $attribute)
    {
        $settings     = [];
        $gridSettings = $attribute->getSettings()->getGridSettings();

        if ($gridSettings !== null) {
            $settings['type']     = $gridSettings->getType();
            $settings['width']    = $gridSettings->getWidth();
            $settings['readOnly'] = $gridSettings->isReadOnly();

            if ($gridSettings->isRemovable()) {
                $settings['removable'] = $gridSettings->isRemovable();
                $settings['removeUrl'] = $gridSettings->getRemoveUrl();
            }

            if ($gridSettings->isEditable()) {
                $settings['editable'] = $gridSettings->isEditable();
                $settings['editUrl']  = $gridSettings->getEditUrl();
            }

            if ($gridSettings->isInsertable()) {
                $settings['insertable'] = $gridSettings->isInsertable();
                $settings['insertUrl']  = $gridSettings->getInsertUrl();
            }
        }

        return $settings;
    }

    /**
     * @param EavAttribute $attribute
     *
     * @return array
     */
    protected function getColumnSettings(EavAttribute $attribute)
    {
        $attributeName  = $this->getAttributeName($attribute);
        $columnSettings = array_merge(
            $this->getAttributeGridSettings($attribute),
            [
                'name'  => $attributeName,
                'label' => $this->translator->trans('view.grid.column.' . $attribute->getName()),
            ]
        );

        return $columnSettings;
    }

    /**
     * @param EavAttribute $attribute
     *
     * @return string
     */
    protected function getAttributeName(EavAttribute $attribute)
    {
        $documentTypeAttr = $this->documentType->getAttributes();
        $attributeName    = $attribute->getName();
        if (isset($documentTypeAttr[$attributeName]['alias'])) {
            $attributeName = $documentTypeAttr[$attributeName]['alias'];
        }

        return $attributeName;
    }
}
