<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Adminhtml\Tag\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Model\Config\Source\Design\Robots;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Class Tag
 * @package Mageplaza\Blog\Block\Adminhtml\Tag\Edit\Tab
 */
class Tag extends Generic implements TabInterface
{
    /**
     * Wysiwyg config
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    public $wysiwygConfig;

    /**
     * Country options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    public $booleanOptions;

    /**
     * @var \Magento\Config\Model\Config\Source\Enabledisable
     */
    protected $enableDisable;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $systemStore;

    /**
     * @var \Magento\Config\Model\Config\Source\Design\Robots
     */
    public $metaRobots;

    /**
     * Tag constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Magento\Config\Model\Config\Source\Enabledisable $enableDisable
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Config\Model\Config\Source\Design\Robots $metaRobotsOptions
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Yesno $booleanOptions,
        Enabledisable $enableDisable,
        Store $systemStore,
        Robots $metaRobotsOptions,
        array $data = []
    )
    {
        $this->wysiwygConfig  = $wysiwygConfig;
        $this->booleanOptions = $booleanOptions;
        $this->enableDisable  = $enableDisable;
        $this->systemStore    = $systemStore;
        $this->metaRobots     = $metaRobotsOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\Blog\Model\Tag $tag */
        $tag = $this->_coreRegistry->registry('mageplaza_blog_tag');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('tag_');
        $form->setFieldNameSuffix('tag');

        $fieldset = $form->addFieldset('base_fieldset', [
                'legend' => __('Tag Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        if ($tag->getId()) {
            $fieldset->addField('tag_id', 'hidden', ['name' => 'tag_id']);
        }

        $fieldset->addField('name', 'text', [
                'name'     => 'name',
                'label'    => __('Name'),
                'title'    => __('Name'),
                'required' => true,
            ]
        );

        $fieldset->addField('enabled', 'select', [
                'name'   => 'enabled',
                'label'  => __('Status'),
                'title'  => __('Status'),
                'values' => $this->enableDisable->toOptionArray(),
            ]
        );
        if (!$tag->hasData('enabled')) {
            $tag->setEnabled(1);
        }

        $fieldset->addField('description', 'editor', [
                'name'   => 'description',
                'label'  => __('Description'),
                'title'  => __('Description'),
                'config' => $this->wysiwygConfig->getConfig()
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            /** @var \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $rendererBlock */
            $rendererBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $fieldset->addField('store_ids', 'multiselect', [
                'name'   => 'store_ids',
                'label'  => __('Store Views'),
                'title'  => __('Store Views'),
                'values' => $this->systemStore->getStoreValuesForForm(false, true)
            ])->setRenderer($rendererBlock);

            if (!$tag->hasData('store_ids')) {
                $tag->setStoreIds(0);
            }
        } else {
            $fieldset->addField('store_ids', 'hidden', [
                'name'  => 'store_ids',
                'value' => $this->_storeManager->getStore()->getId()
            ]);
        }

        $fieldset->addField('url_key', 'text', [
                'name'  => 'url_key',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
            ]
        );
        $fieldset->addField('meta_title', 'text', [
                'name'  => 'meta_title',
                'label' => __('Meta Title'),
                'title' => __('Meta Title'),
            ]
        );

        $fieldset->addField('meta_description', 'textarea', [
                'name'  => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
            ]
        );

        $fieldset->addField('meta_keywords', 'textarea', [
                'name'  => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
            ]
        );

        $fieldset->addField('meta_robots', 'select', [
                'name'   => 'meta_robots',
                'label'  => __('Meta Robots'),
                'title'  => __('Meta Robots'),
                'values' => $this->metaRobots->toOptionArray(),
            ]
        );

        $form->addValues($tag->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Tag');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
