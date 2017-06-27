<?php

namespace HeimrichHannot\EntityFilter;


use HeimrichHannot\Haste\Database\QueryHelper;

class EntityFilter
{
    /**
     * @param array $arrConditions The array containing arrays of the form ['field' => 'name', 'operator' => '=', 'value' => 'value']
     * @return array Returns array($strCondition, $arrValues)
     */
    public static function computeSqlCondition(array $arrConditions, $strTable)
    {
        $strCondition = '';
        $arrValues = [];

        // a condition can't start with a logical connective!
        if (isset($arrCondition[0]['connective']))
        {
            $arrCondition[0]['connective'] = '';
        }

        foreach ($arrConditions as $arrCondition)
        {
            list($strClause, $arrClauseValues) = QueryHelper::computeCondition($arrCondition['field'], $arrCondition['operator'], $arrCondition['value'], $strTable);
            $strCondition .= ' ' . $arrCondition['connective'] . ' ' . ($arrCondition['bracketLeft'] ? '(' : '') . $strClause . ($arrCondition['bracketRight'] ? ')' : '');
            $arrValues = array_merge($arrValues, $arrClauseValues);
        }

        return [trim($strCondition), $arrValues];
    }
}
