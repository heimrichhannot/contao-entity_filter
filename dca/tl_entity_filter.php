<?php

$GLOBALS['TL_DCA']['tl_entity_filter'] = array(
    'fields' => array(
        'connective'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_filter']['connective'],
            'inputType' => 'select',
            'options'   => array(
                \HeimrichHannot\Haste\Database\QueryHelper::SQL_CONDITION_OR,
                \HeimrichHannot\Haste\Database\QueryHelper::SQL_CONDITION_AND
            ),
            'reference' => &$GLOBALS['TL_LANG']['MSC']['connectives'],
            'eval'      => array('tl_class' => 'w50', 'style' => 'width: 50px', 'includeBlankOption' => true),
        ),
        'bracketLeft'  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_filter']['bracketLeft'],
            'inputType' => 'checkbox',
            'eval'      => array('tl_class' => 'w50'),
        ),
        'field'        => array(
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_filter']['field'],
            'inputType'        => 'select',
            'options_callback' => array('HeimrichHannot\EntityFilter\Backend\EntityFilter', 'getFieldsAsOptions'),
            'eval'             => array('tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true, 'mandatory' => true, 'style' => 'width: 350px'),
        ),
        'operator'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_filter']['operator'],
            'inputType' => 'select',
            'options'   => \HeimrichHannot\Haste\Database\QueryHelper::OPERATORS,
            'reference' => &$GLOBALS['TL_LANG']['MSC']['operators'],
            'eval'      => array('tl_class' => 'w50', 'style' => 'width: 100px'),
        ),
        'value'        => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_filter']['value'],
            'inputType' => 'text',
            'eval'      => array('maxlength' => 255, 'tl_class' => 'w50', 'style' => 'width: 200px'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'bracketRight' => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_filter']['bracketRight'],
            'inputType' => 'checkbox',
            'eval'      => array('tl_class' => 'w50'),
        ),
    ),
);