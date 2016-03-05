<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\Translation\Translator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("admin/ajax")
 */
class AjaxController extends Controller{
	/**
	 * @Route("/get-users",name="ajax_get_requests_list")
	 */
	public function getRequestsAction(){
		$request=$this->getRequest();
		$response=[
			'status'=>'fail'
		];
		if($request->isXmlHttpRequest()){
			$_translator=new Translator($request->getLocale());
			$users=$this->get('admin_ajax_data')->getRequests($request);
			if(sizeof($users)>0){
				$response['status']='ok';
				$response['users']=$users;
				$response['statistics']=$this->get('admin_ajax_data')->getStatByCity($request);
			}else{
				$response['status']='zero';
				$response['message']=$_translator->trans('requests.zero.message');
			}
		}
		return new JsonResponse($response);
	}
	
	/**
	 * @Route("/get-matches",name="ajax_get_match_results")
	 */
	public function getMatchesAction(){
		$request=$this->getRequest();
		$response=$this->get('admin_ajax_data')->getScoreOrderedPersons($request);
		return new JsonResponse($response);
	}
	
}