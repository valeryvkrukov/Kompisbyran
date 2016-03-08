<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\Translation\Translator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\User;

/**
 * @Route("admin/ajax")
 */
class AjaxController extends Controller
{
    /**
     * @Route("/get-users",name="ajax_get_requests_list")
     */
    public function getRequestsAction()
    {
        $request = $this->getRequest();
        $response = [
            'status' => 'fail',
        ];
        if ($request->isXmlHttpRequest()) {
            $_translator = new Translator($request->getLocale());
            $users = $this->get('admin_ajax_data')->getRequests($request);
            if (sizeof($users) > 0) {
                $response['status'] = 'ok';
                $response['users'] = $users;
                $response['statistics'] = $this->get('admin_ajax_data')->getStatByCity($request);
            } else {
                $response['status'] = 'zero';
                $response['message'] = $_translator->trans('requests.zero.message');
            }
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/get-matches",name="ajax_get_match_results")
     */
    public function getMatchesAction()
    {
        $request = $this->getRequest();
        $response = [
            'status' => 'fail',
        ];
        if ($request->isXmlHttpRequest()) {
            $response = $this->get('admin_ajax_data')->getScoreOrderedPersons($request, $this->get('router'));
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/update-user/{id}",name="ajax_update_user_info")
     */
    public function updateUserAction($id)
    {
        $request = $this->getRequest();
        $_request = $request->request->get('matchProfile');
        $response = [
            'status' => 'fail',
        ];
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AppBundle:User')->find($id);
            $user->setEmail($_request['email']);
            $user->setAge($_request['age']);
            $user->setWantToLearn($_request['wantToLearn']);
            $user->setFrom($_request['from']);
            $user->setDistrict($_request['district']);
            $user->setHasChildren($_request['hasChildren']);
            $user->setMusicFriend($_request['musicFriend']);
            $user->setAbout($_request['about']);
            //$user->setComment($_request['comment']);
            $user->setInternalComment($_request['internalComment']);
            $em->persist($user);
            $em->flush();
        }

        return new JsonResponse($response);
    }
}
