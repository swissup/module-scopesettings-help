<?php

namespace Swissup\ScopesettingsHelp\Plugin;

use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\Store;

class ConfigField
{
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
     * @param \Swissup\ScopesettingsHelp\Helper\Data $helper
     */
    public function __construct(
        \Swissup\ScopesettingsHelp\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->websiteRepository = $websiteRepository;
        $this->storeRepository = $storeRepository;
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
        $tooltipContent;
        foreach ($mergeArrs as $item) {
            $tooltipContent = implode("<br />", array_unique($item));
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
        foreach ($this->websiteRepository->getList() as $website) {
            if ($website->getCode() === WebsiteInterface::ADMIN_CODE) {
                $websiteScopeInfo = $this->getScopeSettingsInfo($subject, $website);
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
        foreach ($this->storeRepository->getList() as $store) {
            if ($store->getCode() === Store::ADMIN_CODE) {
                $storeScopeInfo = $this->getScopeSettingsInfo($subject, $store);
                $storeList[] = $storeScopeInfo;
            }
        }

        return $storeList;
    }

    /**
     *  @param $subject, $scope
     *  @return
     */
    private function getScopeSettingsInfo($subject, $scope)
    {
        $scopeInfo = [];
        $scopeInfo[] = 'here will be scope info-1';
        $scopeInfo[] = 'here will be scope info-2';
        $scopeInfo[] = 'here will be scope info-1';
        return $scopeInfo;
    }
}
