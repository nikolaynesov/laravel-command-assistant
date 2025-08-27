<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Console\Command;

uses(RefreshDatabase::class);

// Helper to register commands to the kernel during tests
function registerCommand(Command $command): void {
    /** @var Kernel $kernel */
    $kernel = app(Kernel::class);
    $kernel->registerCommand($command);
}

it('available returns only commands that declare --laravel-assistant option', function () {
    // Arrange: register two commands, only one is assistant-enabled
    $assistantCommand = new class extends Command {
        protected $signature = 'demo:assistant {--laravel-assistant} {name?} {--flag}';
        protected $description = 'Assistant-enabled demo command';
        public function handle(): int { $this->info('assistant ok'); return self::SUCCESS; }
    };
    $normalCommand = new class extends Command {
        protected $signature = 'demo:normal';
        protected $description = 'Normal command';
        public function handle(): int { return self::SUCCESS; }
    };

    registerCommand($assistantCommand);
    registerCommand($normalCommand);

    // Act
    $response = $this->withHeaders([
        'Authorization' => 'Bearer test-key',
        'Accept' => 'application/json',
    ])->getJson(route('command-assistant.available'));

    // Assert
    $response->assertOk();
    $response->assertJsonCount(1); // only the assistant-enabled command should be listed
    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('0.command', 'demo:assistant')
        ->where('0.description', 'Assistant-enabled demo command')
        ->where('0.options', function ($opts) {
            return is_array($opts) || $opts instanceof \Illuminate\Support\Collection;
        })
    );
});

it('execute runs the command, appends flag, stores audit record and returns output', function () {
    // Arrange: register an assistant-enabled command
    $assistantCommand = new class extends Command {
        protected $signature = 'demo:assistant {--laravel-assistant}';
        protected $description = 'Assistant-enabled demo command';
        public function handle(): int { $this->info('ran'); return self::SUCCESS; }
    };

    registerCommand($assistantCommand);

    // Act
    $response = $this->withHeaders([
        'Authorization' => 'Bearer test-key',
        'Accept' => 'application/json',
    ])->postJson(route('command-assistant.execute'), [
        'command_string' => 'demo:assistant', // no flag here; controller should append it
        'executed_by' => 'qa-user',
    ]);

    // Assert HTTP
    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('message', 'Command executed successfully.')
        ->where('output', fn ($out) => is_string($out))
    );

    // Assert DB audit record was created, with flag appended by controller
    $this->assertDatabaseHas('executed_assistant_commands', [
        'executed_by' => 'qa-user',
    ]);

    // Fetch and assert the exact command string contains the flag
    $record = \DB::table('executed_assistant_commands')->latest('id')->first();
    expect($record->command)->toBe('demo:assistant --laravel-assistant');
});


it('execute rejects default Laravel commands without the assistant option (e.g., list)', function () {
    // Act: attempt to execute the built-in `list` command which does not declare --laravel-assistant
    $response = $this->withHeaders([
        'Authorization' => 'Bearer test-key',
        'Accept' => 'application/json',
    ])->postJson(route('command-assistant.execute'), [
        'command_string' => 'list',
    ]);

    // Assert HTTP 403 and error message
    $response->assertStatus(403);
    $response->assertJson([
        'error' => 'This command is not available for the assistant.'
    ]);

    // Assert no audit record was created
    $this->assertDatabaseCount('executed_assistant_commands', 0);
});
