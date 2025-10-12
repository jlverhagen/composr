<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    classified_ads
 */

/**
 * Hook class.
 */
class Hook_database_manifest_classified_ads
{

    /**
     * Database manifest for this addon.
     * This is automatically maintained by the software release tools if you are using it.
     *
     * @return array Map of tables, indices, foreign keys, and privileges
     */
    public function db_meta() : array
    {
        return [
            'tables' => [
                'ecom_classifieds_prices' => [
                    'addon' => 'classified_ads',
                    'fields' => [
                        'id' => '*AUTO',
                        'c_catalogue_name' => 'ID_TEXT',
                        'c_days' => 'INTEGER',
                        'c_price' => 'REAL',
                        'c_label' => 'SHORT_TRANS',
                    ],
                ],
            ],
            'indices' => [
                'ecom_classifieds_prices__#c_label' => [
                    'addon' => 'classified_ads',
                    'name' => 'c_label',
                    'table' => 'ecom_classifieds_prices',
                    'fields' => [
                        0 => 'c_label',
                    ],
                    'is_full_text' => true,
                ],
                'ecom_classifieds_prices__c_catalogue_name' => [
                    'addon' => 'classified_ads',
                    'name' => 'c_catalogue_name',
                    'table' => 'ecom_classifieds_prices',
                    'fields' => [
                        0 => 'c_catalogue_name',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [
                'ecom_classifieds_prices__c_catalogue_name||catalogues__c_name' => [
                    'addon' => 'classified_ads',
                    'from_table' => 'ecom_classifieds_prices',
                    'from_field' => 'c_catalogue_name',
                    'to_table' => 'catalogues',
                    'to_field' => 'c_name',
                    'special_values' => [],
                ],
            ],
            'privileges' => [],
        ];
    }
}
