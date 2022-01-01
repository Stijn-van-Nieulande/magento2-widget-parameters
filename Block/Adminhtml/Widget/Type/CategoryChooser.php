<?php

namespace Dmatthew\WidgetParameters\Block\Adminhtml\Widget\Type;

use Dmatthew\WidgetParameters\Block\Adminhtml\Widget\CategoryChooser as CategoryChooserWidget;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;

class CategoryChooser extends Template
{
    /**
     * @var Factory
     */
    protected $factoryElement;

    /**
     * @param Context $context
     * @param Factory $factoryElement
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $factoryElement,
        $data = []
    ) {
        $this->factoryElement = $factoryElement;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        $input = $this->factoryElement->create(CategoryChooserWidget::class, ['data' => $element->getData()]);
        /** @var CategoryChooserWidget $input */
        $input->setLabel('')
            ->setId($element->getId())
            ->setForm($element->getForm())
            ->setClass("widget-option")
            ->setData('multiple', $this->getData('multiple') === 'true');

        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $element->setData('after_element_html', $input->getElementHtml());
        $element->setValue(''); // Hides the additional label that gets added.

        return $element;
    }
}
