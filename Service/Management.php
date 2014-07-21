<?php

namespace Stems\UserBundle\Service;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;

class Management
{
	// Config options for user management
	protected $options;

	// Message string for the most recent error.
	protected $message = '';

	// The encoder factory
	protected $factory;

	// The entity manager
	protected $em;

	// The mailer service
	protected $mailer;

	// The mailer service
	protected $twig;

	public function __construct($factory, EntityManager $em, $mailer, TwigEngine $twig, $options)
	{
		$this->options = $options;
		$this->factory = $factory;
		$this->em = $em;
		$this->mailer = $mailer;
		$this->twig = $twig;
	}

	/**
	 * Validate a new password
	 */
	public function validateNewPassword($password, $confirmation)
	{
		// check the passwords match
		if ($password != $confirmation) {
			$this->message = 'Both passwords do not match!';
			return false;
		}

		// check the password is of the minimum length
		if ($this->options['password_validation']['min_length'] && strlen($password) < $this->options['password_validation']['min_length']) {
			$this->message = 'The password has to be at least '.$this->minLength.' characters long.';
			return false;
		}

		// check the password contains only alphanumeric characters
		// if ($this->options['password_validation']['is_alphanumeric'] && !ctype_alnum($password) && $this->options['password_validation']['is_alphanumeric']) {
		// 	$this->message = 'The password can only contain letters and numbers.';
		// 	return false;
		// }

		return true;
	}

	/**
	 * Validate a new email address (confirmation is optional)
	 */
	public function validateNewEmail($email, $confirmation=null)
	{
		// if a confirmation is passed, ensure they both match
		if ($confirmation && $email != $confirmation) {
			$this->message = 'Both e-mail addresses do not match!';
			return false;
		}

		// check the email is in the format of an email address
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->message = 'Invalid e-mail address.';
			return false;
		}

		// check the password contains only alphanumeric characters
		if ($this->em->getRepository('StemsUserBundle:User')->findOneByEmail($email)) {
			$this->message = 'The e-mail address is already registered.';
			return false;
		}

		return true;
	}

	/**
	 * Create a complete user account from the basic entity provided
	 */
	public function createUser($user)
	{
		$user->setUsername($user->getEmail());
		$user->setToken(md5(uniqid(null, true)));

		// add uppercase to names if the user forgot
		$user->setFullname(ucwords($user->getFullname()));
		
		// encode the password
		$this->encodePassword($user);

		// send the welcome e-mail
		if ($this->options['account_creation']['welcome_email']) {
			$message = \Swift_Message::newInstance()
				->setSubject('Welcome to Thread & Mirror')
				->setFrom(array('notify@threadandmirror.com' => 'Thread & Mirror'))
				->setTo(array($user->getEmail() => $user->getFullname()))
				->setContentType('text/html')
				->setBody(
					$this->twig->render(
					    'StemsUserBundle:Email:welcome.html.twig',
					    array('user' => $user)
					)
				)
			;
			$this->mailer->send($message);
		}
		
		return $user;
	}

	public function encodePassword($user)
	{
		// encode the password
		$encoder = $this->factory->getEncoder($user);
		$password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
		$user->setPassword($password);

		return $user;
	}
	
	/**
	 * Get the most recent message
	 */
	public function getMessage() 
	{
		return $this->message;
	}
}
