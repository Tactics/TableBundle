<?php

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Column;
use Tactics\TableBundle\ColumnHeader;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EmailValidator;


/**
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class EmailColumn extends Column
{
    
    /**
     * {@inheritdoc}
     */
    public function getCell($row)
    {
        $cell = parent::getCell($row);
        
        // Create mailto link if cell value is a valid e-mail address
        $value = (string) $cell['value'];
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL);
        
        if ($valid)
        {
            $cell['url'] = 'mailto:' . $cell['value'];
        }
        
        return $cell;
    }
}
