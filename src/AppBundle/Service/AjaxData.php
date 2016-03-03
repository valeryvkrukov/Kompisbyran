<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;

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
	
}