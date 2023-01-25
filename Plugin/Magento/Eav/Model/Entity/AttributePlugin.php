<?php

namespace JustBetter\OptimizeFlatTables\Plugin\Magento\Eav\Model\Entity;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend_Db_Expr as Zend_Db_Expr;

class AttributePlugin
{
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected Attribute            $attributeResource
    )
    {
    }

    public function after_getFlatColumnsDdlDefinition(AbstractAttribute $subject, array $result): array
    {
        if ($this->isEnabled() && $subject->getBackendType() === 'varchar') {
            $connection = $this->attributeResource->getConnection();
            $select = $connection
                ->select()
                ->from($subject->getBackendTable(), [new Zend_Db_Expr("MAX(LENGTH(value)) AS length")])
                ->where('attribute_id = ?', $subject->getAttributeId());

            $length = (int)$connection->fetchOne($select);

            $result[$subject->getAttributeCode()]['length'] = $length + $this->getMargin();
        }
        return $result;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('justbetter/flat_tables/enabled');
    }

    public function getMargin($type = 'varchar'): int
    {
        return (int)$this->scopeConfig->getValue('justbetter/flat_tables/' . $type . '_margin');
    }
}
