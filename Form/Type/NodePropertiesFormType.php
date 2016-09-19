<?php

namespace SmartCore\Module\Menu\Form\Type;

use SmartCore\Bundle\CMSBundle\Module\AbstractNodePropertiesFormType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class NodePropertiesFormType extends AbstractNodePropertiesFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('depth',          IntegerType::class,  ['attr' => ['autofocus' => 'autofocus'], 'required' => false])
            ->add('css_class',      TextType::class,     ['required' => false])
            ->add('current_class',  TextType::class,     ['required' => false])
            ->add('selected_inheritance', CheckboxType::class, ['required' => false])
            ->add('menu_id',        ChoiceType::class,   ['choices' => $this->getChoicesByEntity('MenuModule:Menu')])
        ;
    }

    public function getName()
    {
        return 'smart_module_menu_node_properties';
    }
}
