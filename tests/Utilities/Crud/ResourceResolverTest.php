<?php

namespace App\Tests\Utilities\Crud;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utilities\Crud\CrudAnnotation;
use App\Utilities\Crud\CrudException;
use App\Utilities\Crud\ResourceResolver;
use Doctrine\Common\Annotations\Reader;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\StringInput;

class ResourceResolverTest extends KernelTestCase
{
    /**
     * @var ResourceResolver
     */
    private $resolver;

    /**
     * @var string
     */
    private static $entityDir;

    /** @var bool */
    private static $migrated;

    protected function setUp ()
    {

        if (!self::$migrated) {
            $kernel = self::bootKernel();
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $application->run(new StringInput('doctrine:schema:update --force'));
            $application->run(new StringInput('doctrine:fixtures:load --no-interaction'));
            self::$entityDir = self::$container->getParameter('kernel.project_dir') . '/src/Entity';

            self::$migrated = true;
        }

        $this->resolver = self::$container->get(ResourceResolver::class);
    }

    protected function tearDown ()
    {
        //pour avoir le kernel qui ne reboot jamais !!
    }

    /** @test */
    public function goodReturnOfEntityClassName ()
    {
        $resource = 'user';
        $entityClassName = $this->resolver->setResource($resource)->resolveEntityClassName();

        $this->assertSame('App\Entity\User', $entityClassName);
    }

    /** @test */
    public function wrongReturnOfEntityClassName ()
    {
        $resource = 'toto';
        $this->expectException(CrudException::class);
        $this->resolver->setResource($resource)->resolveEntityClassName();
    }

    /** @test */
    public function goodReturnOfResolveRepository ()
    {
        $resource = 'user';
        $repository = $this->resolver->setResource($resource)->resolveRepository();

        $this->assertTrue($repository instanceof UserRepository);
    }

    /** @test */
    public function wrongReturnOfResolveRepository ()
    {
        $resource = 'toto';
        $this->expectException(CrudException::class);
        $this->resolver->setResource($resource)->resolveRepository();
    }

    /** @test */
    public function createEntityWithGoodResource ()
    {
        $resource = 'user';
        $entity = $this->resolver->setResource($resource)->createEntity();
        $this->assertTrue($entity instanceof User);
    }

    /** @test */
    public function createEntityWithWrongResource ()
    {
        $resource = 'toto';
        $this->expectException(CrudException::class);
        $this->resolver->setResource($resource)->createEntity();
    }

    private function _camelCaseToKebabCase ($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('-', $ret);
    }

    /** @test */
    public function testGetListOfResources ()
    {
        $resources = $this->resolver->getListOfResources();

        $resourcesInDir = array_diff(scandir(self::$entityDir), ['.', '..', '.gitignore']);

        $this->assertEquals(count($resourcesInDir), count($resources));

        foreach($resourcesInDir as $resourceInDir) {
            $resource = $this->_camelCaseToKebabCase(substr($resourceInDir, 0, -4));
            $resourceCount = $resources[$resource];
            $this->assertEquals($this->resolver->setResource($resource)->resolveRepository()->count([]), $resourceCount);
        }
    }

    /** @test */
    public function getEntity ()
    {
        $entity = self::$container->get('doctrine')->getManager()->getRepository(User::class)->find(5);

        $entityWithResolver = $this->resolver->setResource('user')->getEntity(5);

        $this->assertSame($entity, $entityWithResolver);
    }

    /** @test */
    public function createFormBuilderForCreateResource ()
    {
        $reader = self::$container->get(Reader::class);

        $entity = $this->resolver->setResource('User')->createEntity();
        $builder = $this->resolver->createFormBuilder($entity);

        $keys = array_keys($builder->all());

        $properties = (new \ReflectionClass(User::class))->getProperties();

        foreach ($properties as $property) {
            /** @var CrudAnnotation $annotation */
            $annotation = $reader->getPropertyAnnotation($property, CrudAnnotation::class);

            $in_array = in_array($property->name, $keys);

            if (!$annotation || $annotation->showInCreate === false) {
                $this->assertFalse($in_array);
            } else {
                $this->assertTrue($in_array);
            }
        }
    }

    /** @test */
    public function createFormBuilderForEditResource ()
    {
        $reader = self::$container->get(Reader::class);

        $entity = $this->resolver->setResource('User')->getEntity(1);
        $builder = $this->resolver->createFormBuilder($entity);

        $keys = array_keys($builder->all());

        $properties = (new \ReflectionClass(User::class))->getProperties();

        foreach ($properties as $property) {
            /** @var CrudAnnotation $annotation */
            $annotation = $reader->getPropertyAnnotation($property, CrudAnnotation::class);

            $in_array = in_array($property->name, $keys);

            if (!$annotation || $annotation->showInEdit === false) {
                $this->assertFalse($in_array);
            } else {
                $this->assertTrue($in_array);
            }
        }
    }

    /** @test */
    public function getIndexProperties ()
    {
        $reader = self::$container->get(Reader::class);

        $properties = $this->resolver->setResource('user')->getIndexProperties();
        $keys = array_keys($properties);

        $properties = (new \ReflectionClass(User::class))->getProperties();

        foreach ($properties as $property) {
            /** @var CrudAnnotation $annotation */
            $annotation = $reader->getPropertyAnnotation($property, CrudAnnotation::class);

            $in_array = in_array($property->name, $keys);

            if ($annotation && $annotation->showInIndex === false) {
                $this->assertFalse($in_array);
            } else {
                $this->assertTrue($in_array);
            }
        }
    }
}
