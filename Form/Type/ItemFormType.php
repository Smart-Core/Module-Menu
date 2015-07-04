<?php

namespace SmartCore\Module\Menu\Form\Type;

use SmartCore\Module\Menu\Entity\Menu;
use SmartCore\Module\Menu\Entity\Item;
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
     * Constructor.
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

        $builder
            ->add('is_active')
            ->add('parent_item', 'smart_module_menu_item_tree', [
                'menu'     => $this->menu,
                'required' => false,
            ])
            ->add('folder', 'cms_folder_tree', ['required' => false])
            ->add('title',  null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('url')
            ->add('description')
            ->add('position')
            ->add('open_in_new_window')
        ;

        if ($options['data']->getMenu() instanceof Menu) {
            $this->menu = $options['data']->getMenu();
        }

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
            'data_class' => 'SmartCore\Module\Menu\Entity\Item',
        ]);
    }

    public function getName()
    {
        return 'smart_module_menu_item';
    }
}
