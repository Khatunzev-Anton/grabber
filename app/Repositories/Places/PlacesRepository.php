<?php

namespace Repositories\Places;

use Illuminate\Database\Eloquent\Model;
use Repositories;

class PlacesRepository implements Repositories\IRepository {

    private $model;

    public function __construct(Model $model){
        $this->model = $model;
    }

    public function Get($num){
        return $this->model->select()->limit($num)->get();
    }
}