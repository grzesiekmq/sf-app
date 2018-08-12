<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends Controller {

	public $location;
	public $em;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->location = new Location();
		$this->em = $entityManager;

	}
	/**
	 * @Route("/", name="homepage")
	 */
	public function createAction(Request $request) {
		$form_read = $this->createFormBuilder($this->location, array('allow_extra_fields' => true))
			->add('id', NumberType::class)
			->add('read', SubmitType::class)
			->getForm()
			->handleRequest($request);
		$this->location = $form_read->getData();

		$form = $this->createFormBuilder($this->location)
			->add('name', TextType::class)
			->add('latitude', NumberType::class)
			->add('longitude', NumberType::class)
			->add('add', SubmitType::class)
			->add('update', SubmitType::class)
			->getForm()
			->handleRequest($request);

		$this->location = $form->getData();

		$form_delete = $this->createFormBuilder($this->location)
			->add('id', NumberType::class)
			->add('delete', SubmitType::class)
			->getForm()
			->handleRequest($request);

		$this->location = $form_delete->getData();

		// if button add clicked
		if (!$this->location->id && $form->isSubmitted() && $form->isValid()) {
			$this->em->persist($this->location);
			$this->em->flush();

		}
		// if button update clicked

		if ($form->getClickedButton() && 'update' === $form->getClickedButton()->getName()) {
			return $this->redirectToRoute('update', array('id' => $this->location->id));
		}
		// if button read clicked

		if ($form_read->isSubmitted() && $form_read->isValid()) {
			return $this->redirectToRoute('read', array('id' => $this->location->id));
		}
		// if button delete clicked

		if ($form_delete->isSubmitted() && $form_delete->isValid()) {
			return $this->redirectToRoute('delete', array('id' => $this->location->id));
		}

		return $this->render('default/index.html.twig', array('form' => $form->createView(), 'form_delete' => $form_delete->createView(),
			'form_read' => $form_read->createView(),
		));
	}

	/**
	 * @Route("/location/read/{id}", name="read")
	 */
	public function readAction($id) {
		$repository = $this->getDoctrine()
			->getRepository(Location::class);
		$entity = $repository->find($id);
		$locationData = array('location' => $entity);
		return $this->json($locationData);
	}
	/**
	 * @Route("/location/update/{id}", name="update")
	 */
	public function updateAction(Request $request) {
		$this->em->flush();

		return $this->redirectToRoute('homepage');
	}

	/**
	 * @Route("/location/delete/{id}", name="delete")
	 */
	public function deleteAction($id) {

		$entity = $this->em
			->getRepository(Location::class)
			->findOneById($id);

		$this->em->remove($entity);
		$this->em->flush();

		return $this->redirectToRoute('homepage');
	}

}
