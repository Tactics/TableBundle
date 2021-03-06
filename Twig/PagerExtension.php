<?php

namespace Tactics\TableBundle\Twig;

use Pagerfanta\PagerfantaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagerExtension extends \Twig_Extension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'pager';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('pagerfanta', [$this, 'renderPagerfanta'], array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('generate_page_route', [$this, 'generateRoute']),
        );
    }

    public function renderPagerfanta(PagerfantaInterface $pagerfanta, array $options = array())
    {
        $options = $this->resolveOptions($options);
        $this->guardAgainstInternalRoute($options['routeName']);

        return $this->container->get('templating')->render(
            'TacticsTableBundle:Pager:pager.html.twig', array(
                'pager' => $pagerfanta,
                'options' => $options,
            )
        );
    }

    public function generateRoute($page, $options)
    {
        $routeParams = array_merge($options['routeParams'], array('page' => $page));

        return $this->container->get('router')->generate($options['routeName'], $routeParams);
    }

    private function resolveOptions(array $options)
    {
        return $this->createAndConfigureOptionsResolver()->resolve($options);
    }

    private function createAndConfigureOptionsResolver()
    {
        $resolver = new OptionsResolver();

        return $resolver
            ->setDefaults(array(
                'routeName' => $this->container->get('request_stack')->getMasterRequest()->attributes->get('_route'),
                'routeParams' => array_merge($this->container->get('request_stack')->getMasterRequest()->query->all(), $this->container->get('request_stack')->getMasterRequest()->attributes->get('_route_params', array()))
            ))
            ->setDefined(
                'containerCssClass'
            )
            ;
    }

    private function guardAgainstInternalRoute($routeName)
    {
        if ('_internal' === $routeName) {
            throw new \Exception('Can not guess the route name for pagination in a subrequest.');
        }
    }
}
