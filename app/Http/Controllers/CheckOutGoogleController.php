<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Repositories;
use Services;

class CheckOutGoogleController extends Controller
{
    private $__repo;
    private $__grabService;

    public function __construct(
            Repositories\IRepository $repository, 
            Services\Grab\IGrabService $grabService
            ){
        $this->__repo = $repository;
        $this->__grabService = $grabService;
    }


    public function CheckGoogle(Request $request){
        ob_end_flush();
        
        //$entityArr = $this->__repo->GetFiltered(20,'id = 59061');
        $entityArr = $this->__repo->GetFiltered(10000,'parsedwithgoogle IS NOT TRUE');

        $i = 1;
        foreach($entityArr as $_lookupEl){    
            echo PHP_EOL . "<br />-" . $i++ . " - " . date("h:i:s") . "." . round(microtime(true) * 1000) . " going to scrape el#" . $_lookupEl->id . PHP_EOL;
            $this->__grabService->parse($_lookupEl);
            echo  PHP_EOL . "<br />scrapped" . PHP_EOL;
            $_lookupEl->save();
            echo  PHP_EOL . "<br />saved" . PHP_EOL;

        }
        return "<br />Complete!";
    }

    public function CheckGoogleAlternative(Request $request){
        ob_end_flush();

        //414222  
        //$entityArr = $this->__repo->GetFiltered(50000,'id=411528');
        //$entityArr = $this->__repo->GetFiltered(50000,'parsedwithgoogle IS TRUE AND parsedwithgooglealternative IS NOT TRUE AND char_length(case when googlename is null then \'\' else googlename end) = 0');
        $entityArr = $this->__repo->GetFiltered(50000,'parsedwithgooglealternative is not true and char_length(case when googlename is null then \'\' else googlename end) = 0 order by id');
        
        $i = 1;
        foreach($entityArr as $_lookupEl){    
            echo PHP_EOL . "<br />-" . $i++ . " - " . date("h:i:s") . "." . round(microtime(true) * 1000) . " going to scrape el#" . $_lookupEl->id . PHP_EOL;
            $this->__grabService->parse($_lookupEl);
            echo  PHP_EOL . "<br />scrapped" . PHP_EOL;
            $_lookupEl->save();
            echo  PHP_EOL . "<br />saved" . PHP_EOL;
            //sleep(20);
        }
        return "<br />Complete!";
    }
}
