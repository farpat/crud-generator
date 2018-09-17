<?php

namespace App\Entity;

use App\Utilities\Crud\CrudAnnotation;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Category
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @CrudAnnotation(name="Identifiant", showInCreate=false, showInEdit=false)
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="category", cascade={"persist", "remove"})
     * @CrudAnnotation(showInIndex=false)
     */
    private $posts;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @CrudAnnotation(name="Libellé")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var int
     *
     * @ORM\Column(name="post_count", type="integer")
     * @CrudAnnotation(name="Nombre d'article(s)", showInCreate=false, showInEdit=false)
     */
    private $postCount = 0;

    /**
     * Constructor
     */
    public function __construct ()
    {
        $this->posts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName ($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug ()
    {
        return $this->slug;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Category
     */
    public function setSlug ($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Add post
     *
     * @param \App\Entity\Post $post
     *
     * @return Category
     */
    public function addPost (\App\Entity\Post $post)
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $this->postCount++;

            $post->setCategory($this);
        }

        return $this;
    }

    /**
     * Get postCount
     *
     * @return int
     */
    public function getPostCount ()
    {
        return $this->postCount;
    }

    /**
     * Set postCount
     *
     * @param integer $postCount
     *
     * @return Category
     */
    public function setPostCount ($postCount)
    {
        $this->postCount = $postCount;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function updatePostCount ()
    {
        $this->postCount = $this->posts->count();
    }

    /**
     * Remove post
     *
     * @param \App\Entity\Post $post
     */
    public function removePost (\App\Entity\Post $post)
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            $this->postCount--;

            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }
        }
    }

    /**
     * Get posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public
    function getPosts ()
    {
        return $this->posts;
    }
}
