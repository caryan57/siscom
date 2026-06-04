<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;

#[Signature('app:app-setup')]
#[Description('Command description')]
class AppSetup extends Command
{
    protected $signature = 'app:setup';
    protected $description = 'Setup inicial: genera permisos y siembra datos base';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generando permisos con Shield...');
        $this->call('shield:generate', [
            '--all'   => true,
            '--panel' => 'admin',
        ]);

        $this->info('✅ Setup completado.');

        if ($this->confirm('¿Deseas iniciar los servidores de desarrollo?', true)) {
            $this->info('Iniciando php artisan serve y npm run dev...');

            $serve = new Process(['php', 'artisan', 'serve']);
            $npm   = new Process(['npm', 'run', 'dev']);

            $serve->start();
            $npm->start();

            $this->info('✅ Servidores iniciados.');
            $this->info('   Laravel: http://localhost:8000');
            $this->info('   Vite:    http://localhost:5173');
            $this->line('   Presiona Ctrl+C para detener.');

            while ($serve->isRunning() || $npm->isRunning()) {
                if ($out = $serve->getIncrementalOutput()) {
                    $this->line('<fg=green>[Laravel]</> ' . trim($out));
                }
                if ($out = $npm->getIncrementalOutput()) {
                    $this->line('<fg=blue>[Vite]</> ' . trim($out));
                }
                usleep(100000); // 100ms
            }
        }
    }
}
