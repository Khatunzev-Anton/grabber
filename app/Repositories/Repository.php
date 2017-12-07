<?php

namespace Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Repository implements IRepository {

    protected $model;

    public function __construct(Model $model){
        $this->model = $model;
    }

    public function Get($num){
        return $this->model->select()->limit($num)->get();
    }
    
    public function Save($modelsArr){
        return DB::table($this->model->getTable())->insert($modelsArr);
    }
}