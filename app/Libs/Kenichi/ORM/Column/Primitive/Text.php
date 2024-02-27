<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Text extends Column
{
    protected string $sqlName = 'TEXT';
    protected bool $sanitize = true;
    protected string $dibiModificator = '%s';
    
    public function getPerex(int $maxlength = 50): string
    {
        return self::perex($this->getValue(), $maxlength);
    }
    
    public static function perex(string $string, int $perexMax = 100): string
    {
      
                        $perex = strip_tags($string);
                        if (strlen($perex) > $perexMax) {
                           $pies = explode(" ",$perex);
                           $count = 0;
                           $perex = "";
                           for ($i = 0;$i < count($pies); $i++) {
                               $count = strlen($perex.' '.$pies[$i]);
                               if ($count > $perexMax) {
                                   $perex .= '...';
                                   break;
                               }
                               else $perex .= ' '.$pies[$i];
                           }
                        }
                      return $perex;
     }
}