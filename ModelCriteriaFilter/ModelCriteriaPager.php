<?php

namespace Tactics\TableBundle\ModelCriteriaFilter;

use Criteria;
use ModelCriteria;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class ModelCriteriaPager implements ModelCriteriaFilterInterface
{
    /**
      * @var $container ContainerInterface A ContainerInterface instance.
     */
    protected $container;

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
    public function execute(ModelCriteria $mc, $key = null, $options = array())
    {
        $request = $this->container->get('request'); 
        $session = $this->container->get('session');

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        
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
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'max_per_page' => 10
        ));
    }
}
