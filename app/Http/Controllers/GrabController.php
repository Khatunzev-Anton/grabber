<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Repositories;

class GrabController extends Controller
{
    private $__places_repo;

    public function __construct(Repositories\IRepository $placesRepository){
        $this->__places_repo = $placesRepository;
    }


    public function Grab(Request $request){
        $placesArr = $this->__places_repo->get(20);
        
       foreach($placesArr as $_place){
        echo $_place->postcode . PHP_EOL;
       }

        return "END";
    }
}
