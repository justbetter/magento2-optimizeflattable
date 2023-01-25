# JustBetter OptimizeFlatTables

This module checks the product attributes with backend type `varchar` used in the flat table and determines the length of the column. Instead of taking the default value of `255`.

If you have a lot of product attributes used in the flat table it could result in problems with the "Row Size being too large".
