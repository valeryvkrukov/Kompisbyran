<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\MatchProfileType;
use AppBundle\Form\MatchFilterType;

/**
 * @Route("admin/match")
 */
class MatchController extends Controller{
	/**
	 * @Route("/{id}",name="admin_find_match")
	 */
	public function indexAction($id=0){
		$request=$this->getRequest();
		$em=$this->getDoctrine()->getManager();
		$user=$em->getRepository('AppBundle:User')->find($id);
		$profileForm=$this->get('form.factory')->create(new MatchProfileType(),$user);
		$filterForm=$this->get('form.factory')->create(new MatchFilterType($em));
		$parameters=array(
			'fullname'=>trim(sprintf('%s %s',$user->getFirstName(),$user->getLastName())),
			'profileForm'=>$profileForm->createView(),
			'filterForm'=>$filterForm->createView()
		);
		
		return $this->render('admin/match/index.html.twig',$parameters);
	}
}