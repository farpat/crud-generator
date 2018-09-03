<?php

namespace App\Controller;

use App\Utilities\Crud\ResourceResolver;
use Doctrine\Common\Persistence\ObjectManager;
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
     * @Route("/{resourceName}", name="general.index")
     * @param ResourceResolver $resolver
     * @param string $resourceName
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index (ResourceResolver $resolver, string $resourceName): Response
    {
        $resolver->setResource($resourceName);
        $repository = $resolver->resolveRepository();
        $properties = $resolver->getIndexProperties();

        $resources = $repository->findAll();

        return $this->render('general/index.html.twig', compact('resources', 'properties', 'resourceName'));
    }

    /**
     * @Route("/{resourceName}/create", name="general.create", methods={"GET", "POST"})
     * @param ResourceResolver $resolver
     * @param Request $request
     * @param ObjectManager $manager
     * @param string $resourceName
     *
     * @return Response
     */
    public function create (ResourceResolver $resolver, Request $request, ObjectManager $manager, string $resourceName): Response
    {
        $resolver->setResource($resourceName);

        $resource = $resolver->createEntity();
        $form = $resolver->createFormBuilder($resource)->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($resource);
            $manager->flush();


            $this->addFlash('success', 'Resource << ' . $resource->getName() . ' >> <strong>created</strong> with success!');
            $this->addFlash('id', $resource->getId());

            return $this->redirectToRoute('general.index', compact('resourceName'));
        }

        $form = $form->createView();
        return $this->render('general/create.html.twig', compact('form', 'resourceName', 'resource'));
    }

    /**
     * @Route("/{resourceName}/{resourceId}/edit", name="general.edit", methods={"GET", "PUT"})
     * @param ResourceResolver $resolver
     * @param Request $request
     * @param ObjectManager $manager
     * @param string $resourceName
     *
     * @param int $resourceId
     *
     * @return Response
     */
    public function edit (ResourceResolver $resolver, Request $request, ObjectManager $manager, string $resourceName, int $resourceId): Response
    {
        $resolver->setResource($resourceName);

        $resource = $resolver->getEntity($resourceId);
        $form = $resolver->createFormBuilder($resource)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($resource);
            $manager->flush();


            $this->addFlash('success', 'Resource << ' . $resource->getName() . ' >> <strong>updated</strong> with success!');
            $this->addFlash('id', $resource->getId());

            return $this->redirectToRoute('general.index', compact('resourceName'));
        }

        $form = $form->createView();
        return $this->render('general/edit.html.twig', compact('form', 'resourceName', 'resource'));
    }

    /**
     * @Route("/{resourceName}/{resourceId}/destroy", name="general.destroy", methods={"DELETE"})
     * @param ResourceResolver $resolver
     * @param Request $request
     * @param string $resourceName
     * @param int $resourceId
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function destroy (ResourceResolver $resolver, Request $request, ObjectManager $manager, string $resourceName, int $resourceId): RedirectResponse
    {
        if ($this->isCsrfTokenValid('general.destroy.' . $resourceId, $request->request->get('_token'))) {
            $resource = $resolver->setResource($resourceName)->getEntity($resourceId);

            if ($resource) {
                $manager->remove($resource);
                $manager->flush();
                $this->addFlash('success', 'Resource << ' . $resource->getName() . ' >> <strong>deleted</strong> with success!');
            }
        }

        return $this->redirectToRoute('general.index', compact('resourceName'));
    }
}
