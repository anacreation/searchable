<?php
/**
 * Author: Xavier Au
 * Date: 2019-05-05
 * Time: 00:14
 */

namespace Anacreation\Searchable\traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    public function scopeSearch(Builder $query, string $keyword = null
    ): Builder {
        if(is_null($keyword)) {
            return $query;
        }

        $columns = $this->searchableColumns ?? [];
        foreach($columns as $index => $column) {

            if(strpos($column,
                      ".") > -1) {
                list($column, $relationship) = $this->parseRelationshipColumn($column);

                $queryFunction = function($q) use ($column, $keyword) {
                    return $q->where($column,
                                     'like',
                                     "%{$keyword}%");
                };

                if($index == 0) {
                    $query->whereHas($relationship,
                                     $queryFunction);
                } else {
                    $query->orWhereHas($relationship,
                                       $queryFunction);
                }
            } else {
                if($index == 0) {
                    $query->where($column,
                                  'like',
                                  "%{$keyword}%");
                } else {
                    $query->orWhere($column,
                                    'like',
                                    "%{$keyword}%");
                }
            }
        }

        return $query;
    }

    /**
     * @param $column
     * @return array
     */
    private function parseRelationshipColumn($column): array {
        $array = explode('.',
                         $column);
        $column = $array[1];
        $relationship = $array[0];

        return [$column, $relationship];
    }
}
