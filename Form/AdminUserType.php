<?php

namespace Stems\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('email', null, array(
			'label'  			=> 'Email Address',
			'error_bubbling' 	=> true,
			'required'			=> false,
		));

		$builder->add('password', 'password', array(
			'label'     		=> 'Password',
			'error_bubbling' 	=> true,
		));

		$builder->add('confirmation', 'password', array(
			'label'     		=> 'Confirm Password',
			'error_bubbling' 	=> true,
		));

		$builder->add('forname', null, array(
			'label'     		=> 'Forname',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

		$builder->add('surname', null, array(
			'label'     		=> 'Surname',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));
	}

	public function getName()
	{
		return 'admin_user_type';
	}
}
