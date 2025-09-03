<?php

namespace Nikolaynesov\LaravelCommandAssistant;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandController extends Controller
{
    public function available(): JsonResponse
    {
        $commands = Artisan::all();

        $allowedCommands = collect($commands)
            ->filter(fn($command) => $command->getDefinition()->hasOption('laravel-assistant'))
            ->map(fn($command) => [
                'command' => $command->getName(),
                'description' => $command->getDescription(),
                'arguments' => collect($command->getDefinition()->getArguments())
                    ->map(fn(InputArgument $arg) => $arg->getName())
                    ->values(),
                'options' => collect($command->getDefinition()->getOptions())
                    ->reject(fn(InputOption $opt) => $opt->getName() === 'laravel-assistant')
                    ->map(fn(InputOption $opt) => $opt->getName())
                    ->values(),
            ])
            ->values();

        return response()->json($allowedCommands);
    }

    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'command_string' => 'required|string',
            'executed_by' => 'nullable|string',
        ]);

        $rawCommand = $request->input('command_string');
        $parts = preg_split('/\s+/', $rawCommand);
        $commandName = $parts[0];

        $commands = Artisan::all();

        // Validate command exists
        if (!array_key_exists($commandName, $commands)) {
            return response()->json([
                'error' => 'Invalid or unknown command.'
            ], 400);
        }

        // Validate command defines --laravel-assistant
        $definition = $commands[$commandName]->getDefinition();
        if (! $definition->hasOption('laravel-assistant')) {
            return response()->json([
                'error' => 'This command is not available for the assistant.'
            ], 403);
        }

        // Append --laravel-assistant if missing
        if (! str_contains($rawCommand, '--laravel-assistant')) {
            $rawCommand .= ' --laravel-assistant';
        }

        try {
            Artisan::call($rawCommand);

            ExecutedAssistantCommand::create([
                'command' => $rawCommand,
                'executed_by' => $request->input('executed_by', 'assistant'),
            ]);

            return response()->json([
                'message' => 'Command executed successfully.',
                'output' => Artisan::output(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Command execution failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}