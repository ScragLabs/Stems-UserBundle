<?php

namespace Stems\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UpdateAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder->add('fullname', null, array(
			'label'     		=> 'Name',
			'error_bubbling' 	=> true,
		));
	}

	public function getName()
	{
		return 'update_account_type';
	}
}
