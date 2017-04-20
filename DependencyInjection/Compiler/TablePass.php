<?php

namespace Tactics\TableBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of TablePass
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class TablePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tactics.table.factory')) {
            return;
        }
        $definition = $container->getDefinition('tactics.table.factory');

        $columnExtensions = array();
        $headerExtensions = array();
        
        foreach ($container->findTaggedServiceIds('tactics.table.extension') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (empty($attributes['alias'])) {
                    throw new \InvalidArgumentException(sprintf('The alias is not defined in the "tactics.table.column.extension" tag for the service "%s"', $id));
                }
                $attributes['alias'];
                
                switch($attributes['alias'])
                {
                    case 'column':
                        $columnExtensions[] = $id;
                        break;
                    case 'header':
                        $headerExtensions[] = $id;
                        break;
                    default:
                        throw new \InvalidArgumentException(sprintf('The alias "%s" is unknown in the "tactics.table.column.extension" tag for the service "%s.  Options are "column", "header".', $attributes['alias'], $id));
                }
                
            }
        }
        $definition->replaceArgument(1, $columnExtensions);
        $definition->replaceArgument(2, $headerExtensions);
    }
}

