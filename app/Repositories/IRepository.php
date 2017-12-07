<?php
namespace Repositories;

interface IRepository extends ILookupRepository{
    public function Save($modelsArr);
}