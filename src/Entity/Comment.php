<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
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
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="comments")
     */
    private $post;

    public function getId (): ?int
    {
        return $this->id;
    }

    public function getUsername (): ?string
    {
        return $this->username;
    }

    public function setUsername (string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail (): ?string
    {
        return $this->email;
    }

    public function setEmail (string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getContent (): ?string
    {
        return $this->content;
    }

    public function setContent (string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt (): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt (\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getPost (): ?Post
    {
        return $this->post;
    }

    public function setPost (?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function __toString ()
    {
        if (strlen($this->content) <= 50) {
            return $this->content;
        }

        return substr($this->content, 0, 50) . ' &hellip;';
    }
}
