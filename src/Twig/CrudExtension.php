<?php

namespace App\Twig;

use App\Utilities\Crud\CrudAnnotation;
use Doctrine\ORM\PersistentCollection;
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
            new TwigFunction('read_hide_resource_in_index', [$this, 'readHideResourceInIndex']),
        ];
    }

    public function readHideResourceInIndex (?CrudAnnotation $annotation)
    {
        if (!$annotation || $annotation->showHideInIndex === false) {
            return '';
        }

        return '<a href="#" class="btn-hide" data-shown="0">Hide</a>';
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

        if (is_object($data)) {
            $class = get_class($data);

            if (strpos($class, 'App\Entity\\') !== false) {
                $data = $data->__toString();
            } elseif ($class === 'Doctrine\ORM\PersistentCollection') {
                $html = '<ul>';
                /** @var PersistentCollection $data */
                foreach ($data as $item) {
                    $html .= '<li>' . $item->__toString() . '</li>';
                }
                $html .= '</ul>';
                $data = $html;
            }
        }

        return $data;
    }
}
