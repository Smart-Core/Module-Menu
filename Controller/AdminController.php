<?php

namespace SmartCore\Module\Menu\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use SmartCore\Module\Menu\Entity\Menu;
use SmartCore\Module\Menu\Entity\Item;
use SmartCore\Module\Menu\Form\Type\MenuFormType;
use SmartCore\Module\Menu\Form\Type\ItemFormType;

class AdminController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $form = $this->createForm(MenuFormType::class);

        if ($request->isMethod('POST') and $request->request->has('create')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $menu = $form->getData();
                $menu->setUser($this->getUser());
                $em->persist($menu);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Меню создано.'); // @todo translate

                return $this->redirect($this->generateUrl('smart_module.menu.admin_menu', ['menu_id' => $menu->getId()]));
            }
        }

        return $this->render('MenuModule:Admin:index.html.twig', [
            'menus' => $em->getRepository('MenuModule:Menu')->findAll(),
            'form'   => $form->createView(),
        ]);
    }

    /**
     * Редактирование пункта меню.
     *
     * @param Request $request
     * @param int $item_id
     *
     * @return RedirectResponse|Response
     */
    public function itemAction(Request $request, $item_id)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        /** @var Item $item */
        $item = $em->find('MenuModule:Item', $item_id);

        $form = $this->createForm(ItemFormType::class, $item);

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    $this->getCacheService()->deleteTag('smart_module.menu');
                    $this->get('session')->getFlashBag()->add('success', 'Пункт меню обновлён.'); // @todo translate

                    return $this->redirectToRoute('smart_module.menu.admin_menu', ['menu_id' => $item->getMenu()->getId()]);
                }
            } elseif ($request->request->has('delete')) {
                // @todo безопасное удаление, в частности отключение из нод и удаление всех связаных пунктов меню.
                $em->remove($form->getData());
                $em->flush();

                $this->getCacheService()->deleteTag('smart_module.menu');
                $this->get('session')->getFlashBag()->add('success', 'Пункт меню удалён.');

                return $this->redirectToRoute('smart_module.menu.admin_menu', ['menu_id' => $item->getMenu()->getId()]);
            }
        }

        return $this->render('MenuModule:Admin:item.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Редактирование свойств группы меню.
     *
     * @param Request $request
     * @param int $menu_id
     *
     * @return RedirectResponse|Response
     */
    public function menuEditAction(Request $request, $menu_id)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $menu = $em->find('MenuModule:Menu', $menu_id);

        if (empty($menu)) {
            return $this->redirectToRoute('smart_module.menu.admin');
        }

        $form = $this->createForm(MenuFormType::class, $menu);

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    $this->getCacheService()->deleteTag('smart_module.menu');
                    $this->get('session')->getFlashBag()->add('success', 'Группа меню обновлена.'); // @todo translate

                    return $this->redirectToRoute('smart_module.menu.admin_menu', ['menu_id' => $menu_id]);
                }
            } elseif ($request->request->has('delete')) {
                // @todo безопасное удаление, в частности отключение из нод и удаление всех связаных пунктов меню.
                $em->remove($form->getData());
                $em->flush();

                $this->getCacheService()->deleteTag('smart_module.menu');
                $this->get('session')->getFlashBag()->add('success', 'Группа меню удалена.');

                return $this->redirectToRoute('smart_module.menu.admin');
            }
        }

        return $this->render('MenuModule:Admin:menu_edit.html.twig', [
            'menu' => $menu,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * Редактирование группы меню.
     *
     * @param Request $request
     * @param int $menu_id
     *
     * @return RedirectResponse|Response
     */
    public function menuAction(Request $request, $menu_id)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $menu = $em->find('MenuModule:Menu', $menu_id);

        if (empty($menu)) {
            return $this->redirectToRoute('smart_module.menu.admin');
        }

        $form = $this->createForm(ItemFormType::class, new Item($menu));

        if ($request->isMethod('POST')) {
            if ($request->request->has('create_item')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    /** @var Item $item */
                    $item = $form->getData();
                    $item->setUser($this->getUser());
                    $item->setMenu($menu);

                    $em->persist($item);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('success', 'Пункт меню создан.'); // @todo translate

                    return $this->redirectToRoute('smart_module.menu.admin_menu', ['menu_id' => $menu_id]);
                }
            }
        }

        return $this->render('MenuModule:Admin:menu.html.twig', [
            'menu' => $menu,
            'form' => $form->createView(),
        ]);
    }
}
