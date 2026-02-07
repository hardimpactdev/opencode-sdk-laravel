<?php

namespace HardImpact\OpenCode\Enums;

enum ToolStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Error = 'error';
}
