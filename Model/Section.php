<?php

namespace Swissup\ScopesettingsHelp\Model;

class Section extends \Magento\Framework\Model\AbstractModel
{
    const CONFIG_SECTION_ID = 'scopesettings_help';

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $urlModel;

    /**
     * @param \Magento\Backend\Model\UrlInterface $urlModel
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlModel
    ) {
        $this->urlModel = $urlModel;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\ScopesettingsHelp\Model\ResourceModel\Url::class);
    }

    /**
     * @return string
     */
    protected function getCurrentUrl(): string
    {
        return $this->urlModel->getCurrentUrl();
    }

    /**
     * @return bool
     */
    public function isCurrectSection(): bool
    {
        return str_contains($this->getCurrentUrl(), self::CONFIG_SECTION_ID);
    }
}
