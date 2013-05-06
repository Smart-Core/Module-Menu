<?php

namespace SmartCore\Module\Menu\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use SmartCore\Module\Menu\Entity\Group;
use SmartCore\Module\Menu\Entity\Item;

class MenuBuilder extends ContainerAware
{
    /** @var \Doctrine\ORM\EntityManager $em */
    protected $em;

    /** @var Group $group */
    protected $group;

    /**
     * Режим администрирования.
     * @var bool
     */
    protected $is_admin;

    /**
     * CSS стиль меню.
     * @var string
     */
    protected $css_class;

    /**
     * Построение полной структуры, включая ноды.
     *
     * @param FactoryInterface  $factory
     * @param array             $options
     *
     * @return ItemInterface
     */
    public function full(FactoryInterface $factory, array $options)
    {
        $this->processConfig($options);

        $menu = $factory->createItem('menu');

        if (empty($this->group)) {
            return $menu;
        }

        if (!empty($this->css_class)) {
            $menu->setChildrenAttribute('class', $this->css_class);
        }

        $this->addChild($menu);

        return $menu;
    }

    /**
     * Обработка конфига
     * @param array $options
     */
    protected function processConfig(array $options)
    {
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');

        $defaul_options = $options + [
            'group'     => null,
            'is_admin'  => false,
            'max_depth' => null,
            'css_class' => null,
        ];

        foreach ($defaul_options as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Рекурсивное построение дерева.
     *
     * @param ItemInterface $menu
     * @param Folder        $parent_folder
     */
    protected function addChild(ItemInterface $menu, Item $parent_item = null)
    {
        if (null == $parent_item) {
            $items = $this->em->getRepository('MenuModule:Item')->findByParent(null);
        } else {
            $items = $parent_item->getChildren();
        }

        /** @var Item $item */
        foreach ($items as $item) {
            if ($this->is_admin) {
                $uri = $this->container->get('router')->generate('cmf_admin_module_manage', [
                    'module' => 'Menu',
                    'slug' => 'item/' . $item->getId(),
                ]);
            } else {
                $uri = $this->container->get('engine.folder')->getUri($item->getFolder()->getId());
            }

            $title = (string) $item;

            if ($this->is_admin or $item->getIsActive()) {
                $new_item = $menu->addChild($title, ['uri' => $uri]);
                $new_item->setAttributes([
                    //'class' => 'my_item',
                    'title' => $item->getDescr(),
                ]);

                if ($this->is_admin and !$item->getIsActive()) {
                    $new_item->setAttribute('style', 'text-decoration: line-through;');
                }
            } else {
                continue;
            }

            /** @var ItemInterface $sub_menu */
            $sub_menu = $menu[$title];

            $this->addChild($sub_menu, $item);
        }
    }
}
