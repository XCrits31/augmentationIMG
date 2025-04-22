<?php

namespace App\Jobs;

use App\Events\ProcessImageCompleted;
use App\Models\Transformation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $inputPath;
    protected $outputDir;
    protected $transformations;

    /**
     * Create a new job instance.
     *
     * @param string $inputPath
     * @param string $outputDir
     * @param array $transformations
     */
    public function __construct(string $inputPath, string $outputDir, array $transformations)
    {
        $this->inputPath = $inputPath;
        $this->outputDir = $outputDir;
        $this->transformations = $transformations;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $scriptPath = base_path('scripts/monai_processing.py');
        $pythonInterpreter = base_path('venv/bin/python3');

        $args = [
            $pythonInterpreter,
            $scriptPath,
            $this->inputPath,
            $this->outputDir,
            json_encode($this->transformations),
        ];

        $process = new Process($args);
        $process->run();

        if (!$process->isSuccessful()) {
            // Логирование ошибки
            throw new \Exception($process->getErrorOutput());
        }

        $output = json_decode($process->getOutput(), true);
        if (isset($output['error'])) {
            throw new \Exception($output['error']);
        }

        $processedPath = $output['processed'];

        // Пример сохранения результата в базу
        Transformation::create([
            'image_name' => basename($this->inputPath),
            'transformations' => json_encode($this->transformations),
            'output_image' => basename($processedPath),
        ]);
        $processedPath = asset('storage/processed/' . basename($processedPath));
        $message = 'Обработка завершена: ' . basename($processedPath);
        event(new ProcessImageCompleted($processedPath, $message));;

    }
}
