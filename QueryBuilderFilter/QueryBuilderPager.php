<?php

namespace Tactics\TableBundle\QueryBuilderFilter;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QueryBuilderPager implements QueryBuilderFilterInterface
{
    /**
      * @var $container ContainerInterface A ContainerInterface instance.
     */
    protected $container;

    protected $namespace = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(QueryBuilder $qb, $key = null, $options = array())
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $session = $this->container->get('session');

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        
        $options = $resolver->resolve($options);

        $key = null === $key ? 'pager/'.$request->attributes->get('_route') : $key;
        $this->namespace = $key;

        $page = $request->get('page');
        if ($request->get('pager_namespace') && $request->get('pager_namespace') !== $this->getNamespace()) {
            $page = $session->get($key);
        }
        elseif ($page) {
            $session->set($key, $page);
        } elseif (! $page && $session->has($key)) {
            $page = $session->get($key);
        } else {
            $page = 1;
            $session->set($key, $page);
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb, false));
        $pager->setMaxPerPage($options['max_per_page']);

        // Deal with out of range pages. Typical scenario is when you surf to 
        // page x and apply a filter to the collection.
        if ($page > $pager->getNbPages()) {
            $page = 1;
        }

        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'max_per_page' => 10
        ));
    }

    public function getNamespace()
    {
        return $this->namespace;
    }
}
