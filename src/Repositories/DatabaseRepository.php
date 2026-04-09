<?php

namespace AltDesign\AltRedirect\Repositories;

use AltDesign\AltRedirect\Contracts\RepositoryInterface;
use AltDesign\AltRedirect\Models\QueryString;
use AltDesign\AltRedirect\Models\Redirect;

class DatabaseRepository implements RepositoryInterface
{
    public function all(string $type): array
    {
        if ($type === 'redirects') {
            return Redirect::all()->toArray();
        } elseif ($type === 'query-strings') {
            return QueryString::all()->toArray();
        }

        return [];
    }

    public function getRegex(string $type): array
    {
        if ($type !== 'redirects') {
            return [];
        }

        return Redirect::where('from', 'like', '%(.*)%')->get()->toArray();
    }

    public function find(string $type, string $key, $value): ?array
    {
        if ($type === 'redirects') {
            $model = Redirect::where($key, $value)->first();

            return $model ? $model->toArray() : null;
        } elseif ($type === 'query-strings') {
            $model = QueryString::where($key, $value)->first();

            return $model ? $model->toArray() : null;
        }

        return null;
    }

    public function save(string $type, array $data): void
    {
        if ($type === 'redirects') {
            Redirect::updateOrCreate(['id' => $data['id']], $data);
        } elseif ($type === 'query-strings') {
            QueryString::updateOrCreate(['id' => $data['id']], $data);
        }
    }

    public function saveAll(string $type, array $data): void
    {
        foreach ($data as $item) {
            $this->save($type, $item);
        }
    }

    public function delete(string $type, array $data): void
    {
        if ($type === 'redirects') {
            Redirect::where('id', $data['id'])->delete();
        } elseif ($type === 'query-strings') {
            QueryString::where('id', $data['id'])->delete();
        }
    }
}
