<?php

namespace Stems\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
  //   	$builder->add('oldpass', null, array(
		// 	'label'     		=> 'Name',
		// 	'error_bubbling' 	=> true,
		// ));

		$builder->add('password', 'password', array(
			'label'     		=> 'New Password',
			'error_bubbling' 	=> true,
		));

		$builder->add('passwordConfirmation', 'password', array(
			'label'     		=> 'Confirm New Password',
			'error_bubbling' 	=> true,
		));		
	}

	public function getName()
	{
		return 'change_password_type';
	}
}
