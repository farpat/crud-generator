<?php

namespace App\Utilities\Crud;

use Doctrine\ORM\PersistentCollection;
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
            new TwigFunction('read_property_entity', [$this, 'readPropertyEntity']),
            new TwigFunction('read_hide_entity_in_index', [$this, 'readHideEntityInIndex']),
        ];
    }

    public function readHideEntityInIndex (?CrudAnnotation $annotation)
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

    private function getData ($entity, string $property)
    {
        $getter = 'get' . ucfirst(implode(array_map('ucfirst', explode('_', $property))));
        return is_callable([$entity, $getter]) ? call_user_func([$entity, $getter]) : null;
    }

    private function translateData ($data): string
    {
        if (!is_object($data)) {
            return $data;
        }

        if ($data instanceof \DateTimeInterface) { //type date
            return $data->format($this->container->getParameter('twig_format_date'));
        } else {
            $class = get_class($data);

            if (strpos($class, 'App\Entity\\') !== false) { //type entité
                return $data->__toString();
            } elseif ($class === 'Doctrine\ORM\PersistentCollection') {
                $html = '';
                foreach ($data as $item) $html .= '<li>' . $item->__toString() . '</li>';
                return '<ul>' . $html . '</ul>';
            }
        }
    }

    /**
     * @param $entity
     * @param string $property
     *
     * @return string|null
     */
    public function readPropertyEntity ($entity, string $property): ?string
    {
        $data = $this->getData($entity, $property);
        return $this->translateData($data);
    }
}
