<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Zero.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace LaravelZero\Framework\Commands;

use LaravelZero\Framework\Components;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\ArrayInput;

final class InstallCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'app:install {component? : The component name}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Installs a new component';

    /**
     * The list of components installers.
     *
     * @var array
     */
    private $components = [
        'console-dusk' => Components\ConsoleDusk\Installer::class,
        'database' => Components\Database\Installer::class,
        'dotenv' => Components\Dotenv\Installer::class,
        'log' => Components\Log\Installer::class,
        'queue' => Components\Queue\Installer::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $title = 'Laravel Zero - Component installer';

        $choices = [];
        foreach ($this->components as $name => $componentClass) {
            $choices[$name] = $this->app->make($componentClass)->getDescription();
        }

        if (! Process::isTtySupported()) {
            $option = $this->choice($title, $choices);
        } else {
            $option = $this->argument('component') ?: $this->menu(
                $title,
                $choices
            )
                ->setForegroundColour('green')
                ->setBackgroundColour('black')
                ->open();
        }

        if ($option !== null && ! empty($this->components[$option])) {
            $command = tap($this->app[$this->components[$option]])->setLaravel($this->app);

            $command->setApplication($this->getApplication());

            $this->info("Installing {$option} component...");

            $command->run(new ArrayInput([]), $this->output);
        }
    }
}
