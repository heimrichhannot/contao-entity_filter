<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Dennis Patzer
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\EntityFilter\Backend;

use HeimrichHannot\Haste\Dca\General;

class EntityFilter extends \Backend
{
    public static function addFilterToDca($strName, $strParentTable, $strChildTable)
    {
        \Controller::loadDataContainer($strParentTable);
        \Controller::loadDataContainer('tl_entity_filter');
        \System::loadLanguageFile('tl_entity_filter');
        $arrDca = &$GLOBALS['TL_DCA'][$strParentTable];

        $arrDca['fields'][$strName] = array(
            'label'     => &$GLOBALS['TL_LANG'][$strParentTable][$strName],
            'exclude'   => true,
            'inputType' => 'multiColumnEditor',
            'eval'      => array(
                'multiColumnEditor' => array(
                    'class'  => 'entity-filter',
                    'fields' => $GLOBALS['TL_DCA']['tl_entity_filter']['fields'],
                    'table'  => $strChildTable,
                ),
            ),
            'sql'       => "blob NULL",
        );
    }

    public static function addListToDca($strName, $strParentTable, $strFilterFieldname, $strChildTable, $arrFields = array())
    {
        \Controller::loadDataContainer($strParentTable);
        $arrDca = &$GLOBALS['TL_DCA'][$strParentTable];

        $arrDca['fields'][$strName] = array(
            'label'     => &$GLOBALS['TL_LANG'][$strParentTable][$strName],
            'exclude'   => true,
            'inputType' => 'listWidget',
            'eval'      => array(
                'listWidget' => array(
                    'items_callback'        => array('HeimrichHannot\EntityFilter\Backend\EntityFilter', 'getItems'),
                    'headerFields_callback' => array('HeimrichHannot\EntityFilter\Backend\EntityFilter', 'getHeaderFields'),
                    'filterField'           => $strFilterFieldname,
                    'fields'                => $arrFields,
                    'table'                 => $strChildTable,
                ),
            ),
        );
    }

    public static function addFilterCopierToDca(
        $strName,
        $strParentTable,
        $strFieldTable,
        $strFieldname,
        $arrOptionsCallback = array('HeimrichHannot\FieldValueCopier\Backend\FieldValueCopier', 'getOptions')
    ) {
        \Controller::loadDataContainer($strParentTable);
        $arrDca = &$GLOBALS['TL_DCA'][$strParentTable];

        $arrDca['fields'][$strName] = array(
            'exclude'   => true,
            'inputType' => 'fieldValueCopier',
            'eval'      => array(
                'fieldValueCopier' => array(
                    'table' => $strFieldTable,
                    'field' => $strFieldname,
                    'options_callback' => $arrOptionsCallback
                )
            )
        );
    }

    public static function getExistingFilters(\DataContainer $objDc, $objWidget)
    {
        if (!($strTable = $objDc->table) || !($strField = $objDc->field))
        {
            return array();
        }

        $arrDca = $GLOBALS['TL_DCA'][$strTable]['fields'][$objDc->field];


        if (isset($arrDca['eval']['listWidget']['table']))
        {
            // build query
            $strFilter = $arrDca['eval']['listWidget']['filterField'];

            if (is_array($arrDca['eval']['listWidget']['fields']) && !empty($arrListDca['eval']['listWidget']['fields']))
            {
                $strFields = implode(',', $arrListDca['eval']['listWidget']['fields']);
            }
            else
            {
                $strFields = '*';
            }

            $strQuery = 'SELECT ' . $strFields . ' FROM ' . $arrListDca['eval']['listWidget']['table'] . ' WHERE ';
            list($strWhere, $arrValues) = \HeimrichHannot\EntityFilter\EntityFilter::computeSqlCondition(
                deserialize($objDc->activeRecord->{$strFilter}, true)
            );

            // get items
            $objItems = \Database::getInstance()->prepare($strQuery . $strWhere)->execute($arrValues);
            $arrItems = array();

            if ($objItems !== null)
            {
                while ($objItems->next())
                {
                    $arrItems[] = $objItems->row();
                }
            }

            return $arrItems;
        }
        else
        {
            throw new \Exception("No 'table' set in $objDc->table.$objDc->field's eval array.");
        }
    }

    public static function getItems(\DataContainer $objDc, $objWidget)
    {
        if (!($strTable = $objDc->table) || !($strField = $objDc->field))
        {
            return array();
        }

        $arrListDca = $GLOBALS['TL_DCA'][$strTable]['fields'][$objDc->field];

        if (isset($arrListDca['eval']['listWidget']['table']))
        {
            // build query
            $strFilter = $arrListDca['eval']['listWidget']['filterField'];

            if (is_array($arrListDca['eval']['listWidget']['fields']) && !empty($arrListDca['eval']['listWidget']['fields']))
            {
                $strFields = implode(',', $arrListDca['eval']['listWidget']['fields']);
            }
            else
            {
                $strFields = '*';
            }

            $strQuery = 'SELECT ' . $strFields . ' FROM ' . $arrListDca['eval']['listWidget']['table'];
            list($strWhere, $arrValues) = \HeimrichHannot\EntityFilter\EntityFilter::computeSqlCondition(
                deserialize($objDc->activeRecord->{$strFilter}, true)
            );

            // get items
            $objItems = \Database::getInstance()->prepare($strQuery . ($strWhere ? ' WHERE ' . $strWhere : ''))->execute($arrValues);
            $arrItems = array();

            if ($objItems !== null)
            {
                while ($objItems->next())
                {
                    $arrItems[] = $objItems->row();
                }
            }

            return $arrItems;
        }
        else
        {
            throw new \Exception("No 'table' set in $objDc->table.$objDc->field's eval array.");
        }
    }

    public static function getHeaderFields(\DataContainer $objDc, $objWidget)
    {
        if (!($strTable = $objDc->table) || !($strField = $objDc->field))
        {
            return array();
        }

        \Controller::loadDataContainer($strTable);

        $arrDca      = $GLOBALS['TL_DCA'][$strTable]['fields'][$objDc->field]['eval']['listWidget'];
        $arrChildDca = $GLOBALS['TL_DCA'][$arrDca['table']];

        if (!isset($arrDca['fields']) || empty($arrDca['fields']))
        {
            throw new \Exception("No 'fields' set in $objDc->table.$objDc->field's eval array.");
        }

        // add field labels
        return array_combine(
            $arrDca['fields'],
            array_map(
                function ($val) use ($arrChildDca)
                {
                    return $arrChildDca['fields'][$val]['label'][0] ?: $val;
                },
                $arrDca['fields']
            )
        );
    }

    public static function getFieldsAsOptions(\DataContainer $objDc)
    {
        if (!($strTable = $objDc->table))
        {
            return array();
        }

        \Controller::loadDataContainer($strTable);

        if (isset($GLOBALS['TL_DCA'][$strTable]['fields'][$objDc->field]['eval']['multiColumnEditor']['table']))
        {
            $strChildTable = $GLOBALS['TL_DCA'][$strTable]['fields'][$objDc->field]['eval']['multiColumnEditor']['table'];
            $arrFields     = General::getFields($strChildTable);

            // add table to field values
            return array_combine(
                array_map(
                    function ($val) use ($strChildTable)
                    {
                        return $strChildTable . '.' . $val;
                    },
                    array_keys($arrFields)
                ),
                array_values($arrFields)
            );
        }
        else
        {
            throw new \Exception("No 'table' set in $objDc->table.$objDc->field's eval array.");
        }
    }
}