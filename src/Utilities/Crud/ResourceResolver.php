<?php

namespace App\Utilities\Crud;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class ResourceResolver
{
    /**
     * @var string
     */
    private $resource;
    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var Reader
     */
    private $reader;
    /**
     * @var FormFactory
     */
    private $formFactoryrm;

    public function __construct (ObjectManager $manager, Reader $reader, FormFactoryInterface $formFactoryrm)
    {
        $this->manager = $manager;
        $this->reader = $reader;
        $this->formFactoryrm = $formFactoryrm;
    }

    /**
     * @param string $resource
     *
     * @return ResourceResolver
     */
    public function setResource (string $resource): ResourceResolver
    {
        $this->resource = ucfirst($resource);;
        return $this;
    }

    /**
     * @throws \ReflectionException
     * @return array
     */
    public function getIndexProperties (): array
    {
        return $this->getProperties('index');
    }

    /**
     * @param string $type
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getProperties (string $type): array
    {
        $rf = new \ReflectionClass($this->resolveEntityClassName());

        $properties = [];
        foreach ($rf->getProperties() as $property) {
            /** @var CrudAnnotation $annotation */
            $annotation = $this->reader->getPropertyAnnotation($property, CrudAnnotation::class);

            if (!$annotation || ($type === 'index' && $annotation->showInIndex !== false)) {
                $properties[$property->name] = $annotation;
            } elseif ($annotation && $type === 'create' && $annotation->showInCreate !== false) {
                $properties[$property->name] = $annotation;
            } elseif ($annotation && $type === 'edit' && $annotation->showInEdit !== false) {
                $properties[$property->name] = $annotation;
            }
        }

        return $properties;
    }

    /**
     *
     * @return ObjectRepository
     * @throws \Exception
     */
    public function resolveEntityClassName (): string
    {
        $this->verifyExistenceOfClasses();
        return 'App\Entity\\' . $this->resource;
    }

    /**
     * @throws CrudException
     */
    private function verifyExistenceOfClasses ()
    {
        $entityClass = 'App\Entity\\' . $this->resource;
        $repositoryClass = 'App\Repository\\' . $this->resource . 'Repository';

        if (!class_exists($entityClass)) {
            throw new CrudException($entityClass);
        }

        if (!class_exists($repositoryClass)) {
            throw new CrudException($repositoryClass);
        }
    }

    public function createEntity ()
    {
        $entityClassName = $this->resolveEntityClassName();
        return new $entityClassName;
    }

    public function createFormBuilder ($data): FormBuilderInterface
    {
        $builder = $this->formFactoryrm->createBuilder(FormType::class, $data)->setMethod($data->getId() ? 'PUT' : 'POST');
        $properties = array_keys($this->getProperties($data->getId() ? 'edit' : 'create'));
        foreach ($properties as $property) {
            $builder->add($property);
        }

        return $builder;
    }

    public function getEntity (int $resourceId)
    {
        return $this->resolveRepository()->find($resourceId);
    }

    /**
     *
     * @return ObjectRepository
     * @throws \Exception
     */
    public function resolveRepository (): ObjectRepository
    {
        $this->verifyExistenceOfClasses();
        return $this->manager->getRepository('App\Entity\\' . $this->resource);
    }
}