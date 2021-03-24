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

namespace Common\SalesSequence\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceMeta;
use Magento\SalesSequence\Model\ResourceModel\Profile as ResourceProfile;

/**
 * @package Common\SalesSequence
 * @author  Zengliwei <zengliwei@163.com>
 * @url https://github.com/zengliwei/magento2_sales_sequence
 */
class Config
{
    /**
     * @var ResourceMeta
     */
    private ResourceMeta $resourceMeta;

    /**
     * @var ResourceProfile
     */
    private ResourceProfile $resourceProfile;

    /**
     * @var array|null
     */
    private ?array $entityTypes = null;

    /**
     * @param ResourceMeta    $resourceMeta
     * @param ResourceProfile $resourceProfile
     */
    public function __construct(
        ResourceMeta $resourceMeta,
        ResourceProfile $resourceProfile
    ) {
        $this->resourceMeta = $resourceMeta;
        $this->resourceProfile = $resourceProfile;
    }

    /**
     * @return string[]
     * @throws LocalizedException
     */
    public function getEntityTypes()
    {
        if ($this->entityTypes === null) {
            $conn = $this->resourceMeta->getConnection();
            $tblMeta = $conn->getTableName($this->resourceMeta->getMainTable());
            $select = $conn->select()->distinct(true)->from(['meta' => $tblMeta], ['entity_type']);
            $this->entityTypes = $conn->fetchCol($select);
        }
        return $this->entityTypes;
    }

    /**
     * @param int $scopeId
     * @return array
     * @throws LocalizedException
     */
    public function getConfig(int $scopeId = 0)
    {
        $conn = $this->resourceProfile->getConnection();
        $tblMeta = $conn->getTableName($this->resourceMeta->getMainTable());
        $tblProfile = $conn->getTableName($this->resourceProfile->getMainTable());
        $select = $conn->select()
            ->from(['meta' => $tblMeta], ['entity_type'])
            ->join(
                ['profile' => $tblProfile],
                'meta.meta_id = profile.meta_id',
                ['prefix', 'suffix', 'step', 'is_active']
            )
            ->where('meta.store_id = ?', $scopeId);
        return $conn->fetchAll($select);
    }

    /**
     * @param string $entityType
     * @param int    $storeId
     * @param array  $data
     * @throws LocalizedException
     */
    public function updateConfig($entityType, $storeId, array $data)
    {
        $conn = $this->resourceProfile->getConnection();
        $tblMeta = $conn->getTableName($this->resourceMeta->getMainTable());
        $tblProfile = $conn->getTableName($this->resourceProfile->getMainTable());

        $select = $conn->select()
            ->from(['profile' => $tblProfile], ['profile_id'])
            ->join(
                ['meta' => $tblMeta],
                'meta.meta_id = profile.meta_id',
                []
            )
            ->where('meta.entity_type = ?', $entityType)
            ->where('meta.store_id = ?', $storeId);

        if (($profileId = $conn->fetchOne($select))) {
            $conn->update($tblProfile, $data, ['profile_id = ?' => $profileId]);
        }
    }
}
