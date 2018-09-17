<?php

namespace App\Entity;

use App\Utilities\Crud\CrudAnnotation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @UniqueEntity("slug")
 */
class Post
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @CrudAnnotation
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="posts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @CrudAnnotation(showInIndex=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255)
     * @CrudAnnotation(name="Libellé de l'article")
     */
    private $name;

    /**
     * @var string
     * @Assert\Regex("/^[a-z0-9\-]+$/")
     * @Assert\NotBlank()
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @CrudAnnotation(name="Contenu de l'article")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @CrudAnnotation(name="Date de création")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @CrudAnnotation(name="Date de modification")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     * @CrudAnnotation(showHideInIndex=true)
     */
    private $comments;

    /**
     * Constructor
     */
    public function __construct ()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Post
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
     * @return Post
     */
    public function setSlug ($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent ()
    {
        return $this->content;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Post
     */
    public function setContent ($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt ()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Post
     */
    public function setCreatedAt ($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt ()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Post
     */
    public function setUpdatedAt ($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get category
     *
     * @return \App\Entity\Category
     */
    public function getCategory ()
    {
        return $this->category;
    }

    /**
     * Set category
     *
     * @param \App\Entity\Category $category
     *
     * @return Post
     */
    public function setCategory (\App\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User
     */
    public function getUser ()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     *
     * @return Post
     */
    public function setUser (\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Add comment
     *
     * @param \App\Entity\Comment $comment
     *
     * @return Post
     */
    public function addComment (\App\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \App\Entity\Comment $comment
     */
    public function removeComment (\App\Entity\Comment $comment)
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);

            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments ()
    {
        return $this->comments;
    }

    public function __toString ()
    {
        return $this->getName();
    }

}
