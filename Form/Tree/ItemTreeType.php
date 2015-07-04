<?php

namespace SmartCore\Module\Menu\Form\Tree;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemTreeType extends DoctrineType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $loader = function (Options $options) {
            $loader = $this->getLoader($options['em'], $options['query_builder'], $options['class']);
            $loader->setMenu($options['menu']);

            return $loader;
        };

        $resolver->setDefaults([
            'choice_label' => 'form_title',
            'class'        => 'MenuModule:Item',
            'loader'       => $loader,
            'menu'         => null,
        ]);
    }

    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new ItemLoader($manager, $queryBuilder, $class);
    }

    public function getName()
    {
        return 'smart_module_menu_item_tree';
    }
}
