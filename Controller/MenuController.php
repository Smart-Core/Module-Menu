<?php

namespace SmartCore\Module\Menu\Controller;

use SmartCore\Bundle\EngineBundle\Response;

class MenuController extends Controller
{
    public function indexAction()
    {
        $cache_key = md5($this->get('request')->getRequestUri() . md5(serialize($this->node)));

        if (null == $this->View->menu = $this->getCache($cache_key)) {
            $em = $this->get('doctrine.orm.default_entity_manager');

            $this->get('request')->attributes->set('__selected_inheritance', $this->selected_inheritance);

            $this->View->menu = $this->renderView('MenuModule::menu.html.twig', [
                'group' => $em->find('MenuModule:Group', $this->group_id),
                'css_class' => $this->css_class,
                'current_class' => '',
                'depth' => $this->depth,
            ]);

            $this->setCache($cache_key, $this->View->menu);

            $this->get('request')->attributes->set('__selected_inheritance', false);
        }

        return new Response($this->View);
    }
}
