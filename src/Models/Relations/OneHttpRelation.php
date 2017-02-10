<?php namespace OLC\AIMSUserDriver\Models\Relations;

use Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use OLC\AIMSUserDriver\Models\DummyModel;

/*
class HttpRelation
Dummy class to mock Eloquent's relation. Eventually we should actually parse the relations and call them as includes if we add those into the api.
 */
class OneHttpRelation extends Relation
{

    public $foreigns = [];
    public $repository;
    public $otherKey;
    public $table;

    public function addEagerConstraints(array $models)
    {
        return;
    }

    public function addConstraints()
    {

    }

    //this method overrides the use of the database entirely.
    //We're going to cache 1:1 objects so we don't have N+1 HTTP Requests
    public function getEager()
    {
        $models = [];
        foreach ($this->foreigns as $foreign)
        {
            $prefix = $this->table . '.';
            $key    = $foreign['otherKey'];
            if (!$key)
            {
                continue;
            }
            if (!Cache::has($prefix . $key))
            {
                if ($model = $this->repository->find($key))
                {
                    $name         = $foreign['fk_name'];
                    $model->pivot = new Pivot($foreign['parent'], [$name => $foreign['fk_value']], $this->table);
                    Cache::forever($prefix . $key, $model);
                }
            }
            $models[] = Cache::get($prefix . $key);
        }
        return new Collection($models);
    }

    public function getModels()
    {
        return [];
    }

    public function initRelation(array $models, $relation)
    {
        $key = $this->otherKey;
        $fk  = '';

        foreach ($models as $model)
        {
            $fk = str_singular($model->table) . '_id';
            if (!$model->$key)
            {
                continue;
            }
            $this->foreigns[] =
                [
                'otherKey' => $model->$key,
                'fk_name' => $fk,
                'fk_value' => $model->id, 'parent' => $model,
            ];
        }
        $this->related = new DummyModel([]);
        foreach ($models as $model)
        {
            if (!$model->$key)
            {
                $model->setRelation($relation, null);
                continue;
            }
            $model->setRelation($relation, $this->related);
        }
        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        $foreign = $this->otherKey;
        $other   = 'id';

        // First we will get to build a dictionary of the child models by their primary
        // key of the relationship, then we can easily match the children back onto
        // the parents using that dictionary and the primary key of the children.
        $dictionary = [];
        foreach ($results as $result)
        {
            if (!$result)
            {
                continue;
            }
            $dictionary[$result->$other] = $result;
        }

        // Once we have the dictionary constructed, we can loop through all the parents
        // and match back onto their children using these keys of the dictionary and
        // the primary key of the children to map them onto the correct instances.
        foreach ($models as $model)
        {
            if (isset($dictionary[$model->$foreign]))
            {
                $model->setRelation($relation, $dictionary[$model->$foreign]);
            }
        }

        return $models;
    }

    public function getResults()
    {
        return $this->get();
    }

}