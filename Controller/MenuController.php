<?php

namespace SmartCore\Module\Menu\Controller;

use SmartCore\Bundle\EngineBundle\Response;

class MenuController extends Controller
{
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $this->View->menu = $this->renderView('MenuModule::menu.html.twig', [
            'group' => $em->find('MenuModule:Group', $this->menu_group_id),
            'css_class' => $this->css_class,
            'current_class' => '',
            'depth' => $this->depth,
        ]);

        return new Response($this->View);
    }
}
