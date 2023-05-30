<?php

namespace Swissup\ScopesettingsHelp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_ENABLED = 'scopesettings_help/general/enabled';

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
