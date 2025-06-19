# JustBetter OptimizeFlatTables

In Magento 2 when you want to index many text fields to the flat tables you could run into the `Syntax error or access violation: 1118 Row size too large (> 8126). Changing some columns to TEXT or BLOB may help.` error.

The reason being every field taking up length for 255 characters while this is often not used.

This module aims to reduce the chance of this happening by checking the product attributes with backend type `varchar` used in the flat table, and determine the actual length of the column. Instead of taking the default value of `255`.

## Installation

Run

```shell
composer require justbetter/magento2-optimizeflattable
```

After enabling the module your problems indexing to the flat tables should be resolved!