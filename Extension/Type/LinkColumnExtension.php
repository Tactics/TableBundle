<?php

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\ColumnTypeExtensionInterface;
use Tactics\TableBundle\AbstractColumnTypeExtension;
use Tactics\TableBundle\ColumnInterface;
use Tactics\TableBundle\Exception\InvalidOptionException;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of LinkColumnExtension
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class LinkColumnExtension extends AbstractColumnTypeExtension {

    private $router;


    public function __construct($router) {
        $this->router = $router;
    }

    public function execute(ColumnInterface $column, array &$row, array &$cell) {
        $route = $column->getOption('route');

        if (! $route)
        {
            return;
        }

        if (! is_array($route) || !  $route[0])
        {
            throw new InvalidOptionException('"route" option form LinkColumnExtension should be an array with up to 3 elements: route name (string), list of dynamic route parameters (array), list of static route parameters (array).');
        }

        // dynamic route parameters, to be replace by row values
        $route[1] = (isset($route[1]) && is_array($route[1])) ? $route[1]: array();
        // static route parameters
        $route[2] = (isset($route[2]) && is_array($route[2])) ? $route[2]: array();

        // resolve all route parameters
        foreach($route[1] as &$param)
        {
            if (isset($row[$param]))
            {
                $param = $row[$param];
                $param = is_array($param) ? $param['value'] : $param;
            }
            else
            {
                die($param);
                // cannot resolve all route parameters in row
                // no url will be generated.  This is ok as not all rows
                // need to contain a value for each column
                return;
            }
        }

        $routeParameters = array_merge($route[1], $route[2]);

        $url = $this->router->generate($route[0], $routeParameters);
        $cell['url'] = $url;
    }

    /**
     * Overrides the default options from the extended type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('route'));
    }

}
