<?php

namespace Tactics\TableBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Pagerfanta\PagerfantaInterface;

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
            'pagerfanta' => new \Twig_Function_Method($this, 'renderPagerfanta', array('is_safe' => array('html'))),
            'generate_page_route' => new \Twig_Function_Method($this, 'generateRoute'),
        );
    }

    public function renderPagerfanta(PagerfantaInterface $pagerfanta, array $routeOptions = array())
    {
        $routeOptions = $this->resolveRouteOptions($routeOptions);

        return $this->container->get('templating')->render(
            'TacticsTableBundle:Pager:pager.html.twig', array(
                'pager' => $pagerfanta,
                'routeOptions' => $routeOptions,
            )
        );
    }

    public function generateRoute($page, $options)
    {
        $routeParams = array_merge($options['routeParams'], array('page' => $page));

        return $this->container->get('router')->generate($options['routeName'], $routeParams);
    }

    private function resolveRouteOptions(array $options)
    {
        return $this->createAndConfigureOptionsResolver()->resolve($options);
    }

    private function createAndConfigureOptionsResolver()
    {
        $this->guardAgainstInternalRoute($this->container->get('request')->attributes->get('_route'));

        $resolver = new OptionsResolver();

        return $resolver->setDefaults(array(
            'routeName' => $this->container->get('request')->attributes->get('_route'),
            'routeParams' => array_merge($this->container->get('request')->query->all(), $this->container->get('request')->attributes->get('_route_params'))
        ));
    }

    private function guardAgainstInternalRoute($routeName)
    {
        if ('_internal' === $routeName) {
            throw new \Exception('Can not guess the route name for pagination in a subrequest.');
        }
    }
}
