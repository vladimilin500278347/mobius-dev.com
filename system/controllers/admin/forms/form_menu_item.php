<?php

class formAdminMenuItem extends cmsForm {

    public function init($menu_id, $current_id) {

        return array(
            array(
                'title' => LANG_CP_BASIC,
                'type'  => 'fieldset',
                'childs' => array(
                    new fieldCheckbox('is_enabled', array(
                        'title'   => LANG_IS_ENABLED,
                        'default' => 1
                    )),
                    new fieldString('title', array(
                        'title' => LANG_TITLE,
                        'is_clean_disable' => true,
                        'rules' => array(
                            array('required'),
                            array('max_length', 64)
                        )
                    )),
                    new fieldHidden('menu_id', array()),
                    new fieldList('parent_id', array(
                        'title' => LANG_CP_MENU_ITEM_PARENT,
                        'generator' => function ($item) use ($menu_id, $current_id) {

                            $menu_model = cmsCore::getModel('menu');
                            $tree = $menu_model->getMenuItemsTree($menu_id, false);

                            $items = [0 => LANG_ROOT_NODE];

                            if ($tree) {
                                foreach ($tree as $tree_item) {
                                    if (!empty($current_id)) {
                                        if ($tree_item['id'] == $current_id) {
                                            continue;
                                        }
                                    }
                                    $items[$tree_item['id']] = str_repeat('- ', $tree_item['level']) . ' ' . $tree_item['title'];
                                }
                            }

                            return $items;
                        }
                    ))
                )
            ),
            array(
                'type'   => 'fieldset',
                'title'  => LANG_CP_MENU_ITEM_ACTION,
                'childs' => array(
                    new fieldString('url', array(
                        'title' => LANG_CP_MENU_ITEM_ACTION_URL,
                        'hint'  => LANG_CP_MENU_ITEM_ACTION_URL_HINT,
                        'rules' => array(
                            array('max_length', 255)
                        )
                    )),
                    new fieldList('options:target', array(
                        'title' => LANG_CP_MENU_ITEM_ACTION_TARGET,
                        'items' => array(
                            '_self'   => LANG_CP_MENU_ITEM_TARGET_SELF,
                            '_blank'  => LANG_CP_MENU_ITEM_TARGET_BLANK,
                            '_parent' => LANG_CP_MENU_ITEM_TARGET_PARENT,
                            '_top'    => LANG_CP_MENU_ITEM_TARGET_TOP,
                        )
                    ))
                )
            ),
            array(
                'type'   => 'fieldset',
                'title'  => LANG_OPTIONS,
                'childs' => array(
                    new fieldString('options:class', array(
                        'title' => LANG_CSS_CLASS,
                    )),
                    new fieldString('options:icon', array(
                        'title' => LANG_CP_MENU_ITEM_ICON,
                        'suffix' => '<a href="#" class="icms-icon-select" data-href="'.href_to('admin', 'settings', ['theme', cmsConfig::get('template'), 'icon_list']).'"><span>'.LANG_CP_ICON_SELECT.'</span></a>',
                    )),
                    new fieldCheckbox('options:hide_title', array(
                        'title' => LANG_CP_MENU_ITEM_HIDE_TITLE,
                        'visible_depend' => array('options:icon' => array('hide' => array('')))
                    ))
                )
            ),
            'access' => array(
                'type'   => 'fieldset',
                'title'  => LANG_PERMISSIONS,
                'childs' => array(
                    new fieldListGroups('groups_view', array(
                        'title'       => LANG_SHOW_TO_GROUPS,
                        'show_all'    => true,
                        'show_guests' => true
                    )),
                    new fieldListGroups('groups_hide', array(
                        'title'       => LANG_HIDE_FOR_GROUPS,
                        'show_all'    => false,
                        'show_guests' => true
                    ))
                )
            )
        );

    }

}
