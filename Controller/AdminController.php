<?php

namespace SmartCore\Module\Menu\Controller;

use Symfony\Component\HttpFoundation\Request;
use SmartCore\Bundle\EngineBundle\Response;
use SmartCore\Module\Menu\Entity\Group;
use SmartCore\Module\Menu\Form\Type\GroupFormType;

class AdminController extends Controller
{
    public function indexAction(Request $request, $slug = null)
    {
        // @todo сделать роутинг
        if (is_numeric($slug)) {
            return $this->groupAction($request, $slug);
        }

        $em = $this->get('doctrine.orm.default_entity_manager');

        $form = $this->createForm(new GroupFormType());

        if ($request->isMethod('POST')) {
            if ($request->request->has('create')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $group = $form->getData();
                    $group->setCreateByUserId($this->getUser()->getId());
                    $em->persist($group);
                    $em->flush();

                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'status' => 'OK',
                            'message' => 'Group created successful.',
                        ]);
                    } else {
                        $this->get('session')->getFlashBag()->add('notice', 'Группа меню создана.'); // @todo translate
                        return $this->redirect($this->generateUrl('cmf_admin_module_manage', ['module' => 'Menu']));
                    }
                }
            } else if ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->render('MenuModule:Admin:index.html.twig', [
            'groups' => $em->getRepository('MenuModule:Group')->findAll(),
            'form_create' => $form->createView(),
        ]);
    }

    /**
     * Редактирование группы меню
     */
    public function groupAction(Request $request, $id)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $group = $em->find('MenuModule:Group', $id);

        if (empty($group)) {
            return $this->redirect($this->generateUrl('cmf_admin_module_manage', ['module' => 'Menu']));
        }

        $form = $this->createForm(new GroupFormType(), $group);

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'status' => 'OK',
                            'message' => 'Group updated successful.',
                        ]);
                    } else {
                        $this->get('session')->getFlashBag()->add('notice', 'Группа меню обновлена.'); // @todo translate
                        return $this->redirect($this->generateUrl('cmf_admin_module_manage', ['module' => 'Menu']));
                    }
                }
            } else if ($request->request->has('delete')) {
                // @todo безопасное удаление, в частности отключение оз нод и удаление всех связаных пунктов меню.
                $em->remove($form->getData());
                $em->flush();

                $this->get('session')->getFlashBag()->add('notice', 'Группа меню удалеа.');
                return $this->redirect($this->generateUrl('cmf_admin_module_manage', ['module' => 'Menu']));
            }
        }

        return $this->render('MenuModule:Admin:group.html.twig', [
            'group' => $group,
            'form_edit' => $form->createView(),
        ]);
    }
}
