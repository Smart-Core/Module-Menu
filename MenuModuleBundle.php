<?php

namespace SmartCore\Module\Menu;

use SmartCore\Bundle\CMSBundle\Module\ModuleBundle;
use SmartCore\Module\Menu\DependencyInjection\Compiler\FormPass;
use SmartCore\Module\Menu\Entity\Menu;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MenuModuleBundle extends ModuleBundle
{
    protected $adminMenuBeforeCode = '<i class="fa fa-indent"></i>';

    /**
     * Получить виджеты для рабочего стола.
     *
     * @return array
     */
    public function getDashboard()
    {
        $em     = $this->container->get('doctrine.orm.default_entity_manager');
        $r      = $this->container->get('router');
        $menus  = $em->getRepository(Menu::class)->findAll();

        $data = [
            'title' => 'Меню',
            'items' => [],
        ];

        foreach ($menus as $menu) {
            $data['items']['edit_menu_'.$menu->getId()] = [
                'title' => 'Редактировать меню: <b>'.$menu->getName().'</b>',
                'descr' => '',
                'url' => $r->generate('smart_module.menu.admin_menu', ['menu_id' => $menu->getId()]),
            ];
        }

        return $data;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FormPass());
    }

    /**
     * @return array
     */
    public function getRequiredParams()
    {
        return [
            'menu_id',
        ];
    }
}
