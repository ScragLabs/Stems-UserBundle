<?php

namespace Stems\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder->add('fullname', null, array(
			'label'     		=> 'Name',
			'error_bubbling' 	=> true,
		));

		$builder->add('email', null, array(
			'label'  			=> 'Email Address',
			'error_bubbling' 	=> true,
		));

		$builder->add('emailConfirmation', null, array(
			'label'  			=> 'Confirm Email',
			'error_bubbling' 	=> true,
		));

		$builder->add('password', 'password', array(
			'label'     		=> 'Password',
			'error_bubbling' 	=> true,
		));

		$builder->add('passwordConfirmation', 'password', array(
			'label'     		=> 'Confirm Password',
			'error_bubbling' 	=> true,
		));		
	}

	public function getName()
	{
		return 'registration_type';
	}
}
