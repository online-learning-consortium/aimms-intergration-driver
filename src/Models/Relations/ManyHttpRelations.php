<?php namespace OLC\AIMSUserDriver\Models\Relations;

use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use OLC\AIMSUserDriver\Models\DummyModel;
use OLC\AIMSUserDriver\Repositories\UserRepository;

/*
class HttpRelation
Dummy class to mock Eloquent's relation. Eventually we should actually parse the relations and call them as includes if we add those into the api.
 */
class ManyHttpRelations extends Relation
{
    public $user_ids;

    public function addEagerConstraints(array $models)
    {
        return;
    }

    public function addConstraints()
    {

    }

    public function getEager()
    {
        $btmUsersTable = isset($this->parent->btmUsersTable) ? $this->parent->btmUsersTable : str_singular($this->parent->table) . '_user';
        $fk            = isset($this->parent->fk) ? $this->parent->fk : str_singular($this->parent->table) . '_id';
        $ids           = $this->user_ids->pluck('fk_value');
        $pivotValues   = DB::table($btmUsersTable)->whereIn($fk, $ids->toArray())->get();
        $models        = $this->user_ids->pluck('parent');
        $ids           = [];
        $fkMap         = [];
        foreach ($pivotValues as $pivot)
        {
            $ids[]                        = $pivot->user_id;
            $pivotValues[$pivot->user_id] = json_decode(json_encode($pivot), true);
            //FKMAP tells us what users belong to what Model.
            $fkMap[] = ['parent' => $models->where('id', $pivot->$fk)->first(), 'user_id' => $pivot->user_id];
        }
        $users = [];
        if (count($ids) >= 1)
        {
            $users = app(UserRepository::class)->whereIds($ids);

            $fkCollection = collect($fkMap);
            $fkCollection->each(function ($item) use ($pivotValues, $fk, $users)
            {
                $user = $users->where('id', $item['user_id'])->first();
                if (!$user)
                {
                    return;
                }
                $item['parent']->users->push($user);

                if (is_object($item['parent']))
                {
                    $item['parent'] = $item['parent']->toArray();
                }

                $user->pivot = new Pivot($item['parent'], $pivotValues[$user->id], $fk);

            });
            $users = $users->toArray();
        }

        return new Collection($users);
    }

    public function getModels()
    {
        return [];
    }
    /*
    Right now we have a very specific use case of binding users to the parent object. So this will only fit that case.
     */
    public function initRelation(array $models, $relation)
    {
        $this->user_ids = collect();
        foreach ($models as $model)
        {
            $fk = str_singular($model->table) . '_id';
            $this->user_ids->push(['user_id' => $model->user_id, 'fk_name' => $fk, 'fk_value' => $model->id, 'parent' => $model]);
        }
        $this->related = new DummyModel([]);
        foreach ($models as $model)
        {
            $model->setRelation($relation, $this->related->newCollection());
        }
        //dd($models);
        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // the parent models. Then we will return the hydrated models back out.
        foreach ($models as $model)
        {
            if (isset($dictionary[$key = $model->getKey()]))
            {
                $collection = $this->related->newCollection($dictionary[$key]);
                $model->setRelation($relation, $collection);
            }
        }

        return $models;
    }

    protected function buildDictionary(Collection $results)
    {
        $foreign = str_singular($this->parent->table) . '_id';

        // First we will build a dictionary of child models keyed by the foreign key
        // of the relation so that we will easily and quickly match them to their
        // parents without having a possibly slow inner loops for every models.
        $dictionary = [];

        foreach ($results as $result)
        {
            if (isset($result->pivot))
            {
                $dictionary[$result->pivot->$foreign][] = $result;
            }
        }

        return $dictionary;
    }

    public function getResults()
    {
        return $this->get();
    }

    public function updateExistingPivot($id, array $attributes)
    {
        $btmUsersTable   = str_singular($this->parent->table) . '_user';
        $fk              = str_singular($this->parent->table) . '_id';
        $attributes[$fk] = $this->parent->id;
        try
        {
            DB::table($btmUsersTable)->where($fk, $this->parent->id)->where('user_id', $id)->update($attributes);
        }
        catch (Exception $e)
        {
            return false;}
        return true;
    }

    public function detach($id)
    {
        $btmUsersTable = str_singular($this->parent->table) . '_user';
        $fk            = str_singular($this->parent->table) . '_id';
        try {
            DB::table($btmUsersTable)->where($fk, $this->parent->id)->where('user_id', $id)->delete();
        }
        catch (Exception $e)
        {
            return false;}
        return true;
    }

    public function attach($id, array $attributes = [], $touch = true)
    {
        $btmUsersTable = str_singular($this->parent->table) . '_user';
        $fk            = str_singular($this->parent->table) . '_id';
        DB::table($btmUsersTable)->insert([array_merge($attributes, ['user_id' => $id, $fk => $this->parent->id])]);
        $user        = app(UserRepository::class)->find($id);
        $pivotValues = DB::table($btmUsersTable)->where($fk, $this->parent->id)->where('user_id', $id)->first();
        $user->pivot = new Pivot($this->parent->toArray(), json_decode(json_encode($pivotValues), true), $fk);
        if (!$this->parent->relationLoaded('users'))
        {
            $dm         = new DummyModel([]);
            $collection = $dm->newCollection([$user]);
            $this->parent->setRelation('users', $collection);
        }
        $this->parent->users->push($user);
    }

}
