<?php

namespace App\Twig;

use App\Utilities\Crud\CrudAnnotation;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CrudExtension extends AbstractExtension
{

    private $container;

    public function __construct (ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getFilters (): array
    {
        return [
//            new TwigFilter('read_property_name', [$this, 'readPropertyName']),
        ];
    }

    public function getFunctions (): array
    {
        return [
            new TwigFunction('read_property_name', [$this, 'readPropertyName']),
            new TwigFunction('read_property_resource', [$this, 'readPropertyResource']),
        ];
    }

    /**
     * @param string $key
     * @param CrudAnnotation|null $annotation
     *
     * @return string
     */
    public function readPropertyName (string $key, ?CrudAnnotation $annotation)
    {
        $key = ucfirst($key);

        return !$annotation ? $key : ($annotation->name ?? $key);
    }

    /**
     * @param $entity
     * @param string $property
     *
     * @return string|null
     */
    public function readPropertyResource ($entity, string $property): ?string
    {
        $data = null;

        $getter = 'get' . ucfirst($property);
        if (is_callable([$entity, $getter])) {
            $data = call_user_func([$entity, $getter]);
        }

        if ($data instanceof \DateTimeInterface) {
            $data = $data->format($this->container->getParameter('twig_format_date'));
        }

        return $data;
    }
}
