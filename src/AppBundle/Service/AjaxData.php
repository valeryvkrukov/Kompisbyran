<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Enum\Countries;

class AjaxData{
	protected $em=null;
	
	public function __construct(EntityManager $em){
		$this->em=$em;
	}
	
	public function getRequests(Request $request){
		$limit=$request->request->get('last');
		$offset=$request->request->get('first');
		$exclude=$request->request->get('exclude');
		$city=$request->request->get('city');
		$requests=$this->em->getRepository('AppBundle:ConnectionRequest')->findOldRequests($city,$limit,$offset,$exclude);
		$response=[];
		$translator=new Translator($request->getLocale());
		foreach($requests as $_req){
			$user=$_req->getUser();
			$category=$translator->trans(($_req->getWantToLearn()==0?'Established':'New'));
			$response[]=[
				'req_id'=>$_req->getId(),
				'user_id'=>$user->getId(),
				'username'=>trim(sprintf('%s %s',$user->getFirstName(),$user->getLastName())),
				'category'=>$category,
				'request_at'=>$_req->getCreatedAt()->format('Y-m-d')
			];
		}
		return $response;
	}
	
	public function getStatByCity(Request $request){
		$city=$request->request->get('city');
		$sql='SELECT 
				SUM(IF(c.want_to_learn = 0,0,1)) AS learners,
				SUM(IF(c.want_to_learn = 0,1,0)) AS established 
				FROM connection_request AS c 
			WHERE c.city_id = :city
		';
		$stmt=$this->em->getConnection()->prepare($sql);
		$stmt->execute([':city'=>$city]);
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}
	
	public function getScoreOrderedPersons(Request $request,$router){
		$_translator=new Translator($request->getLocale());
		$response=[
			'status'=>'fail'
		];
		$users=$this->em
			->getRepository('AppBundle:User')
			->getMatchedUsers(
				$request->request->get('user_id'),
				$request->request->get('filters')/*,
				$request->request->get('limit'),
				$request->request->get('offset')*/
			);
		$currentCategories=[];
		foreach($users['user']->getCategories() as $ccat){
			$currentCategories[]=$ccat->getId();
		}
		$response['users']=[];
		if(isset($users['result'])&&sizeof($users['result'])>0){
			foreach($users['result'] as $k=>$u){
				$municipality=$users['user']->getMunicipality();
				$matches=[];
				$matches[]=(isset($u['age'])&&($u['age']==$users['user']->getAge()))?'<span class="matches">'.$u['age'].'</span>':$u['age'];
				$matches[]=(isset($u['from'])&&($u['from']==$users['user']->getFrom()))?'<span class="matches">'.Countries::getName($u['from']).'</span>':Countries::getName($u['from']);
				$matches[]=(isset($u['municipality'])&&($u['municipality']==$municipality->getName()))?'<span class="matches">'.$municipality->getName().'</span>':$municipality->getName();
				$matches[]=(isset($u['hasChildren'])&&($u['hasChildren']==$users['user']->hasChildren()))?'<span class="matches">'.($users['user']->hasChildren()?'kids':'no kids').'</span>':($users['user']->hasChildren()?'kids':'no kids');
				$interests=[];
				$_u=$this->em->getRepository('AppBundle:User')->find($u['id']);
				foreach($_u->getCategories() as $cat){
					$interests[]=in_array($cat->getId(),$currentCategories)?'<span class="matches">'.$cat->getName().'</span>':$cat->getName();
				}
				$row='<div class="row candidate">';
				$row.='<div class="col-md-1">';
				$row.='<p class="score">'.$u['summary_points'].'</p>';
				$row.='</div>';
				$row.='<div class="col-md-9 presentation">';
				$row.='<div class="pull-right">';
				$row.='<a href="'.$router->generate('admin_user',['id'=>$u['id']]).'" class="btn btn-orange">'.$_translator->trans('Edit Profile').'</a>';
				$row.='</div>';
				$row.='<h4>'.trim(sprintf('%s %s',$u['firstName'],$u['lastName'])).' ('.implode(', ',$matches).')</h4>';
				$row.='<p class="interests">'.$_translator->trans('Matching interests').' : '.implode(', ',$interests).'</p>';
				$row.='<p>'.$u['about'].'</p>';
				$row.='</div>';
				$row.='<div class="col-md-2 choice">';
				$row.='<p><strong>'.$_translator->trans('Choose candidate').'</strong></p>';
				$row.='<input type="radio" name="userId" value="'.$u['id'].'">';
				$row.='</div>';
				$row.='</div>';
				$response['users'][]=$row;
			}
			$response['status']='ok';
		}
		return $response;
	}
	
}