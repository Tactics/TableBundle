<?php

namespace Tactics\TableBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tactics\TableBundle\QueryBuilderFilter\QueryBuilderPager;

class QueryBuilderPagerExtension extends \Twig_Extension
{
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container A container.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
           new \Twig_SimpleFunction('pager_widget', [$this, 'renderQueryBuilderPager'], array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders a QueryBuilderPager.
     *
     * @param QueryBuilderPager   $pager      A QueryBuilderPager instance.
     * @param string              $viewName   The view name.
     * @param array               $options    An array of options (optional).
     *
     * @return string The pagerfanta rendered.
     */
    public function renderQueryBuilderPager($tableBuilder, $viewName = null, array $options = array())
    {
        $options = array_replace(array(
            'routeName'     => null,
            'routeParams'   => array(),
            'pageParameter' => '[page]',
        ), $options);

        $pagerNamespace = $tableBuilder->getPagerNamespace();

        if (null === $viewName) {
            $viewName = $this->container->getParameter('white_october_pagerfanta.default_view');
        }

        $router = $this->container->get('router');

        if (null === $options['routeName']) {
            $request = $this->container->get('request');

            $options['routeName'] = $request->attributes->get('_route');
            if ('_internal' === $options['routeName']) {
                throw new \Exception('PagerfantaBundle can not guess the route when used in a subrequest');
            }

            $options['routeParams'] = array_merge($request->query->all(), $request->attributes->get('_route_params'));
        }

        $routeName = $options['routeName'];
        $routeParams = $options['routeParams'];
        $pagePropertyPath = new PropertyPath($options['pageParameter']);
        $routeGenerator = function($page) use($router, $routeName, $routeParams, $pagePropertyPath, $pagerNamespace) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $propertyAccessor->setValue($routeParams, $pagePropertyPath, $page);
            if ($pagerNamespace) {
                $routeParams['pager_namespace'] = $pagerNamespace;
            }

            return $router->generate($routeName, $routeParams);
        };

        return $this->container->get('white_october_pagerfanta.view_factory')->get($viewName)->render($tableBuilder->getPagerfanta(), $routeGenerator, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'query_builder_pager';
    }
}
