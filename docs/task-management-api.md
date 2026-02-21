# Task Management API Documentation

## Overview

The Task Management API provides a pre-creation refinement pipeline that converts raw prompts into coder-ready task specs before any task record is created.

## Endpoints

### POST /api/tasks/refine-and-create

Refines a raw prompt into structured task specifications and creates task records.

#### Request

```json
{
  "prompt": "Build user authentication and admin dashboard",
  "assignee": "coder",
  "project_id": 7,
  "bypass_refinement": false
}
```

**Parameters:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `prompt` | string | Yes | Raw task description (min 5 chars) |
| `assignee` | string | Yes | User/agent to assign the task(s) |
| `project_id` | integer | Yes | Project ID (min 1) |
| `bypass_refinement` | boolean | No | Skip refinement (default: false) |

#### Success Response (201)

```json
{
  "success": true,
  "message": "2 tasks created successfully",
  "data": [
    {
      "id": 1,
      "title": "User Authentication",
      "status": "pending",
      "assignee": "coder",
      "project_id": 7,
      "refined": true,
      "bypass_refinement": false,
      "is_claimable": true,
      "blocked_by": [],
      "blocking": [2],
      "refinement_output": {
        "title": "User Authentication",
        "objective": "Implement: Build user authentication",
        "context": "This is the first task in a sequence of 2 related tasks.",
        "acceptance_criteria": "- [ ] Build user authentication is fully implemented\n- [ ] Code follows project conventions\n- [ ] All related tests pass\n- [ ] No regressions introduced",
        "deliverables": "- Implementation code\n- Unit tests\n- Documentation updates (if applicable)\n- Verification that requirements are met",
        "implementation_details": "Follow existing patterns in the codebase...",
        "how_to_verify": "1. Run the test suite: `php artisan test`...",
        "definition_of_done": "- [ ] Implementation complete\n- [ ] Tests passing...",
        "if_blocked": "If blocked:\n1. Document the specific blocker...",
        "blocked_by": []
      },
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    },
    {
      "id": 2,
      "title": "Admin Dashboard",
      "status": "pending",
      "assignee": "coder",
      "project_id": 7,
      "refined": true,
      "bypass_refinement": false,
      "is_claimable": false,
      "blocked_by": [1],
      "blocking": [],
      "refinement_output": {
        "title": "Admin Dashboard",
        "objective": "Implement: Admin Dashboard",
        "context": "This is task 2 of 2. It depends on the completion of previous tasks.",
        "acceptance_criteria": "...",
        "deliverables": "...",
        "implementation_details": "...",
        "how_to_verify": "...",
        "definition_of_done": "...",
        "if_blocked": "...",
        "blocked_by": [0]
      },
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### POST /api/tasks/create-direct

Creates a task directly without refinement (escape hatch for manual use cases).

#### Request

```json
{
  "prompt": "Fix urgent bug in production",
  "assignee": "coder",
  "project_id": 7
}
```

#### Success Response (201)

```json
{
  "success": true,
  "message": "Task created directly (bypassed refinement)",
  "data": {
    "id": 3,
    "title": "Fix urgent bug in production",
    "status": "pending",
    "assignee": "coder",
    "project_id": 7,
    "refined": false,
    "bypass_refinement": true,
    "is_claimable": true,
    "blocked_by": [],
    "blocking": [],
    "refinement_output": null,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

## Error Responses

### Validation Error (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "prompt": ["The prompt field is required."],
    "assignee": ["The assignee field is required."],
    "project_id": ["The project id must be at least 1."]
  }
}
```

### Refinement Validation Error (422)

```json
{
  "success": false,
  "message": "Refinement validation failed",
  "errors": {
    "refinement": [
      "Task 0 validation failed: objective: Required section 'objective' is missing or empty"
    ]
  }
}
```

### Server Error (500)

```json
{
  "success": false,
  "message": "Failed to create task(s)",
  "errors": {
    "general": ["Database connection failed"]
  }
}
```

## Refinement Output Schema

All refined tasks must conform to this schema:

```typescript
{
  title: string (max 255 chars)
  objective: string (required)
  context?: string
  acceptance_criteria: string (required)
  deliverables: string (required)
  implementation_details?: string
  how_to_verify?: string
  definition_of_done?: string
  if_blocked?: string
  blocked_by?: number[] // Array of task indices
}
```

### Required Sections

- `title`
- `objective`
- `acceptance_criteria`
- `deliverables`

### Validation Rules

1. All required sections must be present and non-empty
2. Title must be 255 characters or less
3. `blocked_by` must be an array of integers (task indices)
4. Unknown fields are rejected
5. Task creation is atomic - all tasks in a multi-task operation succeed or fail together

## Multi-Task Decomposition

The refinement service automatically splits prompts containing conjunctions into separate tasks:

- ` and `
- ` & `
- ` + `
- ` as well as `
- ` along with `
- ` plus `
- `, followed by `
- `; `

Example:

```
Input:  "Build auth system and create admin panel"
Output: 2 tasks with Task 2 blocked_by Task 1
```

## Task Claimability

A task is claimable when:

1. Status is `pending`
2. Either `refined=true` OR `bypass_refinement=true`
3. Has no `blocked_by` dependencies (or all dependencies are completed)

```php
$task->isClaimable(); // boolean
```

## Testing

Run the test suite:

```bash
# Refinement tests
php artisan test --filter=Refinement --compact

# Task creation tests
php artisan test --filter=TaskCreation --compact

# Schema validation tests
php artisan test --filter=SchemaValidation --compact

# All task tests
php artisan test --filter=Task --compact
```

## curl Examples

```bash
# Single task refinement
curl -sLk -X POST https://sequence.beast/api/tasks/refine-and-create \
  -H 'Content-Type: application/json' \
  -d '{
    "prompt": "Implement user login",
    "assignee": "coder",
    "project_id": 7
  }' | jq

# Multi-task decomposition
curl -sLk -X POST https://sequence.beast/api/tasks/refine-and-create \
  -H 'Content-Type: application/json' \
  -d '{
    "prompt": "Build API and write tests",
    "assignee": "coder",
    "project_id": 7
  }' | jq

# Bypass refinement
curl -sLk -X POST https://sequence.beast/api/tasks/refine-and-create \
  -H 'Content-Type: application/json' \
  -d '{
    "prompt": "Fix urgent bug",
    "assignee": "coder",
    "project_id": 7,
    "bypass_refinement": true
  }' | jq

# Direct creation endpoint
curl -sLk -X POST https://sequence.beast/api/tasks/create-direct \
  -H 'Content-Type: application/json' \
  -d '{
    "prompt": "Manual task",
    "assignee": "coder",
    "project_id": 7
  }' | jq
```