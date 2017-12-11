<?php
namespace Repositories;

interface ILookupRepository{

    public function Get($num);

    public function GetFiltered($num, $whereClause);
}