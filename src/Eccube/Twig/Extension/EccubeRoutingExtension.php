<?php

namespace Eccube\Twig\Extension;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\TwigFunction;

class EccubeRoutingExtension extends RoutingExtension
{

    // /**
    //  * EccubeRoutingExtension constructor.
    //  *
    //  * @param UrlGeneratorInterface $generator
    //  */
    // public function __construct(UrlGeneratorInterface $generator)
    // {
    //     parent::__construct($generator);
    // }
    //
    // public function getFunctions()
    // {
    //     return array(
    //         new TwigFunction('url', array($this, 'getUrl'), array('is_safe_callback' => array($this, 'isUrlGenerationSafe'))),
    //     );
    // }
    //
    //
    // /**
    //  * @param string $name
    //  * @param array $parameters
    //  * @param bool $relative
    //  *
    //  * @return string
    //  */
    // // public function getPath($name, $parameters = array(), $relative = false)
    // // {
    // // return $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    // // }
    //
    /**
     * @param string $name
     * @param array $parameters
     * @param bool $schemeRelative
     *
     * @return string
     */
    public function getUrl($name, $parameters = array(), $schemeRelative = false)
    {

        error_log("hoge1");

        /** @var RoutingExtension $RoutingExtension */
        try {
            return parent::getUrl($name, $parameters, $schemeRelative);
        } catch (RouteNotFoundException $e) {
            trigger_error($e->getMessage(), E_USER_NOTICE);
        }

        return parent::getUrl('homepage').'404?bind='.$name;
    }

    // public function isUrlGenerationSafe(\Twig_Node $argsNode)
    // {
    //     return parent::isUrlGenerationSafe($argsNode);
    // }
}
