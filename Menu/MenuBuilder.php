<?php

namespace SmartCore\Module\Menu\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use SmartCore\Module\Menu\Entity\Menu;
use SmartCore\Module\Menu\Entity\MenuItem;

class MenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var Menu
     */
    protected $menu;

    /**
     * Режим администрирования.
     *
     * @var bool
     */
    protected $is_admin;

    /**
     * CSS стиль меню.
     *
     * @var string
     */
    protected $css_class;

    /**
     * Глубина вложенности.
     *
     * @var int
     */
    protected $depth;

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

        if (empty($this->menu)) {
            return $menu;
        }

        if (!empty($this->css_class)) {
            $menu->setChildrenAttribute('class', $this->css_class);
        }

        $this->addChild($menu);

        return $menu;
    }

    /**
     * Обработка конфига.
     *
     * @param array $options
     */
    protected function processConfig(array $options)
    {
        $this->em = $this->container->get('doctrine.orm.entity_manager');

        $defaul_options = $options + [
            'css_class' => null,
            'depth'     => null,
            'menu'      => null,
            'is_admin'  => false,
        ];

        foreach ($defaul_options as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Рекурсивное построение дерева.
     *
     * @param ItemInterface $menu
     * @param MenuItem|null $parent_item
     */
    protected function addChild(ItemInterface $menu, MenuItem $parent_item = null)
    {
        $items = (null == $parent_item)
            ? $this->em->getRepository(MenuItem::class)->findByParent($this->menu, null)
            : $parent_item->getChildren();

        /** @var MenuItem $item */
        foreach ($items as $item) {
            if ($this->is_admin) {
                $uri = $this->container->get('router')->generate('smart_module.menu.admin_item', ['item_id' => $item->getId()]);
            } else {
                $itemUrl = $item->getUrl();

                if ((null === $item->getFolder() or !$item->getFolder()->isActive()) and empty($itemUrl)) {
                    continue;
                }

                $uri = $item->getFolder()
                    ? $this->container->get('cms.folder')->getUri($item->getFolder())
                    : $itemUrl;
            }

            $item_title = $this->is_admin ? (string) $item.' (position: '.$item->getPosition().')' : (string) $item;
            $item_title = isset($menu[$item_title]) ? $item_title.' ('.$item->getId().')' : $item_title;

            if ($this->is_admin or $item->getIsActive()) {
                $new_item = $menu->addChild($item_title, ['uri' => $uri]);
                $new_item
                    ->setAttributes([
                        //'class' => 'my_item', // @todo аттрибуты для пунктов меню.
                        'title' => $item->getDescription(),
                    ])
                    ->setExtras($item->getProperties())
                    ->setExtra('translation_domain', false)
                ;

                if (!$this->is_admin and $item->getOpenInNewWindow()) {
                    $new_item->setLinkAttribute('target', '_blank');
                }

                if ($this->is_admin and (!$item->getIsActive() or (null != $item->getFolder() and !$item->getFolder()->isActive()))) {
                    $new_item->setAttribute('style', 'text-decoration: line-through;');
                }
            } else {
                continue;
            }

            $this->addChild($menu[$item_title], $item);
        }
    }
}
