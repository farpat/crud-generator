<?php

namespace App\Controller;

use App\Utilities\Crud\CrudException;
use App\Utilities\Crud\ResourceResolver;
use Doctrine\Common\Persistence\ObjectManager;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{RedirectResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GeneralController
 * @package App\Controller
 * @Route("/general")
 */
class GeneralController extends AbstractController
{

    /**
     * Liste l'ensemble des ressources disponibles
     * @Route("/_list", name="general.list", methods={"GET"})
     * @param ResourceResolver $resolver
     *
     * @return Response
     * @throws \Exception
     */
    public function list (ResourceResolver $resolver): Response
    {
        $resources = $resolver->getListOfResources();

        return $this->render('general/list.html.twig', compact('resources'));
    }

    /**
     * Liste l'ensemble des lignes de la ressource courante
     * @Route("/{resource}", name="general.index")
     * @param ResourceResolver $resolver
     * @param Request $request
     * @param string $resource
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function index (ResourceResolver $resolver, Request $request, string $resource): Response
    {
        if ($request->query->getInt('page') === 1) {
            return $this->redirectToRoute('general.index', compact('resource'));
        }

        $resolver->setResource($resource);
        $repository = $resolver->resolveRepository();
        $properties = $resolver->getIndexProperties();

        $adapter = new ArrayAdapter($repository->findAll());
        $entities = (new Pagerfanta($adapter))
            ->setMaxPerPage(3)
            ->setCurrentPage($request->query->getInt('page', 1));

        return $this->render('general/index.html.twig', compact('entities', 'properties', 'resource'));
    }

    /**
     * Création d'une ressource
     * @Route("/{resource}/create", name="general.create", methods={"GET", "POST"})
     * @param ResourceResolver $resolver
     * @param Request $request
     * @param ObjectManager $manager
     * @param string $resource
     *
     * @return Response
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function create (ResourceResolver $resolver, Request $request, ObjectManager $manager, string $resource): Response
    {
        $resolver->setResource($resource);

        $entity = $resolver->createEntity();
        $form = $resolver->createFormBuilder($entity)->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($entity);
            $manager->flush();


            $this->addFlash('success', 'Resource << ' . $entity->getName() . ' >> <strong>created</strong> with success!');
            $this->addFlash('id', $entity->getId());

            return $this->redirectToRoute('general.index', compact('resource'));
        }

        $form = $form->createView();
        return $this->render('general/create.html.twig', compact('form', 'resource', 'entity'));
    }

    /**
     * Edition d'une ressource
     * @Route("/{resource}/{resourceId}/edit", name="general.edit", methods={"GET", "PUT"})
     * @param ResourceResolver $resolver
     * @param Request $request
     * @param ObjectManager $manager
     * @param string $resource
     *
     * @param int $resourceId
     *
     * @return Response
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function edit (ResourceResolver $resolver, Request $request, ObjectManager $manager, string $resource, int $resourceId): Response
    {
        $resolver->setResource($resource);

        $entity = $resolver->getEntity($resourceId);
        $form = $resolver->createFormBuilder($entity)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($entity);
            $manager->flush();


            $this->addFlash('success', 'Resource << ' . $entity->getName() . ' >> <strong>updated</strong> with success!');
            $this->addFlash('id', $entity->getId());

            return $this->redirectToRoute('general.index', compact('resource'));
        }

        $form = $form->createView();
        return $this->render('general/edit.html.twig', compact('form', 'resource', 'entity'));
    }

    /**
     * Suppression d'une ressource
     * @Route("/{resource}/{resourceId}/destroy", name="general.destroy", methods={"DELETE"})
     * @param ResourceResolver $resolver
     * @param Request $request
     * @param ObjectManager $manager
     * @param string $resource
     * @param int $resourceId
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function destroy (ResourceResolver $resolver, Request $request, ObjectManager $manager, string $resource, int $resourceId): RedirectResponse
    {
        if ($this->isCsrfTokenValid('general.destroy.' . $resourceId, $request->request->get('_token'))) {
            $entity = $resolver->setResource($resource)->getEntity($resourceId);

            if ($entity) {
                $manager->remove($entity);
                $manager->flush();
                $this->addFlash('success', 'Resource << ' . $entity->getName() . ' >> <strong>deleted</strong> with success!');
            }
        }

        return $this->redirectToRoute('general.index', compact('resource'));
    }
}
