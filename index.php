#!/usr/bin/php
<?php

require 'vendor/autoload.php';

use VmgLtd\HlrLookupClient;


$climate = new League\CLImate\CLImate;

$climate->arguments->add([
    'user' => [
        'prefix'       => 'u',
        'longPrefix'   => 'user',
        'description'  => 'hlr-lookups.com username',
        'required' => true
    ],
    'pass' => [
        'prefix'       => 'p',
        'longPrefix'   => 'pass',
        'description'  => 'hlr-lookups.com password',
        'required' => true
    ],
    'file' => [
        'prefix'       => 'f',
        'longPrefix'   => 'file',
        'description'  => 'the input file',
        'required'     => true,
    ],
    'output' => [
        'prefix'       => 'o',
        'longPrefix'   => 'output',
        'description'  => 'the output file to write too'
    ]
]);


$climate->description("\r\nCheck if mobile phone numbers are from the UK and valid. Uses the https://www.hlr-lookups.com/ API - an account is required.");

//validate our args
try{
    $climate->arguments->parse();
    if ( !file_exists($climate->arguments->get('file')) ) {
        throw new Exception('Input file does not exist.');
    }
    $inputFile = fopen($climate->arguments->get('file'), "r");
    if ( !$inputFile ) {
        throw new Exception('Could not open input file.');
    }
    if($climate->arguments->defined('output')){
        $outputFile = fopen($climate->arguments->get('output'), "w+");
        if ( !$outputFile ) {
            throw new Exception('Could not open output file.');
        }
    }
}
catch(Exception $e){
    $climate->red("\n". $e->getMessage()."\n");
    $climate->usage();
    $climate->out("\n");
    exit();
}


$client = new HlrLookupClient( $climate->arguments->get('user'), $climate->arguments->get('pass') );

$numberTable = [];

//loop through each line in our input file
while($num = fgets($inputFile)){

    $numberRow = [
        'num' => $num,
        'carrier' => null,
        'valid' => false
    ];
    
    $num = preg_replace('/^07/','447',$num); //add 44 to make it international if it isn't already

    if(preg_match('/^\+?44/',$num)){ //if this seems to be a valid number (it at least starts with (+)44 now)

        $response = json_decode($client->submitSyncLookupRequest($num));
        if($response->success){
            $result = $response->results[0];

            if($result->msisdncountrycode == "GB"){
                $numberRow['valid'] = true;
                $numberRow['carrier'] = ($result->portednetworkname != null)? $result->portednetworkname : $result->originalnetworkname;
            }
        }
    }

    array_push($numberTable,$numberRow);

}
fclose($inputFile);

if($climate->arguments->defined('output') && $outputFile){

    foreach($numberTable as $row){
        fputcsv($outputFile,$row);
    }
    fclose($outputFile);

}else{
    $climate->table($numberTable);
}