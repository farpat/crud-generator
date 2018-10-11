<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment implements \JsonSerializable
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
            'post' => $this->getPost(),
            'content' => $this->getContent(),
        ];
    }
}
