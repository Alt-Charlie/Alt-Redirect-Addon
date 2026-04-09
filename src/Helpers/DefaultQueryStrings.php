<?php

namespace AltDesign\AltRedirect\Helpers;

use AltDesign\AltRedirect\Contracts\RepositoryInterface;
use AltDesign\AltRedirect\Repositories\DatabaseRepository;
use Illuminate\Support\Facades\Schema;
use Statamic\Fields\BlueprintRepository;
use Statamic\Filesystem\Manager;

class DefaultQueryStrings
{
    public array $defaultQueryStrings = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'gclid',
        'fbclid',
        'msclkid',
        'srsltid',
    ];

    public function makeDefaultQueryStrings()
    {
        $repository = app(RepositoryInterface::class);

        // If using database driver, check if tables exist
        if ($repository instanceof DatabaseRepository) {
            if (! Schema::hasTable('alt_query_strings')) {
                return;
            }
        }

        $blueprint = with(new BlueprintRepository)->setDirectory(__DIR__.'/../../resources/blueprints')->find('query-strings');
        // Add the values to the array

        foreach ($this->defaultQueryStrings as $query) {
            $fields = $blueprint->fields();
            $arr = [
                'id' => md5($query),
                'sites' => ['default'],
                'query_string' => $query,
                'strip' => true,
            ];
            $fields = $fields->addValues($arr);
            $fields->validate();
            $repository->save('query-strings', $fields->process()->values()->toArray());
        }
        (new Manager)->disk()->makeDirectory('content/alt-redirect/.installed');
    }
}
