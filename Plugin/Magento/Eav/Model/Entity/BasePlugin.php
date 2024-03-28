<?php

namespace JustBetter\OptimizeFlatTables\Plugin\Magento\Eav\Model\Entity;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Zend_Db_Expr;

class BasePlugin
{
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected Attribute $attributeResource
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('justbetter/flat_tables/enabled');
    }

    public function getConnection(): false|AdapterInterface
    {
        return $this->attributeResource->getConnection();
    }

    public function getMargin(string $type = 'varchar'): int
    {
        return (int)$this->scopeConfig->getValue('justbetter/flat_tables/' . $type . '_margin');
    }
}
