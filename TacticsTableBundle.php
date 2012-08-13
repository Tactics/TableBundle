<?php

namespace Tactics\TableBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tactics\TableBundle\DependencyInjection\Compiler\TablePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TacticsTableBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $container->addCompilerPass(new TablePass());
    }
}
