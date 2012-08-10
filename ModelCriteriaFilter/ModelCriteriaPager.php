<?php

namespace Tactics\TableBundle\ModelCriteriaFilter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use \ModelCriteria;
use \Criteria;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class ModelCriteriaPager implements ModelCriteriaFilterInterface
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function execute(ModelCriteria $mc, $key = null, $options = array())
    {
        $request = $this->container->get('request'); 
        $session = $this->container->get('session');

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        
        $options = $resolver->resolve($options);

        $key = null === $key ? 'pager/'.$request->attributes->get('_route') : $key;

        $page = $request->get('page');

        if ($page) {
            $session->set($key, $page);
        } elseif (! $page && $session->has($key)) {
            $page = $session->get($key);
        } else {
            $page = 1;
            $session->set($key, $page);
        }

        return $mc->paginate($page, $options['max_per_page']);
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'max_per_page' => 10
        ));
    }
}
