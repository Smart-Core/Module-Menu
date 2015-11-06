<?php

namespace SmartCore\Module\Menu\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    public function indexAction(Request $request)
    {
        $current_folder_path = $this->get('cms.context')->getCurrentFolderPath();

        $cache_key = md5('smart_module.menu'.$current_folder_path.$this->node->getId());

        if (false == $menu = $this->getCacheService()->get($cache_key)) {
            // Хак для Menu\RequestVoter
            $request->attributes->set('__selected_inheritance', $this->selected_inheritance);
            $request->attributes->set('__current_folder_path', $current_folder_path);

            $menu = $this->get('twig')->render('MenuModule::menu.html.twig', [
                'css_class'     => $this->css_class,
                'current_class' => $this->current_class,
                'depth'         => $this->depth,
                'menu'          => $this->getDoctrine()->getManager()->find('MenuModule:Menu', $this->menu_id),
            ]);

            //$menu = $this->get('html.tidy')->prettifyFragment($menu);

            $this->getCacheService()->set($cache_key, $menu, ['smart_module.menu', 'folder', 'node_'.$this->node->getId()]);

            $request->attributes->remove('__selected_inheritance');
            $request->attributes->remove('__current_folder_path');
        }

        $this->node->addFrontControl('edit')
            ->setTitle('Редактировать меню')
            ->setUri($this->generateUrl('smart_module.menu.admin_menu', [
                'menu_id' => $this->menu_id,
            ]));

        return new Response($menu);
    }
}
