<?php

namespace App\Entity;

use App\Utilities\Crud\CrudAnnotation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="integer")
     * @CrudAnnotation(name="Posts count")
     */
    private $posts_count = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="category", cascade={"persist"})
     * @CrudAnnotation(showHideInIndex=true)
     */
    private $posts;

    public function __construct ()
    {
        $this->posts = new ArrayCollection();
    }

    public function getId (): ?int
    {
        return $this->id;
    }

    public function getName (): ?string
    {
        return $this->name;
    }

    public function setName (string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug (): ?string
    {
        return $this->slug;
    }

    public function setSlug (string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPostsCount (): ?int
    {
        return $this->posts_count;
    }

    public function setPostsCount (int $posts_count): self
    {
        $this->posts_count = $posts_count;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts (): Collection
    {
        return $this->posts;
    }

    public function addPost (Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setCategory($this);
            $this->posts_count++;
        }

        return $this;
    }

    public function removePost (Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }

            $this->posts_count--;
        }

        return $this;
    }

    public function __toString ()
    {
        return $this->getName();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize ()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'slug' => $this->getSlug(),
            'posts_count' => $this->getPostsCount(),
            'posts' => $this->getPosts()->map(function ($post) {
                $post = $post->jsonSerialize();
                unset($post['category']);
                return $post;
            })->toArray()
        ];
    }
}
