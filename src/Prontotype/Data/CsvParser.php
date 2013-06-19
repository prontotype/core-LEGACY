<?php

namespace Prontotype\Data;

Class CsvParser extends Parser {

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function getHandledExtensions()
    {
        return array(
            'csv'
        );
    }
    
    public function parse($content)
    {
        if ( ! is_string($content) ) {
            throw new \Exception('CSV data format error');
        }
        $config = $this->app['pt.config']->get('data.csv');
        $data = $this->csvToArray($content, $config['escape'], $config['enclosure'],  $config['delimiter']);
        if ( $data && $config['headers'] ) {
            $indexedData = array();
            $headerData = $data[0];
            unset($data[0]);
            $data = array_values($data);
            foreach( $data as $row ) {
                $rowData = array();
                for($i = 0; $i < count($row); $i++) {
                    if ( isset($headerData[$i])) {
                        $rowData[$headerData[$i]] = $row[$i];
                    } else {
                        $rowData[$i] = $row[$i];
                    }
                }
                if ( $config['id_header'] && isset($rowData[$config['id_header']]) ) {
                    $indexedData[$rowData[$config['id_header']]] = $rowData;
                } else {
                    $indexedData[] = $rowData;
                }
            }
            $data = $indexedData;
        }
        return $data;
    }
    
    function csvToArray($fileContent,$escape = '\\', $enclosure = '"', $delimiter = '\\n')
    {
        $lines = array();
        $fields = array();

        if ($escape == $enclosure) {
            $escape = '\\';
            $fileContent = str_replace(array('\\',$enclosure.$enclosure,"\r\n","\r"),
            array('\\\\',$escape.$enclosure,"\\n","\\n"),$fileContent);
        } else {
            $fileContent = str_replace(array("\r\n","\r"),array("\\n","\\n"),$fileContent);
        }

        $nb = strlen($fileContent);
        $field = '';
        $inEnclosure = false;
        $previous = '';

        for ($i = 0;$i<$nb; $i++) {
            $c = $fileContent[$i];
            if ($c === $enclosure) {
                if ($previous !== $escape) {
                    $inEnclosure ^= true;
                } else {
                    $field .= $enclosure;
                }
            } elseif ($c === $escape) {
                $next = $fileContent[$i+1];
                if ($next != $enclosure && $next != $escape) {
                    $field .= $escape;
                } 
            } elseif ($c === $delimiter) {
                if ($inEnclosure) {
                    $field .= $delimiter;
                } else {
                    $fields[] = $field;
                    $field = '';
                }
            } elseif ($c === "\n") {
                $fields[] = $field;
                $field = '';
                $lines[] = $fields;
                $fields = array();
            } else {
                $field .= $c;
            }   
            $previous = $c;
        }
        if ($field !== '') {
            $fields[] = $field;
            $lines[] = $fields;
        }
        return $lines;
    }
    
}
