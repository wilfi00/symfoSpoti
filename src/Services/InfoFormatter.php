<?php

namespace App\Services;

use Monolog\Formatter\LineFormatter;

class InfoFormatter extends LineFormatter
{
    const KEYWORD = '[curious] ';
    
    
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
    
    /**
     * Formats a set of log records.
     *
     * @param  array  $records A set of records to format
     * @return string The formatted set of records
     */
    public function formatBatch(array $records): string
    {
        $message = '';
        foreach ($records as $record) {
            dump($record);
            $message .= $this->format($record);
        }

        return $message;
    }
}
