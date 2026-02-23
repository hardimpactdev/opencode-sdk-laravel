---
date: 2026-02-23
problem_type: workflow_issue
component: config
severity: high
symptoms:
  - "Task asks to modify app/Providers/AppServiceProvider.php which doesn't exist"
  - "Repository is a Laravel package, not a Laravel application"
  - "Task references wrong working directory in description"
root_cause: task_misassignment
resolution_type: workflow_improvement
tags: [task-validation, repository-type, laravel-package, app-service-provider]
---

# Task Assigned to Wrong Repository Type

## Symptom

Task #91 asks to add `DB::prohibitDestructiveCommands()` to `app/Providers/AppServiceProvider.php`, but this file doesn't exist. The repository is a Laravel SDK package (`hardimpact/opencode-sdk-laravel`), not a full Laravel application.

**Repository structure shows:**
- `src/` - Package source code
- `tests/` - Test suite
- `config/` - Package config
- **Missing:** `app/`, `routes/`, `database/` (application directories)

**Task description references:**
- Incident occurred in `~/projects/sequence` (different project)
- Production database wipe in Sequence application
- Fix should be applied there, not in SDK package

## Investigation

1. **Attempted:** Search for AppServiceProvider.php
   **Result:** File doesn't exist - this is a package, not an application

2. **Attempted:** Check repository structure
   **Result:** Confirmed package structure (PSR-4 autoloading, no app/ directory)

3. **Attempted:** Review composer.json
   **Result:** Package provides `OpenCodeServiceProvider` for Laravel's service container, but has no database tables or migrations

## Root Cause

Task assigned to incorrect repository. The `DB::prohibitDestructiveCommands()` feature:
- Is for **Laravel applications** with database migrations
- Protects against `migrate:fresh`, `migrate:rollback`, `db:wipe`
- Has no purpose in SDK packages (no DB tables, no migrations run by package)

The actual target should be `~/projects/sequence` (the Laravel application where the incident occurred).

## Solution

### Immediate Action
1. **Identify repository type** by checking for:
   - `app/` directory (applications have this, packages don't)
   - `composer.json` type field (`project` vs `library`)
   - Package provider registration in `extra.laravel`

2. **Validate task applicability:**
   ```bash
   # Check if this is an application
   if [ -d "app/Providers" ]; then
       # Can add AppServiceProvider modifications
   else
       # This is likely a package - task may be misassigned
   fi
   ```

3. **Communicate blocking issue clearly:**
   - Explain repository type mismatch
   - Reference correct repository from task description
   - Request task reassignment

### Prevention

**For task creators:**
- Verify target repository has required structure before assignment
- Check `composer.json` type field
- Include repository validation in task templates

**For autonomous agents:**
- Always validate repository structure against task requirements
- Check for existence of key files before starting work
- Read AGENTS.md for project type confirmation

## Related

- Task #91 (Sequence project - production DB incident)
- `~/projects/sequence` (correct repository for this fix)
