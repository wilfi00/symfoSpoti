<?php

namespace App\Services;

use Monolog\Formatter\LineFormatter;

class InfoFormatter extends LineFormatter
{
    public const KEYWORD = '[curious] ';
    
    // this method is called for each log record; optimize it to not hurt performance
    public function format(array $record): string
    {
        if (!(strpos($record['message'], static::KEYWORD) !== false)) {
            return '';
        }
        
        foreach ($record['extra'] as $key => $extra) {
            $record['extra'][$key] = print_r($extra, true);
        }
        
        
        
        return parent::format($record);
    }
}
