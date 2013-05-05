<?php

namespace SmartCore\Module\Menu\Controller;

use SmartCore\Bundle\EngineBundle\Module\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected $menu_group_id = 'A';
    protected $max_depth = 0;
    protected $css_class = '';
    protected $tpl = '';

    protected $_tree_level = 0;
    protected $_folder_tree_list_arr = [];
    //protected $tree_link = [];
    
    protected $only_is_active = true;
    protected $selected_inheritance = true;

    /**
     * Конструктор.
     */
    protected function init()
    {
        $this->View->setOptions(array(
            'bundle' => 'MenuModule::',
        ));
    }
}
