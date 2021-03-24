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

namespace Common\SalesSequence\Plugin;

use Common\SalesSequence\Helper\Config as ConfigHelper;
use Magento\Config\Model\Config\Structure\Converter;
use Magento\Framework\Exception\LocalizedException;

/**
 * @package Common\SalesSequence
 * @author  Zengliwei <zengliwei@163.com>
 * @url https://github.com/zengliwei/magento2_sales_sequence
 */
class ConfigStructureConverter
{
    /**
     * @var ConfigHelper
     */
    private ConfigHelper $configHelper;

    /**
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * @param Converter $subject
     * @param array     $result
     * @return array
     * @throws LocalizedException
     */
    public function afterConvert(Converter $subject, array $result)
    {
        if (!isset($result['config']['system']['sections']['sales']['children']['sequence']['children']['template'])) {
            return $result;
        }
        foreach ($this->configHelper->getEntityTypes() as $entityType) {
            $group = $result['config']['system']['sections']['sales']['children']['sequence']['children']['template'];
            $group['id'] = $entityType;
            $group['label'] .= $entityType;
            foreach ($group['children'] as &$field) {
                $field['path'] = 'sales/sequence/' . $entityType;
            }
            $result['config']['system']['sections']['sales']['children']['sequence']['children'][$entityType] = $group;
        }
        unset($result['config']['system']['sections']['sales']['children']['sequence']['children']['template']);
        return $result;
    }
}
