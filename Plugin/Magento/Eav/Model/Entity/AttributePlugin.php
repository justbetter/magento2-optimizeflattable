<?php

namespace JustBetter\OptimizeFlatTables\Plugin\Magento\Eav\Model\Entity;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use JustBetter\OptimizeFlatTables\Plugin\Magento\Eav\Model\Entity\BasePlugin;
use Zend_Db_Expr;

class AttributePlugin extends BasePlugin
{

    public function after_getFlatColumnsDdlDefinition(AbstractAttribute $subject, array $result): array
    {
        if ($this->isEnabled() && $subject->getBackendType() === 'varchar' && $connection = $this->getConnection()) {
            $select = $connection
                ->select()
                ->from($subject->getBackendTable(), [new Zend_Db_Expr("MAX(LENGTH(value)) AS length")])
                ->where('attribute_id = ?', $subject->getAttributeId());

            $length = (int)$connection->fetchOne($select);

            $result[$subject->getAttributeCode()]['length'] = $length + $this->getMargin();
        }
        return $result;
    }
}
