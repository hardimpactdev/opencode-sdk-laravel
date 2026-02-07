---
name: opencode
description: Interact with OpenCode AI coding agent via its HTTP API. Use for creating sessions, sending messages to models (Claude, GPT, Gemini, Kimi, etc.), and programmatic control of OpenCode server. Triggers on queries about OpenCode API, headless server, or automating coding tasks.
---

# OpenCode Skill

OpenCode is an AI coding agent with a headless HTTP server mode. This skill covers programmatic interaction via its REST API.

## Quick Reference

**Server:** `http://localhost:4096` (default port)
**OpenAPI Spec:** `http://localhost:4096/doc`

### Start Server

```bash
opencode serve --hostname 0.0.0.0 --port 4096
```

### Common Operations

#### Create Session
```bash
curl -s -X POST "http://localhost:4096/session?directory=/path/to/project" \
  -H "Content-Type: application/json" \
  -d '{"title": "My Session"}'
```

#### Send Message (sync, waits for response)
```bash
curl -s -X POST "http://localhost:4096/session/{sessionID}/message?directory=/path/to/project" \
  -H "Content-Type: application/json" \
  -d '{
    "model": {"providerID": "anthropic", "modelID": "claude-sonnet-4-20250514"},
    "parts": [{"type": "text", "text": "Your prompt here"}]
  }'
```

#### Send Message (async, returns immediately)
```bash
curl -s -X POST "http://localhost:4096/session/{sessionID}/prompt_async?directory=/path/to/project" \
  -H "Content-Type: application/json" \
  -d '{
    "model": {"providerID": "opencode", "modelID": "kimi-k2.5-free"},
    "parts": [{"type": "text", "text": "Your prompt here"}]
  }'
```

### Available Providers & Models

Check connected providers:
```bash
curl -s http://localhost:4096/provider | jq '.connected'
```

List models for a provider:
```bash
opencode models [provider]
```

Common models:
- `anthropic/claude-sonnet-4-20250514`
- `openai/gpt-4o`
- `google/gemini-2.5-pro`
- `opencode/kimi-k2.5-free` (free tier)

### Question API (Interactive Prompts)

When OpenCode needs user input (file selection, confirmations, etc.), it creates questions you can answer programmatically.

#### List Pending Questions
```bash
curl -s "http://localhost:4096/question"
```

#### Answer a Question
```bash
curl -s -X POST "http://localhost:4096/question/{requestID}/reply" \
  -H "Content-Type: application/json" \
  -d '{"answers": [["SelectedOption"]]}'
```

#### Reject/Dismiss a Question
```bash
curl -s -X POST "http://localhost:4096/question/{requestID}/reject"
```

**Note:** The `requestID` starts with `que_` (e.g., `que_c1964b27c001...`)

## API Discovery

When blocked or unsure about an API route, always research:

1. **OpenAPI Spec:** `http://localhost:4096/doc`
2. **GitHub Source:** https://github.com/opencode/opencode/tree/main/packages/server/src/routes

Key route files:
- `session.ts` - Session management
- `message.ts` - Sending messages
- `question.ts` - Interactive prompts
- `provider.ts` - Model providers

## API Reference

For complete endpoint documentation → see [references/api.md](references/api.md)

## CLI Reference

For command-line usage → see [references/cli.md](references/cli.md)
