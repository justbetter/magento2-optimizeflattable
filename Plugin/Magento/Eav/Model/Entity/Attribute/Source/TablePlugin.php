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
        if (!$this->isEnabled()) {
            return $result;
        }

        $attribute = $subject->getAttribute();

        if ($attribute->getFrontend()->getInputType() !== 'multiselect' && $connection = $this->attributeResource->getConnection()) {
            $optionTable = $connection->getTableName('eav_attribute_option');
            $optionValueTable = $connection->getTableName('eav_attribute_option_value');
            $select = $connection
                ->select()
                ->from($optionTable, [new Zend_Db_Expr("MAX(LENGTH($optionValueTable.value)) AS length")])
                ->joinLeft($optionValueTable, $optionTable.'.option_id = '.$optionValueTable.'.option_id', 'value')
                ->where($optionTable.'.attribute_id = ?', $attribute->getAttributeId());

            $length = (int)$connection->fetchOne($select);

            $result[$attribute->getAttributeCode() . '_value']['length'] = $length + $this->getMargin();
        }

        if($result[$attribute->getAttributeCode()]['type'] === 'text' && $result[$attribute->getAttributeCode()]['length'] == 255 && $connection = $this->attributeResource->getConnection()) {
            $select = $connection
                ->select()
                ->from($attribute->getBackendTable(), [new Zend_Db_Expr("MAX(LENGTH(value)) AS length")])
                ->where('attribute_id = ?', $attribute->getAttributeId());

            $length = (int)$connection->fetchOne($select);

            $result[$attribute->getAttributeCode()]['length'] = $length + $this->getMargin();
        }

        return $result;
    }
}
