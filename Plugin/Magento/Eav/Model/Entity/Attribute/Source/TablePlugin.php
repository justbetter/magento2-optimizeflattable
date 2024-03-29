<?php

namespace JustBetter\OptimizeFlatTables\Plugin\Magento\Eav\Model\Entity\Attribute\Source;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use JustBetter\OptimizeFlatTables\Plugin\Magento\Eav\Model\Entity\BasePlugin;
use Zend_Db_Expr;

class TablePlugin extends BasePlugin
{

    public function afterGetFlatColumns (\Magento\Eav\Model\Entity\Attribute\Source\Table $subject, array $result) : array
    {
        if ($this->isEnabled() && $connection = $this->attributeResource->getConnection()) {
            $optionTable = $connection->getTableName('eav_attribute_option');
            $optionValueTable = $connection->getTableName('eav_attribute_option_value');
            $select = $connection
                ->select()
                ->from($optionTable, [new Zend_Db_Expr("MAX(LENGTH($optionValueTable.value)) AS length")])
                ->joinLeft($optionValueTable, $optionTable.'.option_id = '.$optionValueTable.'.option_id', 'value')
                ->where($optionTable.'.attribute_id = ?', $subject->getAttribute()->getAttributeId());

            $length = (int)$connection->fetchOne($select);

            $result[$subject->getAttribute()->getAttributeCode() . '_value']['length'] = $length + $this->getMargin();
        }
        return $result;
    }
}
