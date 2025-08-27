<?php

namespace Nikolaynesov\LaravelCommandAssistant;

use Illuminate\Database\Eloquent\Model;

class ExecutedAssistantCommand extends Model
{
    protected $table = 'executed_assistant_commands';

    protected $fillable = [
        'command',
        'executed_by',
    ];
}