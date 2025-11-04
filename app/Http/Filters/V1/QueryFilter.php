<?php 

namespace App\Http\Filters\V1;

use Illuminate\Http\Request;
use Illuminate\Contracts\Database\Eloquent\Builder;

abstract class QueryFilter {
    protected $builder;
    protected $request;
    protected $sortable = [];

    public function __construct(Request $request) {
        $this->request = $request;
    }

    

    public function apply(Builder $builder) {
        $this->builder = $builder;

        foreach ($this->request->all() as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $this->builder;
    }

    protected function filter($arr) {
        foreach ($arr as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $this->builder;
    }

    public function sort($value) {
        $sortAttributs = explode(',', $value);
        foreach ($sortAttributs as $sortAttribut) {
            $direction = 'asc';
            if (strpos($sortAttribut, '-') === 0) {
                $direction = 'desc';
                $sortAttribut = substr($sortAttribut, 1);
            }
            if (!in_array($sortAttribut, $this->sortable) && !array_key_exists($sortAttribut, $this->sortable)) {
                continue;
            }

            $colName = $this->sortable[$sortAttribut]?? null;

            if ($colName === null) {
                $colName = $sortAttribut;
            }
            $this->builder->orderBy($sortAttribut, $direction);
        }
    }
}
