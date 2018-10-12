<?php

namespace App\Utilities\Crud;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\{ObjectManager, ObjectRepository};
use Doctrine\ORM\Mapping\{Annotation, ManyToMany, ManyToOne, OneToMany, OneToOne};
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\{Extension\Core\Type\FormType, FormBuilderInterface, FormFactoryInterface};

/**
 * Cette classe prend " en entrée " un nom de ressource (exemples : post, category, comment, etc.)
 * Elle permet de rendre en retour une entité, une repository, un formulaire.
 * @package App\Utilities\Crud
 */
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
     * @var FormFactoryInterface
     */
    private $formFactory;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct (ObjectManager $manager, Reader $reader, FormFactoryInterface $formFactory, ContainerInterface $container)
    {
        $this->manager = $manager;
        $this->reader = $reader;
        $this->formFactory = $formFactory;
        $this->container = $container;
    }

    /**
     * @param string $resource
     *
     * @return ResourceResolver
     */
    public function setResource (string $resource): self
    {
        $this->resource = implode(array_map('ucfirst', explode('-', $resource)));
        return $this;
    }

    /**
     * Sert à lire les propriétés à afficher dans la page d'index
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
     * @throws \Exception
     */
    private function getProperties (string $type): array
    {
        $rf = new \ReflectionClass($this->resolveEntityClassName());

        $properties = [];
        foreach ($rf->getProperties() as $property) {
            /** @var CrudAnnotation $annotation */
            $annotation = $this->reader->getPropertyAnnotation($property, CrudAnnotation::class);

            if ($type === 'index' && (!$annotation || $annotation->showInIndex !== false)) { //si pas d'annotation par défaut on affiche
                $properties[$property->name] = $annotation;
            } elseif ($type === 'create' && $annotation && $annotation->showInCreate !== false) { //si pas d'annotation par défaut on n'affiche pas
                $properties[$property->name] = $annotation;
            } elseif ($type === 'edit' && $annotation && $annotation->showInEdit !== false) { //si pas d'annotation par défaut on n'affiche pas
                $properties[$property->name] = $annotation;
            }
        }

        return $properties;
    }

    /**
     * Une fois le $this->setResource() effectué, on peut résoudre la classe de l'entité
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

    /**
     * Sert à créer une entité du nom de la ressource $this->resource
     * @return mixed
     * @throws \Exception
     */
    public function createEntity ()
    {
        $entityClassName = $this->resolveEntityClassName();
        return new $entityClassName;
    }

    /**
     * Renvoie un " FormBuilder " de création ou d'édition selon $data
     *
     * @param mixed $data Entité à hydrater par le formulaire
     *
     * @return FormBuilderInterface
     * @throws \ReflectionException
     */
    public function createFormBuilder ($data): FormBuilderInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class, $data)->setMethod($data->getId() ? 'PUT' : 'POST');
        $properties = array_keys($this->getProperties($data->getId() ? 'edit' : 'create'));
        foreach ($properties as $property) {
            $builder->add($property);
        }

        return $builder;
    }

    /**
     * Récupère une entité (à partir de $this->resource et $resourceId passé en paramètre)
     *
     * @param int $resourceId Id de la ressource
     *
     * @return null|object
     * @throws \Exception
     */
    public function getEntity (int $resourceId)
    {
        return $this->resolveRepository()->find($resourceId);
    }

    /**
     * Résouds une entité à partir de $this->resource
     * @return ObjectRepository
     * @throws \Exception
     */
    public function resolveRepository (): ObjectRepository
    {
        $this->verifyExistenceOfClasses();
        return $this->manager->getRepository($this->resolveEntityClassName());
    }

    /**
     * Renvoie le QueryBuilder de la requête permettant d'avoir toutes les lignes de la ressource courante
     * @return QueryBuilder
     * @throws \Exception
     */
    public function getQueryBuilderOfFindAll (): QueryBuilder
    {
        $propertiesWithRelation = $this->getPropertiesWithRelation();
        $repository = $this->resolveRepository();

        if (empty($propertiesWithRelation)) {
            $repository->findAll();
        }

        $builder = $repository->createQueryBuilder('table');
        $selects = ['table'];
        foreach ($propertiesWithRelation as $property => $annotation) {
            $builder->join('table.' . $property, $property);
            $selects[] = $property;
        }

        return $builder->select($selects);
    }

    /**
     * Renvoie un tableau contenant toutes les lignes de la ressource courante
     * @return array
     * @throws \Exception
     */
    public function findAll (): array
    {
        return $this->getQueryBuilderOfFindAll()->getQuery()->getResult();
    }

    /**
     * Renvoie les propriétés contenant une annotation de relation
     * @return array
     * @throws \ReflectionException
     */
    private function getPropertiesWithRelation (): array
    {
        $rf = new \ReflectionClass($this->resolveEntityClassName());

        $properties = [];
        foreach ($rf->getProperties() as $property) {
            if ($annotation = $this->getRelationAnnotation($property)) {
                $properties[$property->name] = $annotation;
            }
        }

        return $properties;
    }

    /**
     * Renvoie l'annotation de relation (ou null)
     *
     * @param \ReflectionProperty $property
     *
     * @return Annotation|null
     */
    private function getRelationAnnotation (\ReflectionProperty $property): ?Annotation
    {
        $annotations = $this->reader->getPropertyAnnotations($property);

        foreach ($annotations as $annotation) {
            $annotationClass = get_class($annotation);
            if (in_array($annotationClass, [OneToOne::class, ManyToMany::class, OneToMany::class, ManyToOne::class])) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * Retourne un tableau contenant la liste des ressources.
     * Le tableau est formaté => ['resource' => 'nombre de lignes'] (exemple : ['category' => 10, 'comment' => 1000, etc.])
     * @return array
     * @throws \Exception
     */
    public function getListOfResources (): array
    {
        $entityDir = $this->container->getParameter('kernel.project_dir') . '/src/Entity';

        if (!file_exists($entityDir)) {
            throw new \Exception("The directory << $entityDir >> doesn't exists!");
        }

        $entities = array_diff(scandir($entityDir), ['.', '..', '.gitignore']);

        $return = [];

        foreach ($entities as $entity) {
            $resource = substr(lcfirst($entity), 0, -4);
            $return[$resource] = $this->setResource($resource)->resolveRepository()->count([]);
        }

        return $return;
    }
}