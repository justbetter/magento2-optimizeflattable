<?php

namespace JustBetter\OptimizeFlatTables\Plugin\Magento\Eav\Model\Entity;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use JustBetter\OptimizeFlatTables\Plugin\Magento\Eav\Model\Entity\BasePlugin;
use Zend_Db_Expr;

class AttributePlugin extends BasePlugin
{
    public function after_getFlatColumnsDdlDefinition(AbstractAttribute $attribute, array $result): array
    {
        if (!$this->isEnabled() || empty($result[$attribute->getAttributeCode()]['length'])) {
            return $result;
        }

        $result[$attribute->getAttributeCode()]['length'] = match($attribute->getBackendType()) {
            'varchar' => $this->getVarcharLength($attribute),
            'decimal' => $this->getDecimalLength($attribute),
            default => $result[$attribute->getAttributeCode()]['length'],
        };

        return $result;
    }

    public function getVarcharLength(AbstractAttribute $attribute): int
    {
        $connection = $this->getConnection();

        $select = $connection
            ->select()
            ->from($attribute->getBackendTable(), [new Zend_Db_Expr("MAX(LENGTH(value)) AS length")])
            ->where('attribute_id = ?', $attribute->getAttributeId());

        $length = (int)$connection->fetchOne($select);

        return $length + $this->getMargin();
    }

    public function getDecimalLength(AbstractAttribute $attribute): string
    {
        $connection = $this->getConnection();

        $select = $connection
            ->select()
            ->from($attribute->getBackendTable(), [
                new Zend_Db_Expr("MAX(CHAR_LENGTH(TRUNCATE(ABS(value), 0))) AS max"),
                new Zend_Db_Expr("MAX(CHAR_LENGTH(REGEXP_REPLACE(SUBSTRING_INDEX(CAST(value AS CHAR), '.', -1), '0+$', ''))) as min")
            ])
            ->where('attribute_id = ?', $attribute->getAttributeId());

        ['max' => $max, 'min' => $min] = $connection->fetchRow($select);

        return (max($max, 1) + $min) . ',' . $min;
    }
}
