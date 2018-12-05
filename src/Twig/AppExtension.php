<?php

namespace App\Twig;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct (ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters (): array
    {
        return [];
    }

    public function getFunctions (): array
    {
        return [
            new TwigFunction('render_asset', [$this, 'renderAsset'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $asset
     *
     * @return string
     */
    public function renderAsset (string $asset)
    {
        static $local, $package;

        if (!isset($local)) {
            $local = !isset($_SERVER['APP_ENV']) || $_SERVER['APP_ENV'] !== 'production';
        }

        if ($local) {
            return (substr($asset, -4) !== '.css') ? 'http://localhost:3003/assets/' . $asset : '';
        } else {

            if (!$package) {
                $package = new Package(new JsonManifestVersionStrategy(realpath('.') . '/assets/manifest.json'));
            }

            return $package->getUrl($asset);
        }
    }
}
