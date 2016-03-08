<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use AppBundle\Form\AdminUserType;
use AppBundle\Enum\Countries;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("admin/users")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="admin_users")
     */
    public function indexAction()
    {
        $users = $this->getUserRepository()->findAllWithCategoryJoinAssoc();
        $categories = $this->getCategoryRepository()->findAll();

        $parameters = [
            'users' => $users,
            'categories' => $categories,
        ];

        return $this->render('admin/user/index.html.twig', $parameters);
    }

    /**
     * @Route("/{id}", name="admin_user", defaults={"id": null})
     * @Route("/ajax/{id}", name="ajax_admin_user", defaults={"id": null})
     */
    public function viewAction(Request $request, User $user)
    {
        $form = $this->createForm(
            new AdminUserType(),
            $user,
            [
                'manager' => $this->getDoctrine()->getManager(),
                'locale' => $request->getLocale(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            if ($request->get('_route') == 'ajax_admin_user') {
                $interests = [];
                foreach ($user->getCategories() as $cat) {
                    $interests[] = $cat->getName();
                }
                $data = [
                    'fullName' => trim(sprintf('%s %s', $user->getFirstName(), $user->getLastName())),
                    'email' => $user->getEmail(),
                    'age' => $user->getAge(),
                    'district' => $user->getDistrict(),
                    'wantToLearn' => ($user->getWantToLearn() == 0 ? 'Established' : 'New'),
                    'musicFriend' => ($user->isMusicFriend() ? 'Musicbuddy' : 'Fikabuddy'),
                    'from' => Countries::getName($user->getFrom()),
                    'childs' => ($user->hasChildren() ? 'Yes' : 'No'),
                    'categories' => $interests,
                    'about' => $user->getAbout(),
                    'comment' => '',
                    'internalComment' => $user->getInternalComment(),
                ];

                return new JsonResponse($data);
            } else {
                return $this->redirect($this->generateUrl('admin_start'));
            }
        }

        $parameters = [
            'userId' => $user->getId(),
            'isAjax' => ($request->get('_route') == 'ajax_admin_user'),
            'form' => $form->createView(),
        ];
        if ($request->get('_route') == 'ajax_admin_user') {
            return $this->render('admin/user/form.html.twig', $parameters);
        } else {
            return $this->render('admin/user/view.html.twig', $parameters);
        }
    }

    protected function getUserRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
    }

    protected function getCategoryRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle:Category');
    }
}
