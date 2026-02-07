# OpenCode SDK for Laravel

A Laravel SDK for the [OpenCode](https://github.com/opencode-ai/opencode) AI coding agent HTTP API, built with [Saloon](https://docs.saloon.dev).

## Requirements

- PHP 8.4+
- Laravel 11 or 12

## Installation

```bash
composer require hardimpact/opencode-sdk-laravel
```

The package auto-registers its service provider via Laravel's package discovery.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="opencode-config"
```

```php
// config/opencode.php
return [
    'base_url' => env('OPENCODE_BASE_URL', 'http://localhost:4096'),
];
```

## Usage

### Using the Facade

```php
use HardImpact\OpenCode\Facades\OpenCode;

$session = OpenCode::sessions()->create(directory: '/path/to/project');
```

### Using Dependency Injection

```php
use HardImpact\OpenCode\OpenCode;

public function __construct(private OpenCode $opencode) {}
```

### Without Laravel

```php
use HardImpact\OpenCode\OpenCode;

$opencode = new OpenCode('http://localhost:4096');
```

### Sessions

```php
// Create
$session = $opencode->sessions()->create(directory: '/path/to/project', title: 'My Session');

// List all
$sessions = $opencode->sessions()->list();

// Get by ID
$session = $opencode->sessions()->get(id: 'ses_xxx');

// Update
$session = $opencode->sessions()->update(id: 'ses_xxx', title: 'New Title');

// Delete
$opencode->sessions()->delete(id: 'ses_xxx');

// Abort active operation
$opencode->sessions()->abort(id: 'ses_xxx');
```

### Sending Messages

```php
// Sync (waits for completion)
$response = $opencode->sessions()->sendMessage(
    id: 'ses_xxx',
    providerID: 'anthropic',
    modelID: 'claude-sonnet-4-20250514',
    text: 'What files are in this project?',
);

// $response->info is an AssistantMessage
// $response->parts is an array of Part DTOs

// Async (returns immediately, monitor via events)
$opencode->sessions()->sendMessageAsync(
    id: 'ses_xxx',
    providerID: 'anthropic',
    modelID: 'claude-sonnet-4-20250514',
    text: 'Refactor the auth module',
);

// Send with raw parts (for file attachments, etc.)
$response = $opencode->sessions()->sendMessageWithParts(
    id: 'ses_xxx',
    providerID: 'anthropic',
    modelID: 'claude-sonnet-4-20250514',
    parts: [
        ['type' => 'text', 'text' => 'Review this file'],
        ['type' => 'file', 'mime' => 'text/plain', 'url' => 'file:///path/to/file.php'],
    ],
);

// Get message history
$messages = $opencode->sessions()->messages(id: 'ses_xxx');
```

### Slash Commands

```php
$response = $opencode->sessions()->command(
    id: 'ses_xxx',
    command: 'compact',
    arguments: '',
);
```

### Session Operations

```php
// Initialize
$opencode->sessions()->init(id: 'ses_xxx', messageID: '...', modelID: '...', providerID: '...');

// Summarize
$opencode->sessions()->summarize(id: 'ses_xxx', modelID: '...', providerID: '...');

// Revert to a previous message
$session = $opencode->sessions()->revert(id: 'ses_xxx', messageID: 'msg_xxx');

// Undo revert
$session = $opencode->sessions()->unrevert(id: 'ses_xxx');

// Share / unshare
$session = $opencode->sessions()->share(id: 'ses_xxx');
$session = $opencode->sessions()->unshare(id: 'ses_xxx');
```

### Permissions (Questions)

When OpenCode needs approval for tool usage (file edits, bash commands, etc.), permissions appear as events. Respond programmatically:

```php
// List pending permissions
$questions = $opencode->questions()->list();

// Approve once
$opencode->questions()->answer(
    sessionId: 'ses_xxx',
    permissionId: 'perm_xxx',
    response: 'once',
);

// Approve always (for this pattern)
$opencode->questions()->answer(
    sessionId: 'ses_xxx',
    permissionId: 'perm_xxx',
    response: 'always',
);

// Reject
$opencode->questions()->reject(sessionId: 'ses_xxx', permissionId: 'perm_xxx');
```

### Event Streaming (SSE)

Monitor real-time state changes via Server-Sent Events:

```php
foreach ($opencode->events()->stream() as $event) {
    match ($event->type) {
        EventType::SessionIdle => handleDone($event),
        EventType::SessionError => handleError($event),
        EventType::MessagePartUpdated => handlePartUpdate($event),
        EventType::PermissionUpdated => handlePermission($event),
        default => null,
    };
}
```

### Providers

```php
$providers = $opencode->providers()->list();
```

## Feature Parity

Comparison with the official OpenCode SDKs ([JS](https://github.com/anomalyco/opencode-sdk-js), [Go](https://github.com/anomalyco/opencode-sdk-go)).

### Session

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| Create session | Y | Y | Y |
| List sessions | Y | Y | Y |
| Get session | Y | Y | Y |
| Update session | Y | - | Y |
| Delete session | Y | Y | Y |
| Abort session | Y | Y | Y |
| Send message (sync) | Y | Y | Y |
| Send message (async) | Y | - | - |
| Get messages | Y | Y | Y |
| Get single message | - | - | Y |
| Run slash command | Y | - | Y |
| Run shell command | - | - | Y |
| Init session | Y | Y | Y |
| Summarize session | Y | Y | Y |
| Revert session | Y | Y | Y |
| Unrevert session | Y | Y | Y |
| Share session | Y | Y | Y |
| Unshare session | Y | Y | Y |
| Get child sessions | - | - | Y |

### Permissions

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| List pending | Y | - | - |
| Respond (once/always/reject) | Y | - | Y |

### Events

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| SSE event stream | Y | Y | Y |

### Providers

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| List providers + models | Y | Y | Y |

### App

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| Get app info | - | Y | - |
| Init app | - | Y | - |
| Log | - | Y | Y |
| List modes | - | Y | - |

### Config

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| Get config | - | Y | Y |

### File

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| List files | - | - | Y |
| Read file | - | Y | Y |
| File status (git) | - | Y | Y |

### Find

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| Find files | - | Y | Y |
| Find symbols | - | Y | Y |
| Find text | - | Y | Y |

### Agent

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| List agents | - | - | Y |

### Project

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| List projects | - | - | Y |
| Current project | - | - | Y |

### Command

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| List commands | - | - | Y |

### Path

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| Get paths | - | - | Y |

### TUI

| Endpoint | Laravel | JS | Go |
|----------|:-------:|:--:|:--:|
| Append/clear/submit prompt | - | Y | Y |
| Open help/models/sessions/themes | - | Y | Y |
| Execute command | - | - | Y |
| Show toast | - | - | Y |

## Testing

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Style

```bash
composer format
```

## License

MIT
