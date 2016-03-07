<?php 

namespace AppBundle\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Enum\Countries;

class MatchProfileType extends AbstractType{
	
	public function buildForm(FormBuilderInterface $builder,array $options){
		$builder
			->addEventListener(FormEvents::PRE_SET_DATA,[$this,'onPreSetData'])
			->addEventListener(FormEvents::PRE_SUBMIT,[$this,'onPreSubmit']);
	}
	
	public function onPreSetData(FormEvent $event){
		$form=$event->getForm();
		$data=$event->getData();
		$fullName=trim(sprintf('%s %s',$data->getFirstName(),$data->getLastName()));
		$category=($data->getWantToLearn()==0?'Established':'New');
		$type=($data->isMusicFriend()?'Musicbuddy':'Fikabuddy');
		$from=Countries::getName($data->getFrom());
		$childs=($data->hasChildren()?'Yes':'No');
		$interests=[];
		foreach($data->getCategories() as $cat){
			$interests[]=$cat->getName();
		}
		$form
			->add('fullName','text',['label'=>'Name','data'=>$fullName,'mapped'=>false])
			->add('email','email',['label'=>'Email'])
			->add('age','number',['label'=>'Age'])
			->add('wantToLearn','text',['label'=>'Category','data'=>$category])
			->add('from','text',['label'=>'Country','data'=>$from])
			->add('district','text',['label'=>'Area'])
			->add('hasChildren','text',['label'=>'Children','data'=>$childs])
			->add('musicFriend','text',['label'=>'Type','data'=>$type])
			->add('categories','choice',[
				'label'=>'Interests',
				'expanded'=>true,
				'multiple'=>true,
				'mapped'=>false,
				'choices'=>$interests
			])
			->add('about','textarea',['label'=>'Description'])
			->add('comment','textarea',[
				'label'=>'Match request',
				'mapped'=>false
			])
			->add('internalComment','textarea',['label'=>'Internal comments']);
	}
	
	public function onPreSubmit(FormEvent $event){
		$form=$event->getForm();
		$data=$event->getData();
		var_dump($data);die();
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver){
		parent::setDefaultOptions($resolver);
	}
	
	public function getName(){
		return 'matchProfile';
	}
	
}