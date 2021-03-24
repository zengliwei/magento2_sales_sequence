<?php
/*
 * Copyright (c) 2020 Zengliwei
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFINGEMENT. IN NO EVENT SHALL THE AUTHORS
 * OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Common\SalesSequence\Observer;

use Common\SalesSequence\Helper\Config as ConfigHelper;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @package Common\SalesSequence
 * @author  Zengliwei <zengliwei@163.com>
 * @url https://github.com/zengliwei/magento2_sales_sequence
 */
class UpdateSalesSequenceSettings implements ObserverInterface
{
    /**
     * @var ReinitableConfigInterface
     */
    private ReinitableConfigInterface $config;

    /**
     * @var ConfigHelper
     */
    private ConfigHelper $configHelper;

    /**
     * @param ReinitableConfigInterface $config
     * @param ConfigHelper              $configHelper
     */
    public function __construct(
        ReinitableConfigInterface $config,
        ConfigHelper $configHelper
    ) {
        $this->config = $config;
        $this->configHelper = $configHelper;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $website = $observer->getDataByKey('website');
        $store = $observer->getDataByKey('store');
        if (!empty($website)) {
            return;
        }

        if ($store) {
            $scope = ScopeInterface::SCOPE_STORE;
            $storeId = $store;
        } else {
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $storeId = 0;
        }

        $fields = ['prefix', 'suffix', 'step', 'is_active'];
        foreach ($this->configHelper->getEntityTypes() as $entityType) {
            $data = [];
            foreach ($fields as $field) {
                $value = $this->config->getValue(
                    'sales/sequence/' . $entityType . '/' . $field,
                    $scope,
                    $storeId
                );
                if (!empty($value)) {
                    $data[$field] = $value;
                }
            }
            if (!empty($data)) {
                $this->configHelper->updateConfig($entityType, $storeId, $data);
            }
        }
    }
}
