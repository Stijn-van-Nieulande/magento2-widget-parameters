<?php

namespace Dmatthew\WidgetParameters\Block\Adminhtml\Widget;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\MultiSelect;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;

class CategoryChooser extends MultiSelect
{
    public $collectionFactory;

    public $authorization;

    protected $_urlBuilder;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        AuthorizationInterface $authorization,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->authorization = $authorization;
        $this->_urlBuilder = $urlBuilder;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function getElementHtml(): string
    {
        $html = '<div class="admin__field-control admin__control-grouped">';
        $html .= '<div id="' . $this->getHtmlId() . '-category-select" class="admin__field" data-bind="scope:\'' . $this->getHtmlId() . 'Category\'" data-index="index">';
        $html .= '<!-- ko foreach: elems() -->';
        $html .= '<input name="' . $this->getName() . '" data-bind="value: value" style="display: none"/>';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '<!-- /ko -->';
        $html .= '</div></div>';

        $html .= $this->getAfterElementHtml();

        return $html;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName(): string
    {
        return substr(parent::getName(), 0, -2);
    }

    public function getAfterElementHtml(): string
    {
        return '<script type="text/x-magento-init">
            {
                "*": {
                    "Magento_Ui/js/core/app": {
                        "components": {
                            "' . $this->getHtmlId() . 'Category": {
                                "component": "uiComponent",
                                "children": {
                                    "select_category": {
                                        "component": "Dmatthew_WidgetParameters/js/components/new-category",
                                        "config": {
                                            "filterOptions": true,
                                            "disableLabel": true,
                                            "chipsEnabled": true,
                                            "levelsVisibility": "1",
                                            "multiple": ' . ($this->getData('multiple') ? 'true' : 'false') . ',
                                            "elementTmpl": "ui/grid/filters/elements/ui-select",
                                            "options": ' . json_encode($this->getCategoriesTree()) . ',
                                            "value": ' . json_encode($this->getValues()) . ',
                                            "listens": {
                                                "index=create_category:responseData": "setParsed",
                                                "newOption": "toggleOptionSelected"
                                            },
                                            "config": {
                                                "dataScope": "select_category",
                                                "sortOrder": 10
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        </script>';
    }

    /**
     * @throws LocalizedException
     */
    public function getCategoriesTree()
    {
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('position', 'asc');

        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }

            $categoryById[$category->getId()]['is_active'] = 1;
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
        }

        return $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
    }

    public function getValues(): array
    {
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        if (!sizeof($values)) {
            return [];
        }

        $collection = $this->collectionFactory->create()
            ->addIdFilter($values);

        $options = [];
        foreach ($collection as $category) {
            $options[] = $category->getId();
        }

        return $options;
    }
}
