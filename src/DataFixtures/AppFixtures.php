<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $faker;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct (UserPasswordEncoderInterface $encoder)
    {
        $this->faker = Faker::create('fr_FR');
        $this->encoder = $encoder;
    }

    public function load (ObjectManager $manager)
    {
        $users = [];
        for ($i = 0; $i < 20; $i++) {
            $users[] = $this->createUser();
        }

        for ($i = 0; $i < 10; $i++) {
            $category = $this->createCategory();

            for ($j = 0; $j < 10; $j++) {
                $category->addPost($post = $this->createPost());
                $post->setUser($users[random_int(0, 19)]);
                $post->setCategory($category);

                for ($k = 0; $k < 10; $k++) {
                    $post->addComment($comment = $this->createComment());
                    $comment->setPost($post);
                }
            }

            $manager->persist($category);
        }

        $manager->flush();
    }

    private function createUser (): User
    {
        $user = (new User)
            ->setUsername($this->faker->userName);
        $user->setPassword($this->encoder->encodePassword($user, 'secret'));

        return $user;
    }

    private function createCategory (): Category
    {
        return (new Category)
            ->setName($this->faker->words(3, true))
            ->setSlug($this->faker->slug)
            ->setPostCount(10);
    }

    private function createPost (): Post
    {
        return (new Post)->setSlug($this->faker->slug)
            ->setName($this->faker->sentence)
            ->setContent($this->faker->paragraph)
            ->setCreatedAt(new \DateTime('-' . random_int(1, 20) . ' months'))
            ->setUpdatedAt(new \DateTime);
    }

    private function createComment (): Comment
    {
        return (new Comment)
            ->setCreatedAt(new \DateTime)
            ->setContent($this->faker->paragraph)
            ->setEmail($this->faker->email)
            ->setUsername($this->faker->userName);
    }
}
