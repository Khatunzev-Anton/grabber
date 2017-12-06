<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Repositories;
use Services;

class GrabController extends Controller
{
    private $__placesRepo;
    private $__grabService;

    public function __construct(
            Repositories\IRepository $placesRepository, 
            Services\Grab\IGrabService $grabService
            ){
        $this->__placesRepo = $placesRepository;
        $this->__grabService = $grabService;
    }


    public function Grab(Request $request){
        $placesArr = $this->__placesRepo->get(20);
        
        $parsedArr = [];
        
       foreach($placesArr as $_place){           
            $parsedArr = $this->__grabService->parse($_place->city . ',,' . $_place->postcode);

       }

        return json_encode($parsedArr);
    }
}
