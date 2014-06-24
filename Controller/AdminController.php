<?php

namespace Stems\UserBundle\Controller;

// Dependencies
use Stems\CoreBundle\Controller\BaseAdminController,
	Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;

// Forms
use Stems\UserBundle\Form\AdminUserType;

// Entities
use Stems\UserBundle\Entity\User,
	Stems\SaleSirenBundle\Entity\Profile,
	Stems\SaleSirenBundle\Entity\Wishlist;

// Exceptions
use Doctrine\ORM\NoResultException;

class AdminController extends BaseAdminController
{
	protected $home = 'stems_admin_user_overview';

	/**
	 * User overview
	 */
	public function indexAction()
	{		
		// get all undeleted users
		$em = $this->getDoctrine()->getEntityManager();
		$users = $em->getRepository('StemsUserBundle:User')->findBy(array('deleted' => false));

		return $this->render('StemsUserBundle:Admin:index.html.twig', array(
			'users' 	=> $users,
		));
	}

	/**
	 * Create a user
	 */
	public function createAction(Request $request)
	{
		// load the user management service
		$userManager = $this->get('stems.user.management');

		// create a new user form
		$em = $this->getDoctrine()->getEntityManager();		
		$user = new User();
		$form = $this->createForm(new AdminUserType(), $user);

		// handle the form submission
		if ($request->getMethod() == 'POST') {

			// validate the submitted values
			$form->bindRequest($request);

			//if ($form->isValid()) {	

				// valid username 
				if ($userManager->validateNewEmail($user->getPassword(), $user->getEmailConfirmation())) {
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

						$request->getSession()->setFlash('success', 'The user account for '.$user->getEmail().' has been created.');
						return $this->redirect($this->generateUrl($this->home));
					}
				} 
				
				$request->getSession()->setFlash('error', $userManager->getMessage());

			//}
		}

		return $this->render('StemsUserBundle:Admin:create.html.twig', array(
			'form'			=> $form->createView(),
			'user' 			=> $user,
		));
	}

	/**
	 * Edit a user
	 */
	public function editAction(Request $request, $id)
	{
		// load the user management service
		$userManager = $this->get('stems.user.management');

		// get the user form
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('StemsUserBundle:User')->findOneBy(array('id' => $id, 'deleted' => false));
		$form = $this->createForm(new AdminUserType(), $user);

		// throw an exception if the user could not be found
		if (!$user) {
			$request->getSession()->setFlash('error', 'The requested user could not be found.');
			return $this->redirect($this->generateUrl($this->home));
		}

		// handle the form submission
		if ($request->getMethod() == 'POST') {

			// validate the submitted values
			$form->bindRequest($request);

			//if ($form->isValid()) {

				// valid username 

				// check passwords match
				if ($userManager->validateNewPassword($user->getPassword(), $user->getConfirmation())) {
					
					// create the user
					//$userManager->createUser($user);
					$em->persist($user);
					$em->flush();

					$request->getSession()->setFlash('success', 'The user account for '.$user->getEmail().' has been created.');
					return $this->redirect($this->generateUrl($this->home));

				} else {
					$request->getSession()->setFlash('error', $userManager->getMessage());
				// 	$request->getSession()->setFlash('debug', '');
				}
			//}
		}

		return $this->render('StemsUserBundle:Admin:edit.html.twig', array(
			'form'			=> $form->createView(),
			'user' 			=> $user,
		));
	}

	/**
	 * Delete a user
	 */
	public function deleteAction(Request $request, $id)
	{
		// get the entity
		$em = $this->getDoctrine()->getEntityManager();
		$user = $em->getRepository('StemsUserBundle:User')->findOneBy(array('id' => $id, 'deleted' => false));

		if ($user) {
			// delete the user if was found
			$name = $user->getFullname();
			$user->setDeleted(true);
			$em->persist($user);
			$em->flush();

			// return the success message
			$request->getSession()->setFlash('success', 'The user "'.$name.'" was successfully deleted!');
		} else {
			$request->getSession()->setFlash('error', 'The requested user could not be deleted as it does not exist in the database.');
		}

		return $this->redirect($this->generateUrl($this->home));
	}

	/**
	 * deactivate a user
	 */
	public function deactivateAction(Request $request, $id)
	{
		// get the entity
		$em = $this->getDoctrine()->getEntityManager();
		$user = $em->getRepository('StemsUserBundle:User')->findOneBy(array('id' => $id, 'deleted' => false));

		if ($user) {
			// set the user the published/unpublished 
			if ($user->getActive() == true) {	
				$user->setActive(false);
				$user->setUpdated(new \DateTime());
				$request->getSession()->setFlash('success', 'The user "'.$user->getFullname().'" was successfully deactivated!');
			}

			$em->persist($user);
			$em->flush();

		} else {
			$request->getSession()->setFlash('error', 'The request user could not be deactivated as it does not exist in the database.');
		}

		return $this->redirect($this->generateUrl($this->home));
	}

	/**
	 * activate a user
	 */
	public function activateAction(Request $request, $id)
	{
		// get the entity
		$em = $this->getDoctrine()->getEntityManager();
		$user = $em->getRepository('StemsUserBundle:User')->findOneBy(array('id' => $id, 'deleted' => false));

		if ($user) {
			// set the user the published/unpublished 
			if ($user->getActive() == false) {
				$user->setActive(true);
				$user->setUpdated(new \DateTime());
				$request->getSession()->setFlash('success', 'The user "'.$user->getFullname().'" was successfully activated!');
			}

			$em->persist($user);
			$em->flush();

		} else {
			$request->getSession()->setFlash('error', 'The request user could not be activated as it does not exist in the database.');
		}

		return $this->redirect($this->generateUrl($this->home));
	}
}
