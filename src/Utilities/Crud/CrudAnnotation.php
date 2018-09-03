<?php

namespace App\Utilities\Crud;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class CrudAnnotation
{

    /**
     * @var string|null
     */
    public $name;

    /**
     * Afficher dans la table d'index
     * @var bool
     */
    public $showInIndex;

    /**
     * Afficher dans le formulaire de création
     * @var bool
     */
    public $showInCreate;

    /**
     * Afficher dans le formulaire de création
     * @var bool
     */
    public $showInEdit;

    public function __construct (array $options)
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->showInIndex = $this->showInIndex ?? true;
        $this->showInCreate = $this->showInCreate ?? true;
        $this->showInEdit = $this->showInEdit ?? true;
        $this->name = $this->name ?? null;
    }
}