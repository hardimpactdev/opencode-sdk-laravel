<?php

namespace HardImpact\OpenCode\Enums;

enum EventType: string
{
    case SessionCreated = 'session.created';
    case SessionUpdated = 'session.updated';
    case SessionDeleted = 'session.deleted';
    case SessionIdle = 'session.idle';
    case SessionError = 'session.error';
    case SessionCompacted = 'session.compacted';
    case MessageUpdated = 'message.updated';
    case MessageRemoved = 'message.removed';
    case MessagePartUpdated = 'message.part.updated';
    case MessagePartRemoved = 'message.part.removed';
    case PermissionUpdated = 'permission.updated';
    case PermissionReplied = 'permission.replied';
    case FileEdited = 'file.edited';
    case FileWatcherUpdated = 'file.watcher.updated';
    case TodoUpdated = 'todo.updated';
    case StorageWrite = 'storage.write';
    case InstallationUpdated = 'installation.updated';
    case LspClientDiagnostics = 'lsp.client.diagnostics';
    case IdeInstalled = 'ide.installed';
    case ServerConnected = 'server.connected';
}
