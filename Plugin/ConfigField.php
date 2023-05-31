<?php

namespace Swissup\ScopesettingsHelp\Plugin;

use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class ConfigField
{
    const SCOPE_TYPE_WEBSITE = 'website';
    const SCOPE_TYPE_STORE = 'store';

    /**
     * @var \Swissup\ScopesettingsHelp\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

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
     */
    public function __construct(
        \Swissup\ScopesettingsHelp\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->websiteRepository = $websiteRepository;
        $this->storeRepository = $storeRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $subject, $result
     * @return string
     */
    public function afterGetTooltip(Field $subject, $result)
    {
        if (!$this->helper->isEnabled()) {
            return '';
        }

        //$params = $this->request->getParams();
        $websitesArray = $this->getWebsiteList($subject);
        $storesArray = $this->getStoreList($subject);
        $mergeArrs = array_merge($websitesArray, $storesArray);

        /* get unique values and convert to string */
        $tooltipContent = '';
        foreach ($mergeArrs as $item) {
            if (empty($item)) {
                continue;
            }
            $tooltipContent .= implode(" ", array_unique($item)) . "<br />";
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
        $scopeInfo = [];

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
                $scope->getId()
            );
            $scopeCode = $scope->getCode();
        }

        if (!is_null($configValue)) {
            $scopeInfo[] = $configValue . " - " . $scopeCode;
        }

        return $scopeInfo;
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
