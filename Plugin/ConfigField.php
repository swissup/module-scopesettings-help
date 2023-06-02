<?php

namespace Swissup\ScopesettingsHelp\Plugin;

use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Swissup\ScopesettingsHelp\Model\Section;

class ConfigField
{
    const SCOPE_TYPE_WEBSITE = 'website';
    const SCOPE_TYPE_STORE = 'store';

    /**
     * @var \Swissup\ScopesettingsHelp\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\ScopesettingsHelp\Model\Section
     */
    protected $section;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Swissup\ScopesettingsHelp\Helper\Data $helper
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Url $currentUrl
     */
    public function __construct(
        \Swissup\ScopesettingsHelp\Helper\Data $helper,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        ScopeConfigInterface $scopeConfig,
        Section $section
    ) {
        $this->helper = $helper;
        $this->websiteRepository = $websiteRepository;
        $this->storeRepository = $storeRepository;
        $this->scopeConfig = $scopeConfig;
        $this->section = $section;
    }

    /**
     * @param $subject, $result
     * @return string
     */
    public function afterGetTooltip(Field $subject, $result)
    {
        if (!$this->helper->isEnabled() || $this->section->isCurrectSection()) {
            return '';
        }

        $websitesArray = $this->getWebsiteList($subject);
        $storesArray = $this->getStoreList($subject);
        $mergeArrs = array_unique(array_merge($websitesArray, $storesArray), SORT_REGULAR);

        /* prepare tooltip content string value */
        $tooltipContent = '';
        foreach ($mergeArrs as $item) {
            if (empty($item)) {
                continue;
            }
            $tooltipContent .= implode(" ", $item) . "<br />";
        }

        return $tooltipContent;
    }

    /**
     * @param $subject
     * @return array
     */
    private function getWebsiteList($subject): array
    {
        $websiteList = [];
        $scopeType = $this->getScopeTypeWebsite();
        foreach ($this->websiteRepository->getList() as $website) {
            if ($website->getCode() === WebsiteInterface::ADMIN_CODE) {
                $websiteScopeInfo = $this->getScopeSettingsInfo($subject, $scopeType, $website);
                $websiteList[] = $websiteScopeInfo;
            }
        }

        return $websiteList;
    }

    /**
     * @param $subject
     * @return array
     */
    private function getStoreList($subject): array
    {
        $storeList = [];
        $scopeType = $this->getScopeTypeStore();
        foreach ($this->storeRepository->getList() as $store) {
            /* return settings info for each store */
            $storeScopeInfo = $this->getScopeSettingsInfo($subject, $scopeType, $store);
            $storeList[] = $storeScopeInfo;
        }

        return $storeList;
    }

    /**
     *  @param $subject, $scope
     *  @return array
     */
    private function getScopeSettingsInfo($subject, $scopeType, $scope): array
    {
        $configPath = $subject->getPath();

        if ($scopeType === self::SCOPE_TYPE_WEBSITE) {
            $configValue = $this->scopeConfig->getValue(
                $configPath,
                ScopeInterface::SCOPE_WEBSITE,
                $scope
            );
            $scopeCode = $scope->getCode();
        } else {
            $configValue = $this->scopeConfig->getValue(
                $configPath,
                ScopeInterface::SCOPE_STORE,
                $scope
            );

            $scopeCode = $scope->getCode() === 'admin'
                ? $scope->getCode()
                : $scope->getCode() . ' ' . self::SCOPE_TYPE_STORE;
        }

        $scopeInfo = [];
        if (!is_null($configValue)) {
            if (is_numeric($configValue)) {
                /* use intuitive value Yes / No instead of '1' / '0'  */
                $configValue = $this->useIntuitiveConfigValue((int) $configValue);
            }

            $scopeInfo[] = '<span class="config_value">' .
                            ucwords($configValue) .
                           '</span> - ' .
                            ucwords($scopeCode);
        }

        return $scopeInfo;
    }

    public function useIntuitiveConfigValue($configValue): string
    {
        foreach ([0 => __('No'), 1 => __('Yes')] as $key => $value) {
            if ($key === $configValue) {
                $configValue = $value;
            }
        }
        return $configValue;
    }

    private function getScopeTypeWebsite(): string
    {
        return self::SCOPE_TYPE_WEBSITE;
    }

    private function getScopeTypeStore(): string
    {
        return self::SCOPE_TYPE_STORE;
    }
}
