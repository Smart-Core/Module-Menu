<?php

namespace SmartCore\Module\Menu\Controller;

use SmartCore\Bundle\EngineBundle\Module\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected $menu_group_id = null;
    protected $depth = 0;
    protected $css_class = '';
    protected $tpl = ''; // @todo

    protected $selected_inheritance = true;

    protected function init()
    {
        $this->View->setEngine('echo');
    }
}
