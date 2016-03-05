<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\Translation\Translator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Enum\Countries;

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
		$_translator=new Translator($request->getLocale());
		$response=[
			'status'=>'fail'
		];
		$em=$this->getDoctrine()->getEntityManager();
		$users=$em->getRepository('AppBundle:User')->getMatchedUsers($request->request->get('user_id'));
		$currentCategories=[];
		foreach($users['user']->getCategories() as $ccat){
			$currentCategories[]=$ccat->getId();
		}
		$response['users']=[];
		if(isset($users['result'])&&sizeof($users['result'])>0){
			foreach($users['result'] as $u){
				$matches=[];
				$matches[]=(isset($u['age'])&&($u['age']==$users['user']->getAge()))?'<span class="matches">'.$u['age'].'</span>':$u['age'];
				$matches[]=(isset($u['from'])&&($u['from']==$users['user']->getFrom()))?'<span class="matches">'.Countries::getName($u['from']).'</span>':Countries::getName($u['from']);
				$matches[]=(isset($u['hasChildren'])&&($u['hasChildren']==$users['user']->hasChildren()))?'<span class="matches">'.($users['user']->hasChildren()?'kids':'no kids').'</span>':($users['user']->hasChildren()?'kids':'no kids');
				$interests=[];
				$_u=$em->getRepository('AppBundle:User')->find($u['id']);
				foreach($_u->getCategories() as $cat){
					$interests[]=in_array($cat->getId(),$currentCategories)?'<span class="matches">'.$cat->getName().'</span>':$cat->getName();
				}
				$row='<div class="row candidate">';
				$row.='<div class="col-md-1">';
				$row.='<p class="score">'.$u['summary_points'].'</p>';
				$row.='</div>';
				$row.='<div class="col-md-9 presentation">';
				$row.='<div class="pull-right">';
				$row.='<a href="" class="btn btn-orange">'.$_translator->trans('Edit Profile').'</a>';
				$row.='</div>';
				$row.='<h4>'.trim(sprintf('%s %s',$u['firstName'],$u['lastName'])).' ('.implode(', ',$matches).')</h4>';
				$row.='<p class="interests">'.$_translator->trans('Matching interests').' : '.implode(', ',$interests).'</p>';
				$row.='<p>'.$u['about'].'</p>';
				$row.='</div>';
				$row.='<div class="col-md-2 choice">';
				$row.='<p><strong>'.$_translator->trans('Choose candidate').'</strong></p>';
				$row.='<input type="radio" name="gender" value="1">';
				$row.='</div>';
				$row.='</div>';
				$response['users'][]=$row;
			}
			$response['status']='ok';
		}
		return new JsonResponse($response);
	}
	
}