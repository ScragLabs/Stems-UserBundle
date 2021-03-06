<?php

namespace Stems\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	Symfony\Component\Security\Core\SecurityContextInterface,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;

use Stems\UserBundle\Entity\User,
	ThreadAndMirror\ProductsBundle\Entity\Profile,
	ThreadAndMirror\ProductsBundle\Entity\Wishlist;

use Stems\UserBundle\Form\RegistrationType,
	Stems\UserBundle\Form\UpdateAccountType,
	Stems\UserBundle\Form\ChangePasswordType;

class FrontController extends Controller
{
	public function loginAction(Request $request)
	{
		// Redirect to previous page if already logged in
		if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
			// Redirect to the accounts page if we don't have a referrer or if the referrer is somehow this page
			if ($request->headers->get('referrer') && $request->headers->get('referrer') != '/login') {
				return $this->redirect($request->headers->get('referrer'));
			} else {
				return $this->redirect('/account');
			}
		}
						
		$session = $request->getSession();
		// Load the page for the template
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('login');

		// Get the login error if there is one
		if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(
				SecurityContextInterface::AUTHENTICATION_ERROR
			);
			$error and $session->getFlashBag()->set('error', 'Login Failed: '.$error->getMessage());
		} else {
			$error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
			$session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
			$error and $session->getFlashBag()->set('error', 'Login Failed: '.$error->getMessage());
		}

		return $this->render('StemsUserBundle:Front:login.html.twig', array(
			'last_username' => $session->get(SecurityContextInterface::LAST_USERNAME),
			'page'			=> $page,
		));
	}

	public function logoutAction(Request $request)
	{
		$request->getSession()->invalidate();
		$this->get('security.context')->setToken(null);
		
		return $this->redirect($request->headers->get('referer'), 301);
	}

	public function registerAction(Request $request)
	{
		// load the page for the template
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('register');

		// load the user management service
		$userManager = $this->get('stems.user.management');

		// create a new user form
		$user = new User();
		$form = $this->createForm(new RegistrationType(), $user);

		// handle the form submission
		if ($request->getMethod() == 'POST') {

			// validate the submitted values
			$form->bind($request);

			if ($form->isValid()) {

				// valid username (ie. email address)
				if ($userManager->validateNewEmail($user->getEmail(), $user->getEmailConfirmation())) {
					// validate the password
					if ($userManager->validateNewPassword($user->getPassword(), $user->getPasswordConfirmation())) {
						
						// create the user
						$userManager->createUser($user);
						$em->persist($user);
						$em->flush();

						// create the thread and mirror profile
						$profile = new Profile($user);
						$wishlist = new Wishlist($user); 
						$em->persist($profile);
						$em->persist($wishlist);
						$em->flush();

						$request->getSession()->getFlashBag()->set('success', 'Your account has been successfully created! You can now sign in.');

						// redirect to the homepage if we don't have a referrer (can happen)
						if ($request->headers->get('referrer')) {
							return $this->redirect($request->headers->get('referrer'));
						} else {
							return $this->redirect('/login');
						}
					}
				} 
				
				$request->getSession()->getFlashBag()->set('error', $userManager->getMessage());
			} else {
				$request->getSession()->getFlashBag()->set('error', 'You have not entered your name.');
			}
		}

		return $this->render('StemsUserBundle:Front:register.html.twig', array(
			'form'			=> $form->createView(),
			'page'			=> $page,
		));
	}

	/**
	 * User account dashboard
	 */
	public function accountAction(Request $request)
	{
		// load the page for the template
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('account');

		return $this->render('StemsUserBundle:Front:account.html.twig', array(
			'page'			=> $page,
		));
	}

	/**
	 * Update account details page (eg. name, address, etc.)
	 */
	public function updateDetailsAction(Request $request)
	{
		// load the page for the template
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('account/update-account');

		// load the user management service
		$userManager = $this->get('stems.user.management');

		// create the form
		$user = $this->getUser();
		$form = $this->createForm(new UpdateAccountType(), $user);

		// handle the form submission
		if ($request->getMethod() == 'POST') {

			// validate the submitted values
			$form->bind($request);

			if ($form->isValid()) {

				// update the user
				$em->persist($user);
				$em->flush();

				$request->getSession()->getFlashBag()->set('success', 'Your profile has been successfully updated!');
				return $this->redirect('/account');

			} else {
				$request->getSession()->getFlashBag()->set('error', 'The was a problem updating your details...');
			}
		}

		return $this->render('StemsUserBundle:Front:updateAccount.html.twig', array(
			'form'			=> $form->createView(),
			'page'			=> $page,
		));
	}

	/**
	 * Change password
	 */
	public function changePasswordAction(Request $request)
	{
		// load the page for the template
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('account/change-password');

		// load the user management service
		$userManager = $this->get('stems.user.management');

		// create the form
		$user = $this->getUser();
		$form = $this->createForm(new ChangePasswordType(), $user);

		// handle the form submission
		if ($request->getMethod() == 'POST') {

			// validate the submitted values
			$form->bind($request);

			// validate the password
			if ($userManager->validateNewPassword($user->getPassword(), $user->getPasswordConfirmation())) {
				
				// change the password
				$userManager->encodePassword($user);
				$em->persist($user);
				$em->flush();

				$request->getSession()->getFlashBag()->set('success', 'Your password has been successfully changed!');
				return $this->redirect('/account');
			}
			
			$request->getSession()->getFlashBag()->set('error', $userManager->getMessage());
		}

		return $this->render('StemsUserBundle:Front:changePassword.html.twig', array(
			'form'			=> $form->createView(),
			'page'			=> $page,
		));
	}
}
