<?php

namespace SmartCore\Module\Menu\Form\Type;

use SmartCore\Bundle\CMSBundle\Form\Tree\FolderTreeType;
use SmartCore\Module\Menu\Entity\Menu;
use SmartCore\Module\Menu\Entity\Item;
use SmartCore\Module\Menu\Form\Tree\ItemTreeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

class ItemFormType extends AbstractType
{
    /**
     * @var Menu
     */
    protected $menu;

    /**
     * @param Menu|null $menu
     */
    public function __construct(Menu $menu = null)
    {
        $this->menu = $menu;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($this->menu) and $options['data'] instanceof Item) {
            $this->menu = $options['data']->getMenu();
        }

        if ($options['data'] instanceof Menu) {
            $this->menu = $options['data'];
        }

        $builder
            ->add('is_active')
            ->add('parent_item', ItemTreeType::class, [ //'smart_module_menu_item_tree'
                'menu'     => $this->menu,
                'required' => false,
            ])
            ->add('folder', FolderTreeType::class, ['required' => false]) // 'cms_folder_tree'
            ->add('title',  null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('url')
            ->add('description')
            ->add('position')
            ->add('open_in_new_window')
        ;

        if ($this->menu) {
            $properties = Yaml::parse($this->menu->getProperties());

            if (is_array($properties)) {
                $builder->add($builder->create(
                    'properties',
                    new ItemPropertiesFormType($properties),
                    ['required' => false]
                ));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }

    public function getName()
    {
        return 'smart_module_menu_item';
    }
}
