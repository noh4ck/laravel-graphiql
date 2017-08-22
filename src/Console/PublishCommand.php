<?php

namespace Graphiql\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'graphiql:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish config, views, assets';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Publishing all files');
        $this->call('vendor:publish', [
            '--provider' => 'Graphiql\GraphiqlServiceProvider',
        ]);
    }

    /**
     * Execute the console command.
     *
     * @deprecated
     * @return void
     */
    public function fire()
    {
        $this->handle();
    }
}
