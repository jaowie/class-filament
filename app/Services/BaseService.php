<?php

namespace App\Services;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseService.
 */
class BaseService
{
    protected Model $model;

    public function setModel(Model $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function findById(int $id, array $relationships = []): ?Model
    {
        return $this->model->with($relationships)->find($id);
    }

    public function updateById(int $id, array $data): Model
    {
        $record = $this->model->findOrFail($id); 
        $record->update($data);   
        return $record->fresh(); 
    }
}
