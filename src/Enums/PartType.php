<?php

namespace HardImpact\OpenCode\Enums;

enum PartType: string
{
    case Text = 'text';
    case File = 'file';
    case Tool = 'tool';
    case StepStart = 'step-start';
    case StepFinish = 'step-finish';
    case Snapshot = 'snapshot';
    case Patch = 'patch';
}
