<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class QueueDeleteCommand extends Command
{
    protected $signature = 'queue:deletezip';
    protected $description = 'Delete all zip files in storage/zip';

    public function handle()
    {
        // Use the 'public' disk
        $files = Storage::disk('public')->files('zip');

        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }

        $this->info('Deleted all zip files from public/storage/zip');
    }
}
