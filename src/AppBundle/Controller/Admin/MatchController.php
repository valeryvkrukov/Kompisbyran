<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Form\MatchProfileType;
use AppBundle\Form\MatchFilterType;

/**
 * @Route("admin/match")
 */
class MatchController extends Controller
{
    /**
     * @Route("/{id}/{reqId}",name="admin_find_match")
     */
    public function indexAction($id = 0, $reqId = 0)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($id);
        $matchRequest = $em->getRepository('AppBundle:ConnectionRequest')->find($reqId);
        $profileForm = $this->get('form.factory')->create(new MatchProfileType($matchRequest), $user);
        $filterForm = $this->get('form.factory')->create(new MatchFilterType($em));
        $parameters = array(
            'user' => $user,
            'fullname' => trim(sprintf('%s %s', $user->getFirstName(), $user->getLastName())),
            'profileForm' => $profileForm->createView(),
            'filterForm' => $filterForm->createView(),
        );

        return $this->render('admin/match/index.html.twig', $parameters);
    }
}
