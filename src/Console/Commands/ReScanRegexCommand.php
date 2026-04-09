<?php

namespace AltDesign\AltRedirect\Console\Commands;

use AltDesign\AltRedirect\Contracts\RepositoryInterface;
use Illuminate\Console\Command;

class ReScanRegexCommand extends Command
{
    protected $signature = 'alt-redirect:re-scan-regex';

    protected $description = 'Re-scans all redirects and updates the is_regex flag based on the latest detection logic.';

    public function handle()
    {
        $repository = app(RepositoryInterface::class);
        $redirects = $repository->all('redirects');

        $count = 0;
        foreach ($redirects as $redirect) {
            $isRegexBefore = (bool) ($redirect['is_regex'] ?? false);
            $isRegexAfter = $this->isRegex($redirect['from']);

            if ($isRegexBefore !== $isRegexAfter) {
                $redirect['is_regex'] = $isRegexAfter;
                $repository->save('redirects', $redirect);
                $count++;
            }
        }

        $this->info("Successfully updated $count redirects.");
    }

    protected function isRegex(string $str): bool
    {
        $regexChars = ['*', '(', ')', '^', '$', '|', '{', '}', '\\'];
        foreach ($regexChars as $char) {
            if (str_contains($str, $char)) {
                return true;
            }
        }

        return false;
    }
}
